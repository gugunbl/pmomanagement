<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lokasi;
use App\Perangkat;
use App\Project;
use App\ApFloor;
use App\ApTrafficLog;
use App\Exports\ApTrafficMatrixExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApTrafficController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu_monitoring_ap');
        $this->middleware('permission:sub_mapping_lantai_ap', ['only' => ['mappingIndex', 'mappingStore', 'mappingUpdate', 'mappingDestroy', 'mappingAutoGenerate']]);
        $this->middleware('permission:sub_traffic_ap', ['only' => ['inputForm', 'getFloorsForInput', 'inputStore']]);
        $this->middleware('permission:sub_monitor_ap', ['only' => ['monitor', 'exportExcel']]);
    }

    /**
     * Helper to retrieve projects accessible by the authenticated user
     */
    private function getUserProjects()
    {
        $user = Auth::user();
        $userProjects = $user->projects->pluck('id_project')->toArray();

        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->orderBy('nama_project', 'asc')->get();
            $fixedProject = count($userProjects) === 1;
        } else {
            $projects = Project::orderBy('nama_project', 'asc')->get();
            $fixedProject = false;
        }

        return [$projects, $fixedProject];
    }

    /*
    |--------------------------------------------------------------------------
    | SUB MENU 1: MAPPING LANTAI & AP
    |--------------------------------------------------------------------------
    */

    public function mappingIndex(Request $request)
    {
        $title = 'Mapping Lantai & Access Point';

        [$projects, $fixedProject] = $this->getUserProjects();

        // 1. Determine Selected Project
        $selectedProjectId = $request->input('id_project');
        if (!$selectedProjectId && $projects->isNotEmpty()) {
            $selectedProjectId = $projects->first()->id_project;
        }
        $selectedProject = $projects->firstWhere('id_project', $selectedProjectId);

        // 2. Filter Lokasis based on Selected Project
        $lokasis = collect();
        if ($selectedProjectId) {
            $lokasiIds = Perangkat::where('id_project', $selectedProjectId)
                ->whereNotNull('id_lokasi')
                ->pluck('id_lokasi')
                ->unique();

            $lokasis = Lokasi::whereIn('id_lokasi', $lokasiIds)->orderBy('nama_lokasi', 'asc')->get();

            // Fallback: if project has no devices with id_lokasi assigned yet, allow selecting from all lokasis
            if ($lokasis->isEmpty()) {
                $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
            }
        } else {
            $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        }

        // 3. Determine Selected Lokasi
        $selectedLokasiId = $request->input('id_lokasi');
        if (!$selectedLokasiId && $lokasis->isNotEmpty()) {
            $selectedLokasiId = $lokasis->first()->id_lokasi;
        } elseif ($selectedLokasiId && !$lokasis->contains('id_lokasi', $selectedLokasiId)) {
            $selectedLokasiId = $lokasis->first()->id_lokasi ?? null;
        }

        $floors = collect();
        $selectedLokasi = null;
        $availableDevices = collect();

        if ($selectedLokasiId) {
            $selectedLokasi = Lokasi::find($selectedLokasiId);
            if ($selectedLokasi) {
                $floorQuery = ApFloor::with(['perangkats.jenisPerangkat', 'user'])
                    ->where('id_lokasi', $selectedLokasiId);

                if ($selectedProjectId) {
                    $floorQuery->where(function ($q) use ($selectedProjectId) {
                        $q->where('id_project', $selectedProjectId)
                          ->orWhereNull('id_project');
                    });
                }

                $floors = $floorQuery->orderBy('urutan', 'asc')
                    ->orderBy('nama_lantai', 'asc')
                    ->get();

                // Sort floors naturally if urutan is default (0)
                if ($floors->where('urutan', '>', 0)->isEmpty()) {
                    $floors = $floors->sort(function ($a, $b) {
                        return strnatcasecmp($a->nama_lantai, $b->nama_lantai);
                    })->values();
                }

                // Available AP devices: strictly from this project and matching lokasi (or unassigned lokasi in this project)
                $deviceQuery = Perangkat::with('jenisPerangkat');
                if ($selectedProjectId) {
                    $deviceQuery->where('id_project', $selectedProjectId);
                }
                $deviceQuery->where(function ($q) use ($selectedLokasiId) {
                    $q->where('id_lokasi', $selectedLokasiId)
                      ->orWhereNull('id_lokasi');
                });
                $availableDevices = $deviceQuery->get();
            }
        }

        return view('traffic_ap.mapping.index', compact(
            'title',
            'projects',
            'fixedProject',
            'selectedProjectId',
            'selectedProject',
            'lokasis',
            'selectedLokasiId',
            'selectedLokasi',
            'floors',
            'availableDevices'
        ));
    }

    public function mappingStore(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'nullable|exists:m_project,id_project',
            'id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
            'nama_lantai' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
            'keterangan' => 'nullable|string',
            'perangkat_ids' => 'nullable|array',
            'perangkat_ids.*' => 'exists:m_perangkat,id_perangkat',
        ]);

        $floor = ApFloor::create([
            'id_project' => $request->id_project,
            'id_lokasi' => $request->id_lokasi,
            'nama_lantai' => strtoupper(trim($request->nama_lantai)),
            'urutan' => $request->urutan ?? 0,
            'keterangan' => $request->keterangan,
            'user_id' => Auth::id(),
        ]);

        if (!empty($request->perangkat_ids)) {
            $floor->perangkats()->sync($request->perangkat_ids);
        }

        flash('Lantai dan mapping AP berhasil ditambahkan!')->success();
        return redirect()->route('traffic-ap.mapping.index', [
            'id_project' => $request->id_project,
            'id_lokasi' => $request->id_lokasi
        ]);
    }

    public function mappingUpdate(Request $request, $id)
    {
        $floor = ApFloor::findOrFail($id);

        $validated = $request->validate([
            'id_project' => 'nullable|exists:m_project,id_project',
            'nama_lantai' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
            'keterangan' => 'nullable|string',
            'perangkat_ids' => 'nullable|array',
            'perangkat_ids.*' => 'exists:m_perangkat,id_perangkat',
        ]);

        $oldName = $floor->nama_lantai;
        $newName = strtoupper(trim($request->nama_lantai));

        $updateData = [
            'nama_lantai' => $newName,
            'urutan' => $request->urutan ?? 0,
            'keterangan' => $request->keterangan,
        ];
        if ($request->has('id_project') && !empty($request->id_project)) {
            $updateData['id_project'] = $request->id_project;
        }

        $floor->update($updateData);

        // Sync perangkats
        $floor->perangkats()->sync($request->perangkat_ids ?? []);

        // Also update existing logs if floor name changed
        if ($oldName !== $newName) {
            ApTrafficLog::where('ap_floor_id', $floor->id)
                ->orWhere(function ($q) use ($floor, $oldName) {
                    $q->where('id_lokasi', $floor->id_lokasi)
                      ->where('nama_lantai', $oldName);
                })
                ->update(['nama_lantai' => $newName]);
        }

        flash('Mapping Lantai & AP berhasil diupdate!')->success();
        return redirect()->route('traffic-ap.mapping.index', [
            'id_project' => $floor->id_project,
            'id_lokasi' => $floor->id_lokasi
        ]);
    }

    public function mappingDestroy($id)
    {
        $floor = ApFloor::findOrFail($id);
        $projectId = $floor->id_project;
        $lokasiId = $floor->id_lokasi;

        $floor->perangkats()->detach();
        $floor->delete();

        flash('Data Lantai dan mapping AP berhasil dihapus!')->success();
        return redirect()->route('traffic-ap.mapping.index', [
            'id_project' => $projectId,
            'id_lokasi' => $lokasiId
        ]);
    }

    public function mappingAutoGenerate(Request $request)
    {
        $request->validate([
            'id_project' => 'nullable|exists:m_project,id_project',
            'id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
        ]);

        $projectId = $request->id_project;
        $lokasiId = $request->id_lokasi;

        // Ambil data perangkat pada lokasi & project tersebut yang memiliki isian lantai
        $query = Perangkat::where('id_lokasi', $lokasiId)
            ->whereNotNull('lantai')
            ->where('lantai', '<>', '');

        if ($projectId) {
            $query->where('id_project', $projectId);
        }

        $perangkats = $query->get();

        if ($perangkats->isEmpty()) {
            flash('Tidak ditemukan perangkat dengan isian lantai pada project dan lokasi ini.')->warning();
            return redirect()->route('traffic-ap.mapping.index', [
                'id_project' => $projectId,
                'id_lokasi' => $lokasiId
            ]);
        }

        $grouped = $perangkats->groupBy(function ($item) {
            return strtoupper(trim($item->lantai));
        });

        $createdFloors = 0;
        $mappedDevices = 0;

        foreach ($grouped as $namaLantai => $items) {
            $floor = ApFloor::firstOrCreate(
                [
                    'id_project' => $projectId,
                    'id_lokasi' => $lokasiId,
                    'nama_lantai' => $namaLantai,
                ],
                [
                    'urutan' => 0,
                    'user_id' => Auth::id(),
                ]
            );

            if ($floor->wasRecentlyCreated) {
                $createdFloors++;
            }

            // Sync/attach perangkat IDs
            $ids = $items->pluck('id_perangkat')->toArray();
            $floor->perangkats()->syncWithoutDetaching($ids);
            $mappedDevices += count($ids);
        }

        flash("Berhasil auto-generate {$createdFloors} lantai baru dan memetakan {$mappedDevices} perangkat!")->success();
        return redirect()->route('traffic-ap.mapping.index', [
            'id_project' => $projectId,
            'id_lokasi' => $lokasiId
        ]);
    }

    public function getProjectLocations(Request $request)
    {
        $projectId = $request->input('id_project');
        if ($projectId) {
            $lokasiIds = Perangkat::where('id_project', $projectId)
                ->whereNotNull('id_lokasi')
                ->pluck('id_lokasi')
                ->unique();

            $lokasis = Lokasi::whereIn('id_lokasi', $lokasiIds)->orderBy('nama_lokasi', 'asc')->get();

            if ($lokasis->isEmpty()) {
                $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
            }
        } else {
            $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        }

        return response()->json($lokasis);
    }

    public function getApPerangkats(Request $request)
    {
        $projectId = $request->input('id_project');
        $lokasiId = $request->input('id_lokasi');

        $query = Perangkat::with('jenisPerangkat');
        if ($projectId) {
            $query->where('id_project', $projectId);
        }
        if ($lokasiId) {
            $query->where(function ($q) use ($lokasiId) {
                $q->where('id_lokasi', $lokasiId)
                  ->orWhereNull('id_lokasi');
            });
        }

        $devices = $query->get()->map(function ($d) {
            $jenis = optional($d->jenisPerangkat)->jns_perangkat ?? 'Perangkat';
            $sn = $d->serialnumber ?? '-';
            $hostname = $d->hostname ? " ({$d->hostname})" : '';
            $lantai = $d->lantai ? " [{$d->lantai}]" : '';
            return [
                'id' => $d->id_perangkat,
                'label' => "{$jenis} - {$d->merk} {$d->type} - SN: {$sn}{$hostname}{$lantai}",
            ];
        });

        return response()->json($devices);
    }

    /**
     * Auto-detect devices on a specific floor name within project & lokasi.
     * Returns devices whose 'lantai' column matches the given nama_lantai.
     */
    public function autoDetectDevices(Request $request)
    {
        $projectId = $request->input('id_project');
        $lokasiId = $request->input('id_lokasi');
        $namaLantai = strtoupper(trim($request->input('nama_lantai', '')));

        if (!$projectId || !$lokasiId || $namaLantai === '') {
            return response()->json(['devices' => [], 'count' => 0]);
        }

        $devices = Perangkat::with('jenisPerangkat')
            ->where('id_project', $projectId)
            ->where('id_lokasi', $lokasiId)
            ->whereNotNull('lantai')
            ->where('lantai', '!=', '')
            ->get()
            ->filter(function ($d) use ($namaLantai) {
                return strtoupper(trim($d->lantai)) === $namaLantai;
            })
            ->map(function ($d) {
                $jenis = optional($d->jenisPerangkat)->jns_perangkat ?? 'Perangkat';
                $sn = $d->serialnumber ?? '-';
                $hostname = $d->hostname ?: '-';
                $merk = trim($d->merk . ' ' . $d->type);
                return [
                    'id' => $d->id_perangkat,
                    'jenis' => $jenis,
                    'hostname' => $hostname,
                    'serialnumber' => $sn,
                    'merk' => $merk,
                    'lantai' => $d->lantai,
                    'label' => "{$jenis} - {$merk} - SN: {$sn}" . ($hostname !== '-' ? " ({$hostname})" : ''),
                ];
            })
            ->values();

        return response()->json([
            'devices' => $devices,
            'count' => $devices->count(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SUB MENU 2: TRAFFIC AP (INPUT DATA)
    |--------------------------------------------------------------------------
    */

    public function inputForm(Request $request)
    {
        $title = 'Input Traffic AP';
        [$projects, $fixedProject] = $this->getUserProjects();

        $selectedProjectId = $request->input('id_project');
        if (!$selectedProjectId && $projects->isNotEmpty()) {
            $selectedProjectId = $projects->first()->id_project;
        }
        $selectedProject = $projects->firstWhere('id_project', $selectedProjectId);

        $lokasis = collect();
        if ($selectedProjectId) {
            $lokasiIds = Perangkat::where('id_project', $selectedProjectId)
                ->whereNotNull('id_lokasi')
                ->pluck('id_lokasi')
                ->unique();
            $lokasis = Lokasi::whereIn('id_lokasi', $lokasiIds)->orderBy('nama_lokasi', 'asc')->get();
            if ($lokasis->isEmpty()) {
                $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
            }
        } else {
            $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        }

        $selectedLokasiId = $request->input('id_lokasi', $lokasis->first()->id_lokasi ?? null);
        if ($selectedLokasiId && !$lokasis->contains('id_lokasi', $selectedLokasiId)) {
            $selectedLokasiId = $lokasis->first()->id_lokasi ?? null;
        }
        $selectedTanggal = $request->input('tanggal', date('Y-m-d'));

        return view('traffic_ap.input', compact(
            'title',
            'projects',
            'fixedProject',
            'selectedProjectId',
            'selectedProject',
            'lokasis',
            'selectedLokasiId',
            'selectedTanggal'
        ));
    }

    public function getFloorsForInput(Request $request)
    {
        $request->validate([
            'id_project' => 'nullable|exists:m_project,id_project',
            'id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
            'tanggal' => 'required|date',
        ]);

        $projectId = $request->id_project;
        $lokasiId = $request->id_lokasi;
        $tanggal = $request->tanggal;

        // 1. Ambil floors yang terdefinisi di ApFloor
        $floorQuery = ApFloor::with(['perangkats.jenisPerangkat'])
            ->where('id_lokasi', $lokasiId);

        if ($projectId) {
            $floorQuery->where(function ($q) use ($projectId) {
                $q->where('id_project', $projectId)->orWhereNull('id_project');
            });
        }

        $floors = $floorQuery->orderBy('urutan', 'asc')
            ->orderBy('nama_lantai', 'asc')
            ->get();

        // Sort floors naturally if urutan is 0
        if ($floors->where('urutan', '>', 0)->isEmpty()) {
            $floors = $floors->sort(function ($a, $b) {
                return strnatcasecmp($a->nama_lantai, $b->nama_lantai);
            })->values();
        }

        // Jika belum ada di ApFloor, ambil distinct lantai dari m_perangkat sebagai fallback
        $floorList = [];
        if ($floors->isNotEmpty()) {
            foreach ($floors as $f) {
                $apNames = $f->perangkats->map(function ($p) {
                    return $p->hostname ?: ($p->serialnumber ?: $p->merk);
                })->filter()->values()->toArray();

                $floorList[] = [
                    'id' => $f->id,
                    'nama_lantai' => $f->nama_lantai,
                    'ap_count' => $f->perangkats->count(),
                    'ap_names' => $apNames,
                ];
            }
        } else {
            $pQuery = Perangkat::where('id_lokasi', $lokasiId)
                ->whereNotNull('lantai')
                ->where('lantai', '<>', '');
            if ($projectId) {
                $pQuery->where('id_project', $projectId);
            }

            $distinctFloors = $pQuery->pluck('lantai')
                ->unique()
                ->sort(function ($a, $b) {
                    return strnatcasecmp($a, $b);
                })
                ->values();

            foreach ($distinctFloors as $df) {
                $floorList[] = [
                    'id' => null,
                    'nama_lantai' => strtoupper(trim($df)),
                    'ap_count' => 0,
                    'ap_names' => [],
                ];
            }
        }

        // 2. Ambil data traffic log yang sudah diinput untuk tanggal, project & lokasi ini
        $logQuery = ApTrafficLog::where('id_lokasi', $lokasiId)
            ->whereDate('tanggal', $tanggal);
        if ($projectId) {
            $logQuery->where(function ($q) use ($projectId) {
                $q->where('id_project', $projectId)->orWhereNull('id_project');
            });
        }

        $existingLogs = $logQuery->get()
            ->keyBy(function ($item) {
                return strtoupper(trim($item->nama_lantai));
            });

        // 3. Gabungkan data
        $result = [];
        foreach ($floorList as $fl) {
            $key = strtoupper(trim($fl['nama_lantai']));
            $existing = $existingLogs->get($key);

            $result[] = [
                'ap_floor_id' => $fl['id'],
                'nama_lantai' => $fl['nama_lantai'],
                'ap_count' => $fl['ap_count'],
                'ap_names' => $fl['ap_names'],
                'traffic_value' => $existing ? $existing->traffic_value : '',
                'traffic_unit' => $existing ? $existing->traffic_unit : 'GB',
                'has_data' => $existing ? true : false,
            ];
        }

        return response()->json([
            'success' => true,
            'project_id' => $projectId,
            'lokasi_id' => $lokasiId,
            'tanggal' => $tanggal,
            'floors' => $result,
        ]);
    }

    public function inputStore(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'nullable|exists:m_project,id_project',
            'id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
            'tanggal' => 'required|date',
            'entries' => 'required|array',
            'entries.*.nama_lantai' => 'required|string',
            'entries.*.ap_floor_id' => 'nullable|integer',
            'entries.*.traffic_value' => 'nullable|numeric|min:0',
            'entries.*.traffic_unit' => 'nullable|string|in:KB,MB,GB,TB',
        ]);

        $projectId = $request->id_project;
        $lokasiId = $request->id_lokasi;
        $tanggal = $request->tanggal;
        $savedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->entries as $entry) {
                $namaLantai = strtoupper(trim($entry['nama_lantai']));
                $val = $entry['traffic_value'];
                $unit = $entry['traffic_unit'] ?? 'GB';
                $floorId = !empty($entry['ap_floor_id']) ? $entry['ap_floor_id'] : null;

                if ($val !== null && $val !== '') {
                    ApTrafficLog::updateOrCreate(
                        [
                            'id_project' => $projectId,
                            'id_lokasi' => $lokasiId,
                            'nama_lantai' => $namaLantai,
                            'tanggal' => $tanggal,
                        ],
                        [
                            'ap_floor_id' => $floorId,
                            'traffic_value' => $val,
                            'traffic_unit' => $unit,
                            'user_id' => Auth::id(),
                        ]
                    );
                    $savedCount++;
                } else {
                    $deleteQuery = ApTrafficLog::where('id_lokasi', $lokasiId)
                        ->where('nama_lantai', $namaLantai)
                        ->whereDate('tanggal', $tanggal);
                    if ($projectId) {
                        $deleteQuery->where('id_project', $projectId);
                    }
                    $deleteQuery->delete();
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data traffic untuk {$savedCount} lantai berhasil disimpan!",
                ]);
            }

            flash("Data traffic untuk {$savedCount} lantai pada tanggal " . Carbon::parse($tanggal)->format('d F Y') . " berhasil disimpan!")->success();
            return redirect()->route('traffic-ap.input', [
                'id_project' => $projectId,
                'id_lokasi' => $lokasiId,
                'tanggal' => $tanggal,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                ], 500);
            }

            flash('Gagal menyimpan data: ' . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SUB MENU 3: MONITOR (REPORT / GRID / PIVOT VIEW)
    |--------------------------------------------------------------------------
    */

    public function monitor(Request $request)
    {
        $title = 'Monitoring AP';
        [$projects, $fixedProject] = $this->getUserProjects();

        $selectedProjectId = $request->input('id_project');
        if (!$selectedProjectId && $projects->isNotEmpty()) {
            $selectedProjectId = $projects->first()->id_project;
        }
        $selectedProject = $projects->firstWhere('id_project', $selectedProjectId);

        $lokasis = collect();
        if ($selectedProjectId) {
            $lokasiIds = Perangkat::where('id_project', $selectedProjectId)
                ->whereNotNull('id_lokasi')
                ->pluck('id_lokasi')
                ->unique();
            $lokasis = Lokasi::whereIn('id_lokasi', $lokasiIds)->orderBy('nama_lokasi', 'asc')->get();
            if ($lokasis->isEmpty()) {
                $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
            }
        } else {
            $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        }

        $selectedLokasiId = $request->input('id_lokasi', $lokasis->first()->id_lokasi ?? null);
        if ($selectedLokasiId && !$lokasis->contains('id_lokasi', $selectedLokasiId)) {
            $selectedLokasiId = $lokasis->first()->id_lokasi ?? null;
        }

        $selectedMonth = $request->input('bulan', date('n'));
        $selectedYear = $request->input('tahun', date('Y'));
        
        // Mode filter: 'month' (default) atau 'range'
        $filterMode = $request->input('filter_mode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Satuan tampilan: TB, GB (default), MB
        $selectedSatuan = strtoupper($request->input('satuan', 'GB'));
        if (!in_array($selectedSatuan, ['TB', 'GB', 'MB'])) {
            $selectedSatuan = 'GB';
        }

        $selectedLokasi = null;
        $dateColumns = [];
        $floors = [];
        $matrix = [];
        $totalTraffic = null;

        if ($selectedLokasiId) {
            $selectedLokasi = Lokasi::find($selectedLokasiId);

            // Tentukan rentang tanggal
            if ($filterMode === 'range' && $startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
            } else {
                $start = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
                $end = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
                $startDate = $start->format('Y-m-d');
                $endDate = $end->format('Y-m-d');
            }

            // Bangun kolom tanggal (1 Januari 2026, 2 Januari 2026, dst.)
            $current = $start->copy();
            $indonesianMonths = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            while ($current->lte($end)) {
                $day = $current->day;
                $monthName = $indonesianMonths[$current->month] ?? $current->format('F');
                $year = $current->year;
                $key = $current->format('Y-m-d');

                $dateColumns[$key] = [
                    'day' => $day,
                    'label' => "{$day} {$monthName} {$year}",
                    'short_label' => "{$day} " . substr($monthName, 0, 3),
                    'full_date' => $key,
                ];
                $current->addDay();
            }

            // Ambil daftar lantai dari mapping ApFloor atau distinct log/perangkat
            $floorQuery = ApFloor::where('id_lokasi', $selectedLokasiId);
            if ($selectedProjectId) {
                $floorQuery->where(function($q) use ($selectedProjectId) {
                    $q->where('id_project', $selectedProjectId)->orWhereNull('id_project');
                });
            }
            $floorModels = $floorQuery->orderBy('urutan', 'asc')
                ->orderBy('nama_lantai', 'asc')
                ->pluck('nama_lantai')
                ->toArray();

            // Jika ada data di ApTrafficLog yang belum terdaftar di ApFloor, gabungkan
            $logQuery = ApTrafficLog::where('id_lokasi', $selectedLokasiId)
                ->whereBetween('tanggal', [$startDate, $endDate]);
            if ($selectedProjectId) {
                $logQuery->where(function($q) use ($selectedProjectId) {
                    $q->where('id_project', $selectedProjectId)->orWhereNull('id_project');
                });
            }
            $logFloors = $logQuery->pluck('nama_lantai')->unique()->toArray();

            $allFloors = array_values(array_unique(array_merge($floorModels, $logFloors)));

            // Natural sort lantai (LANTAI 2, LANTAI 3, ... LANTAI 10, LANTAI 41)
            natsort($allFloors);
            $floors = array_values($allFloors);

            // Ambil semua logs dalam periode
            $logs = $logQuery->get();

            // Helper: konversi nilai traffic ke satuan yang dipilih
            $convertToSelected = function($value, $fromUnit) use ($selectedSatuan) {
                // Konversi ke MB dulu sebagai basis
                switch (strtoupper($fromUnit)) {
                    case 'TB': $mb = $value * 1024 * 1024; break;
                    case 'GB': $mb = $value * 1024; break;
                    case 'MB': $mb = $value; break;
                    case 'KB': $mb = $value / 1024; break;
                    default:   $mb = $value * 1024; // default GB
                }
                // Konversi dari MB ke satuan target
                switch ($selectedSatuan) {
                    case 'TB': return $mb / (1024 * 1024);
                    case 'GB': return $mb / 1024;
                    case 'MB': return $mb;
                    default:   return $mb / 1024;
                }
            };

            // Susun matriks: $matrix[lantai][tanggal] = formatted string
            foreach ($floors as $floorName) {
                $matrix[$floorName] = [];
                foreach ($dateColumns as $dateKey => $colInfo) {
                    $matrix[$floorName][$dateKey] = [
                        'value' => null,
                        'unit' => null,
                        'display' => '-',
                        'in_mb' => 0,
                    ];
                }
            }

            $totalInSelectedUnit = 0;

            foreach ($logs as $log) {
                $floorKey = strtoupper(trim($log->nama_lantai));
                $dateKey = Carbon::parse($log->tanggal)->format('Y-m-d');

                if (isset($matrix[$floorKey][$dateKey])) {
                    $convertedVal = $convertToSelected($log->traffic_value, $log->traffic_unit);
                    $displayVal = rtrim(rtrim(number_format($convertedVal, 4, '.', ''), '0'), '.');
                    $matrix[$floorKey][$dateKey] = [
                        'value' => $convertedVal,
                        'unit' => $selectedSatuan,
                        'display' => $displayVal . ' ' . $selectedSatuan,
                        'in_mb' => $log->traffic_in_mb,
                    ];
                    $totalInSelectedUnit += $convertedVal;
                }
            }

            // Format total traffic
            $totalFormatted = rtrim(rtrim(number_format($totalInSelectedUnit, 4, '.', ''), '0'), '.');
            $totalTraffic = [
                'value' => $totalInSelectedUnit,
                'display' => $totalFormatted ?: '0',
                'satuan' => $selectedSatuan,
            ];
        }

        return view('traffic_ap.monitor', compact(
            'title',
            'projects',
            'fixedProject',
            'selectedProjectId',
            'selectedProject',
            'lokasis',
            'selectedLokasiId',
            'selectedLokasi',
            'selectedMonth',
            'selectedYear',
            'filterMode',
            'startDate',
            'endDate',
            'selectedSatuan',
            'dateColumns',
            'floors',
            'matrix',
            'totalTraffic'
        ));
    }

    public function exportExcel(Request $request)
    {
        $projectId = $request->input('id_project');
        $lokasiId = $request->input('id_lokasi');
        $month = $request->input('bulan', date('n'));
        $year = $request->input('tahun', date('Y'));
        $filterMode = $request->input('filter_mode', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $lokasi = Lokasi::findOrFail($lokasiId);

        if ($filterMode === 'range' && $startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');
        }

        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $dateColumns = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $day = $current->day;
            $monthName = $indonesianMonths[$current->month] ?? $current->format('F');
            $key = $current->format('Y-m-d');
            $dateColumns[$key] = "{$day} {$monthName} {$current->year}";
            $current->addDay();
        }

        $floorQuery = ApFloor::where('id_lokasi', $lokasiId);
        if ($projectId) {
            $floorQuery->where(function($q) use ($projectId) {
                $q->where('id_project', $projectId)->orWhereNull('id_project');
            });
        }
        $floorModels = $floorQuery->orderBy('urutan', 'asc')
            ->orderBy('nama_lantai', 'asc')
            ->pluck('nama_lantai')
            ->toArray();

        $logQuery = ApTrafficLog::where('id_lokasi', $lokasiId)
            ->whereBetween('tanggal', [$startDate, $endDate]);
        if ($projectId) {
            $logQuery->where(function($q) use ($projectId) {
                $q->where('id_project', $projectId)->orWhereNull('id_project');
            });
        }
        $logFloors = $logQuery->pluck('nama_lantai')->unique()->toArray();

        $floors = array_values(array_unique(array_merge($floorModels, $logFloors)));
        natsort($floors);
        $floors = array_values($floors);

        $logs = $logQuery->get();

        $matrix = [];
        foreach ($floors as $floorName) {
            $matrix[$floorName] = [];
            foreach ($dateColumns as $dateKey => $label) {
                $matrix[$floorName][$dateKey] = '-';
            }
        }

        foreach ($logs as $log) {
            $floorKey = strtoupper(trim($log->nama_lantai));
            $dateKey = Carbon::parse($log->tanggal)->format('Y-m-d');
            if (isset($matrix[$floorKey][$dateKey])) {
                $val = rtrim(rtrim(number_format((float)$log->traffic_value, 2, '.', ''), '0'), '.');
                $matrix[$floorKey][$dateKey] = $val . ' ' . $log->traffic_unit;
            }
        }

        $filename = 'Traffic_AP_' . str_replace(' ', '_', $lokasi->nama_lokasi) . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new ApTrafficMatrixExport($lokasi, $dateColumns, $floors, $matrix), $filename);
    }
}

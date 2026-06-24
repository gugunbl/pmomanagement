<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Perangkat;
use App\Project;
use App\Jenisperangkat;
use App\Lokasi;
use App\Exports\PerangkatTemplateExport;
use App\Exports\PerangkatExport;
use App\Imports\PerangkatImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class PerangkatController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-perangkat');
        $this->middleware('permission:create-perangkat', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-perangkat', ['only' => ['edit', 'update', 'viewDetailSummaryUpdate']]);
        $this->middleware('permission:delete-perangkat', ['only' => ['destroy']]);
        $this->middleware('permission:view-summary-perangkat', ['only' => ['summary', 'viewDetailSummary']]);
    }

    public function show($perangkat)
    {
        $title = "Informasi Detail Perangkat";
        $perangkatss = Perangkat::with([
            'project.statusImplementation',
            'project.statusMaintenance',
            'project.mcategory',
            'project.mPMImplementasi',
            'project.mPMMaintenance',
            'lokasi',
            'jenisPerangkat'
        ])->where('id_perangkat', $perangkat)->firstOrFail();

        return view('perangkat.show', compact('title', 'perangkatss'));
    }




public function index(Request $request)
{
    $user = Auth::user();
    $userProjects = $user->projects->pluck('id_project')->toArray();

    $query = Perangkat::with(['project', 'lokasi', 'jenisPerangkat']);

    // Base project access filter
    if (count($userProjects) > 0) {
        $query->whereIn('id_project', $userProjects);
        $projectes = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
    } else {
        $projectes = Project::pluck('nama_project', 'id_project');
    }

    // search text
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('serialnumber', 'like', '%' . $request->search . '%')
              ->orWhere('merk', 'like', '%' . $request->search . '%')
              ->orWhere('type', 'like', '%' . $request->search . '%');
        });
    }

    $selectedProjects = array_values(array_filter((array) $request->input('id_project', []), function ($idProject) {
        return $idProject !== null && $idProject !== '';
    }));

    // filter project from request
    if (count($selectedProjects) > 0) {
        $query->whereIn('id_project', $selectedProjects);
    }

    $perangkats = $query->paginate(15)->withQueryString();

    return view('perangkat.index', compact('perangkats', 'projectes'));
}



    public function create()
    {
        $statusperangkat = [
            '0' => 'Tidak Aktif',
            '1' => 'Aktif',
            '3' => 'Golden Unit/Stock'
        ];
        $projectes = Project::pluck('nama_project', 'id_project','nomor_spk');
        $jenisperangkates = Jenisperangkat::pluck('jns_perangkat','id_jenis');
        $sitelist=[
        '' => 'Select Site...',
        'GTI' => 'GTI - Ragunan',
        'ODC' => 'ODC - Sentul',
        'DRC' => 'DRC - Tabanan',
        'MB' => 'Menara Brilian',
        'Bripens' => 'Menara BRIPens',
        'SCC Warbun' => 'SCC Warung Buncit',
        'Brilian Club' => 'Brilian Club',
        'DCI' => 'DCI Cibitung'
        ];
        $lokasis = Lokasi::all();

        return view('perangkat.create', compact('projectes','jenisperangkates','sitelist','statusperangkat','lokasis'));
    }

     public function store(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'jenis' => 'required|exists:m_jenis_perangkat,id_jenis',
            'merk' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'dimensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'foto_1' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_2' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_3' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            // Grid Details
            'units' => 'required|array',
            'units.*.sn' => 'required|string|max:255|unique:m_perangkat,serialnumber',
            'units.*.sik' => 'nullable|string|max:255',
            'units.*.hostname' => 'nullable|string|max:255',
            'units.*.id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
            'units.*.koordinat' => 'nullable|string|max:255',
            'units.*.lantai' => 'nullable|string|max:255',
            'units.*.status' => 'nullable|in:0,1,3',
            'units.*.status_server' => 'nullable|string|max:255',
            'units.*.type_lisensi' => 'nullable|string|max:255',
            'units.*.start_warranty' => 'nullable|date',
            'units.*.end_warranty' => 'nullable|date',
            'units.*.tgl_online' => 'nullable|date',
        ]);

        $userId = Auth::id();
        $foto1Path = null;
        $foto2Path = null;
        $foto3Path = null;

        if ($request->hasFile('foto_1')) {
            $foto1Path = $request->file('foto_1')->store('uploads/perangkat', 'public');
        }
        if ($request->hasFile('foto_2')) {
            $foto2Path = $request->file('foto_2')->store('uploads/perangkat', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $foto3Path = $request->file('foto_3')->store('uploads/perangkat', 'public');
        }

        try {
            DB::beginTransaction();

            foreach ($request->units as $unit) {
                \App\Perangkat::create([
                    'id_project' => $validated['id_project'],
                    'jenis' => $validated['jenis'],
                    'merk' => $validated['merk'],
                    'type' => $validated['type'],
                    'dimensi' => $validated['dimensi'],
                    'site' => \App\Lokasi::where('id_lokasi', $unit['id_lokasi'])->value('nama_lokasi') ?? '-',
                    'id_lokasi' => $unit['id_lokasi'],
                    'keterangan' => $validated['keterangan'],
                    'foto_1' => $foto1Path,
                    'foto_2' => $foto2Path,
                    'foto_3' => $foto3Path,
                    'user_id' => $userId,
                    
                    // Unit Specific Info
                    'serialnumber' => $unit['sn'],
                    'sik' => $unit['sik'] ?? null,
                    'hostname' => $unit['hostname'] ?? null,
                    'koordinat' => $unit['koordinat'] ?? null,
                    'lantai' => $unit['lantai'] ?? null,
                    'status_server' => $unit['status_server'] ?? 'New Device',
                    'type_lisensi' => $unit['type_lisensi'] ?? null,
                    'start_warranty' => $unit['start_warranty'] ?? null,
                    'end_warranty' => $unit['end_warranty'] ?? null,
                    'tgl_online' => $unit['tgl_online'] ?? null,
                    'status' => $unit['status'] ?? 1,
                ]);
            }

            DB::commit();

            flash(count($request->units) . ' Perangkat Unit(s) created successfully!')->success();
            return redirect()->route('perangkat.index');

        } catch (\Exception $e) {
            DB::rollback();
            flash('Error creating perangkat: ' . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    public function summary(Request $request)
    {
        $activeTab = $request->get('tab', 'category');
        $customerFilter = $request->get('customer');
        $search = $request->get('search');

        $customers = \App\MCustomer::where('status', 1)->pluck('name', 'name');

        if ($activeTab == 'project') {
            $query = Project::with(['mPMMaintenance', 'statusMaintenance'])
                ->select('m_project.id_project', 'm_project.nama_project', 'm_project.tanggal_akhir_maintenance', 'm_project.pm_maintenance', 'm_project.status_maintenance', 'm_project.customer');

            if ($search) {
                $query->where('m_project.nama_project', 'like', "%{$search}%");
            }

            if ($customerFilter) {
                $query->where('m_project.customer', $customerFilter);
            }

            $summaryperangkats = $query->withCount('perangkats as jml')
                ->orderByRaw("CASE WHEN m_project.status_maintenance = '1' THEN 0 ELSE 1 END ASC")
                ->orderBy('m_project.tanggal_akhir_maintenance', 'asc')
                ->paginate(15)
                ->appends($request->query());
                
            $title = 'Summary Perangkat (By Project)';
            return view('perangkat.summary', compact('summaryperangkats', 'title', 'activeTab', 'customers', 'customerFilter', 'search'));
        } else {
             $query = Perangkat::with([
                'project' => function($query) {
                    $query->select('id_project', 'nama_project');
                },
                'jenisPerangkat' => function($query) {
                    $query->select('id_jenis', 'jns_perangkat');
                }
            ])
            ->select(
                'id_project', 
                'jenis',
                'merk', 
                'type', 
                DB::raw('COUNT(serialnumber) as jml')
            )
            ->groupBy('id_project', 'jenis', 'merk', 'type');
            
            if ($search) {
                $query->whereHas('project', function($q) use ($search) {
                    $q->where('nama_project', 'like', "%{$search}%");
                });
            }

            $summaryperangkats = $query->paginate(15)->appends($request->query());
        
            $title = 'Summary Perangkat';    
            return view('perangkat.summary', compact('summaryperangkats', 'title', 'activeTab', 'customers', 'customerFilter', 'search'));
        }
    }



    public function templateExport()
    {
        return Excel::download(new PerangkatTemplateExport, 'template_import_perangkat.xlsx');
    }

    public function export(Request $request)
    {
        return Excel::download(new PerangkatExport($request->all()), 'data_perangkat.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv,txt'
        ]);

        $file = $request->file('file_excel');
        $import = new \App\Imports\PerangkatImport();
        $data = Excel::toCollection($import, $file);

        $rows = [];
        $skippedCount = 0;
        if (isset($data[0])) {
            $dataCollection = $data[0]->skip(2);
            $rowCount = $dataCollection->count();

            // Batasi 100 data karena limitasi server (max_input_vars)
            if ($rowCount > 100) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Maksimal data yang dapat di-import dalam satu waktu adalah 100 data. Anda mencoba meng-import ' . $rowCount . ' data.'
                ], 422);
            }

            // Get all SNs from Excel to check for duplicates in DB
            $excelSNs = $dataCollection->map(function($row) {
                return isset($row[0]) ? trim($row[0]) : null;
            })->filter()->toArray();

            $existingSNs = \App\Perangkat::whereIn('serialnumber', $excelSNs)->pluck('serialnumber')->toArray();
            $existingSNs = array_map('strtolower', $existingSNs); // Case-insensitive comparison check

            foreach ($dataCollection as $row) {
                $sn = isset($row[0]) ? trim($row[0]) : null;
                
                if ($sn) {
                    // Check if SN already exists in DB
                    if (in_array(strtolower($sn), $existingSNs)) {
                        $skippedCount++;
                        continue;
                    }

                    $rows[] = [
                        'sn' => $sn,
                        'sik' => $row[1] ?? '',
                        'hostname' => $row[2] ?? '',
                        'id_lokasi' => isset($row[3]) ? \App\Lokasi::where('nama_lokasi', 'like', '%' . trim($row[3]) . '%')->value('id_lokasi') : null,
                        'koordinat' => $row[4] ?? '',
                        'lantai' => $row[5] ?? '',
                        'status' => $this->normalizeImportedPerangkatStatus($row[6] ?? null),
                        'status_server' => 'New Device', // Defaulted as it's hidden from template
                        'start_warranty' => $this->formatDateExcel($row[7]),
                        'end_warranty' => $this->formatDateExcel($row[8]),
                        'tgl_online' => $this->formatDateExcel($row[9]),
                        'type_lisensi' => $row[10] ?? '',
                    ];
                }
            }
        }

        return response()->json([
            'success' => true, 
            'data' => $rows,
            'skipped' => $skippedCount
        ]);
    }

    public function edit($perangkat)
    {
        $perangkatModel = Perangkat::with(['project', 'lokasi', 'jenisPerangkat'])->where('id_perangkat', $perangkat)->firstOrFail();
        
        $projectes = Project::pluck('nama_project', 'id_project');
        $jenisperangkates = Jenisperangkat::pluck('jns_perangkat', 'id_jenis');
        $lokasis = Lokasi::all();
        
        $title = "Edit Perangkat: " . $perangkatModel->serialnumber;
        $perangkat = $perangkatModel; // Pastikan $perangkat adalah model untuk view compact

        return view('perangkat.edit', compact('perangkat', 'projectes', 'jenisperangkates', 'lokasis', 'title'));
    }

    public function update(Request $request, $perangkat)
    {
        $perangkatModel = Perangkat::where('id_perangkat', $perangkat)->firstOrFail();

        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'jenis' => 'required|exists:m_jenis_perangkat,id_jenis',
            'merk' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'dimensi' => 'nullable|string|max:255',
            'id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
            'keterangan' => 'nullable|string',
            'serialnumber' => 'required|string|max:255|unique:m_perangkat,serialnumber,' . $perangkat . ',id_perangkat',
            'sik' => 'nullable|string|max:255',
            'hostname' => 'nullable|string|max:255',
            'koordinat' => 'nullable|string|max:255',
            'lantai' => 'nullable|string|max:255',
            'status_server' => 'nullable|string|max:255',
            'type_lisensi' => 'nullable|string|max:255',
            'start_warranty' => 'nullable|date',
            'end_warranty' => 'nullable|date',
            'tgl_online' => 'nullable|date',
            'foto_1' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_2' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_3' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $validated['site'] = \App\Lokasi::where('id_lokasi', $validated['id_lokasi'])->value('nama_lokasi') ?? '-';

        if ($request->hasFile('foto_1')) {
            $validated['foto_1'] = $request->file('foto_1')->store('uploads/perangkat', 'public');
        }
        if ($request->hasFile('foto_2')) {
            $validated['foto_2'] = $request->file('foto_2')->store('uploads/perangkat', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $validated['foto_3'] = $request->file('foto_3')->store('uploads/perangkat', 'public');
        }

        $perangkatModel->update($validated);

        flash('Perangkat updated successfully!')->success();
        return redirect()->route('perangkat.show', $perangkat);
    }

    public function destroy($perangkat)
    {
        $perangkatModel = Perangkat::where('id_perangkat', $perangkat)->firstOrFail();

        // Delete associated photos from storage
        if ($perangkatModel->foto_1) {
            Storage::disk('public')->delete($perangkatModel->foto_1);
        }
        if ($perangkatModel->foto_2) {
            Storage::disk('public')->delete($perangkatModel->foto_2);
        }
        if ($perangkatModel->foto_3) {
            Storage::disk('public')->delete($perangkatModel->foto_3);
        }

        $perangkatModel->delete();

        flash('Perangkat has been deleted successfully!')->success();
        return redirect()->route('perangkat.index');
    }

    public function viewDetailSummary(Request $request)
    {
        $id_project = $request->id_project;
        $merk = $request->merk;
        $type = $request->type;

        $project = Project::findOrFail($id_project);
        
        $query = Perangkat::where('id_project', $id_project);
        if ($merk) {
            $query->where('merk', $merk);
        }
        if ($type) {
            $query->where('type', $type);
        }
        
        $perangkats = $query->get();
        
        // Base info from the first perangkat found or default
        // We take the first one to populate the "Informasi Perangkat" section
        $basePerangkat = $perangkats->first();

        $projectes = Project::pluck('nama_project', 'id_project');
        $jenisperangkates = Jenisperangkat::pluck('jns_perangkat', 'id_jenis');
        $lokasis = Lokasi::all();
        $statusperangkat = [
            '0' => 'Tidak Aktif',
            '1' => 'Aktif',
            '3' => 'Golden Unit/Stock'
        ];
        
        $title = "Detail Summary Perangkat: " . $project->nama_project;
        
        return view('perangkat.view-detail-summary', compact(
            'project', 'perangkats', 'basePerangkat', 'projectes', 
            'jenisperangkates', 'lokasis', 'statusperangkat', 'title', 'merk', 'type', 'id_project'
        ));
    }

    public function viewDetailSummaryUpdate(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'jenis' => 'required|exists:m_jenis_perangkat,id_jenis',
            'merk' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'dimensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'foto_1' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_2' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_3' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            // Grid Details
            'units' => 'required|array',
            'units.*.id_perangkat' => 'nullable', // Null if new
            'units.*.sn' => 'required|string|max:255',
            'units.*.sik' => 'nullable|string|max:255',
            'units.*.hostname' => 'nullable|string|max:255',
            'units.*.id_lokasi' => 'required|exists:m_lokasi,id_lokasi',
            'units.*.koordinat' => 'nullable|string|max:255',
            'units.*.lantai' => 'nullable|string|max:255',
            'units.*.status' => 'nullable|in:0,1,3',
            'units.*.status_server' => 'nullable|string|max:255',
            'units.*.type_lisensi' => 'nullable|string|max:255',
            'units.*.start_warranty' => 'nullable|date',
            'units.*.end_warranty' => 'nullable|date',
            'units.*.tgl_online' => 'nullable|date',
        ]);

        $userId = Auth::id();
        
        // Handle Photos (Keep existing if not uploaded)
        // For multi-update, we might need to handle this carefully if photos are shared
        // But for now let's assume they are shared across units in this "summary view" 
        // as per the requirement "baik informasi perangkat atau spesifikasi detail"
        
        // Find existing photos from first unit if any
        $firstUnitId = collect($request->units)->pluck('id_perangkat')->filter()->first();
        $existingPerangkat = $firstUnitId ? Perangkat::find($firstUnitId) : null;
        
        $foto1Path = $existingPerangkat ? $existingPerangkat->foto_1 : null;
        $foto2Path = $existingPerangkat ? $existingPerangkat->foto_2 : null;
        $foto3Path = $existingPerangkat ? $existingPerangkat->foto_3 : null;

        if ($request->hasFile('foto_1')) {
            $foto1Path = $request->file('foto_1')->store('uploads/perangkat', 'public');
        }
        if ($request->hasFile('foto_2')) {
            $foto2Path = $request->file('foto_2')->store('uploads/perangkat', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $foto3Path = $request->file('foto_3')->store('uploads/perangkat', 'public');
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $newCount = 0;

            foreach ($request->units as $unit) {
                $data = [
                    'id_project' => $validated['id_project'],
                    'jenis' => $validated['jenis'],
                    'merk' => $validated['merk'],
                    'type' => $validated['type'],
                    'dimensi' => $validated['dimensi'],
                    'site' => \App\Lokasi::where('id_lokasi', $unit['id_lokasi'])->value('nama_lokasi') ?? '-',
                    'id_lokasi' => $unit['id_lokasi'],
                    'keterangan' => $validated['keterangan'],
                    'foto_1' => $foto1Path,
                    'foto_2' => $foto2Path,
                    'foto_3' => $foto3Path,
                    'user_id' => $userId,
                    
                    // Unit Specific Info
                    'serialnumber' => $unit['sn'],
                    'sik' => $unit['sik'] ?? null,
                    'hostname' => $unit['hostname'] ?? null,
                    'koordinat' => $unit['koordinat'] ?? null,
                    'lantai' => $unit['lantai'] ?? null,
                    'status_server' => $unit['status_server'] ?? 'New Device',
                    'type_lisensi' => $unit['type_lisensi'] ?? null,
                    'start_warranty' => $unit['start_warranty'] ?? null,
                    'end_warranty' => $unit['end_warranty'] ?? null,
                    'tgl_online' => $unit['tgl_online'] ?? null,
                    'status' => $unit['status'] ?? 1,
                ];

                if (!empty($unit['id_perangkat'])) {
                    $p = Perangkat::find($unit['id_perangkat']);
                    if ($p) {
                        // Check if anything changed
                        $changed = false;
                        foreach($data as $key => $val) {
                            if ($p->{$key} != $val) {
                                $changed = true;
                                break;
                            }
                        }
                        if ($changed) {
                            $p->update($data);
                            $updatedCount++;
                        }
                    }
                } else {
                    // Check if SN already exists to avoid duplicate error
                    $exists = Perangkat::where('serialnumber', $unit['sn'])->first();
                    if (!$exists) {
                        Perangkat::create($data);
                        $newCount++;
                    }
                }
            }

            DB::commit();

            flash("Success: $newCount new devices added, $updatedCount devices updated.")->success();
            return redirect()->route('perangkat.summary');

        } catch (\Exception $e) {
            DB::rollback();
            flash('Error updating perangkat units: ' . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    private function formatDateExcel($value)
    {
        if (!$value) return null;
        
        try {
            // Excel dates can be numeric or string
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeImportedPerangkatStatus($value)
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '' || in_array($normalized, ['aktif', 'active', '1'], true)) {
            return 1;
        }

        if (in_array($normalized, ['tidak aktif', 'nonaktif', 'non-aktif', 'inactive', '0'], true)) {
            return 0;
        }

        if (in_array($normalized, ['golden unit', 'golden unit/stock', 'golden unit stock', 'stock', 'gu', '3'], true)) {
            return 3;
        }

        return 1;
    }
}

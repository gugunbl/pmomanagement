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


class PerangkatController extends Controller
{
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
    $query = Perangkat::with(['project', 'lokasi']);

    // search text
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('serialnumber', 'like', '%' . $request->search . '%')
              ->orWhere('merk', 'like', '%' . $request->search . '%')
              ->orWhere('type', 'like', '%' . $request->search . '%');
        });
    }

    // filter project
    if ($request->filled('id_project')) {
        $query->where('id_project', $request->id_project);
    }

    $perangkats = $query->paginate(15)->withQueryString();

    $projectes = Project::pluck('nama_project', 'id_project');

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
            'site' => 'required|string|max:255',
            'id_lokasi' => 'nullable|exists:m_lokasi,id_lokasi',
            'keterangan' => 'nullable|string',
            'foto_1' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_2' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'foto_3' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            // Grid Details
            'units' => 'required|array',
            'units.*.sn' => 'required|string|max:255|unique:m_perangkat,serialnumber',
            'units.*.koordinat' => 'nullable|string|max:255',
            'units.*.lantai' => 'nullable|string|max:255',
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
                    'site' => $validated['site'],
                    'id_lokasi' => $validated['id_lokasi'],
                    'keterangan' => $validated['keterangan'],
                    'foto_1' => $foto1Path,
                    'foto_2' => $foto2Path,
                    'foto_3' => $foto3Path,
                    'user_id' => $userId,
                    
                    // Unit Specific Info
                    'serialnumber' => $unit['sn'],
                    'koordinat' => $unit['koordinat'] ?? null,
                    'lantai' => $unit['lantai'] ?? null,
                    'status_server' => $unit['status_server'] ?? 'New Device',
                    'type_lisensi' => $unit['type_lisensi'] ?? null,
                    'start_warranty' => $unit['start_warranty'] ?? null,
                    'end_warranty' => $unit['end_warranty'] ?? null,
                    'tgl_online' => $unit['tgl_online'] ?? null,
                    'status' => 1,
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

    public function summary()
    {
         $summaryperangkats = Perangkat::with(['project' => function($query) {
            $query->select('id_project', 'nama_project');
        }])
        ->select(
            'id_project', 
            'merk', 
            'type', 
            DB::raw('COUNT(serialnumber) as jml')
        )
        ->groupBy('id_project', 'merk', 'type') // ← HARUS ADA
        ->paginate(15);
    
    $title = 'Summary Perangkat';    
    return view('perangkat.summary', compact('summaryperangkats', 'title'));


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
            'file_excel' => 'required|mimes:xlsx,xls'
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
                        'koordinat' => $row[1] ?? '',
                        'lantai' => $row[2] ?? '',
                        'status_server' => $row[3] ?? 'New Device',
                        'type_lisensi' => $row[4] ?? '',
                        'start_warranty' => $this->formatDateExcel($row[5]),
                        'end_warranty' => $this->formatDateExcel($row[6]),
                        'tgl_online' => $this->formatDateExcel($row[7]),
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
            'site' => 'required|string|max:255',
            'id_lokasi' => 'nullable|exists:m_lokasi,id_lokasi',
            'keterangan' => 'nullable|string',
            'serialnumber' => 'required|string|max:255|unique:m_perangkat,serialnumber,' . $perangkat . ',id_perangkat',
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
}

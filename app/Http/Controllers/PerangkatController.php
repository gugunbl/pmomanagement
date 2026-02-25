<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Perangkat;
use App\Project;
use App\Jenisperangkat;
use Illuminate\Support\Facades\DB; // ← INI YANG DITAMBAHKAN


class PerangkatController extends Controller
{
public function show(Perangkat $perangkatss)
    {
        $title = "Informasi Detail Project";
        $perangkatss->load(['statusImplementation','statusMaintenance','mcategory','mPMImplementasi','mPMMaintenance']);
        return view('perangkat.show', compact('title', 'perangkatss'));
    }




public function index(Request $request)
{
    $query = Perangkat::with('project');

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

        return view('perangkat.create', compact('projectes','jenisperangkates','sitelist','statusperangkat'));
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
            'upload_do' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
        $doPath = null;

        if ($request->hasFile('upload_do')) {
            $doPath = $request->file('upload_do')->store('uploads/do', 'public');
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
                    'upload_do' => $doPath,
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
                    'status' => 'Warehouse',
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



}

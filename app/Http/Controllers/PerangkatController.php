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
    //
/*     public function index()
    {

    $perangkats = Perangkat::with(['project' => function($query) {
            $query->select('id_project', 'nama_project');
        }])
        ->select('id_perangkat', 'id_project', 'serialnumber', 'merk', 'type')
        //->get();
        ->orderBy('id_perangkat', 'desc') // ORDER BY id_perangkat DESC
        ->paginate(15);

    $title =  'Manage Perangkat';    
    return view('perangkat.index', compact('perangkats','title'));
    }
*/


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
        // Validasi dan simpan data
            // Validasi input
        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'start_warranty' => 'nullable|date_format:Y-m-d',
            'end_warranty' => 'nullable|date_format:Y-m-d',
            'tgl_online' => 'nullable|date_format:Y-m-d',
            'merk' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'serialnumber' => 'required|string|max:255',
            'site' => 'required|string|max:255',
            'lantai' => 'nullable|string|max:255',
            'jenis' => 'required|exists:m_jenis_perangkat,id_jenis',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|max:255',
            
            ]);
        $validated['user_id'] = Auth::id();
        // Jika ada field status_implementation, pastikan nilainya valid
        // (sudah divalidasi di atas)
        
        try {
            // Gunakan transaction untuk konsistensi data
            DB::beginTransaction();
            
            // Create project
            Perangkat::create($validated);
            
            DB::commit();
            
            flash('Perangkat created successfully!')->success();
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

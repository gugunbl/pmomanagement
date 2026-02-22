<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProjectRequest;
use App\Project;
use App\Category;
use App\Status;
use App\ProjectTopImplementasi;
use App\ProjectTopMaintenance;
use App\ProjectReport;
use App\Mprojectmanager;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Jika ingin transaction


class ProjectController extends Controller
{


public function maintenance(Request $request)
{
    $tahun = $request->tahun;
    $pm_maintenance = $request->pm_maintenance;
    $projects = DB::table('m_project as p')
        ->leftJoin('project_top_maintenance as ptm', 'ptm.project_id', '=', 'p.id_project')
        ->leftJoin('m_project_manager as pm', 'pm.id_pm', '=', 'p.pm_maintenance')
        ->select(
    'p.id_project',
    'p.nomor_spk',
    'p.nama_project',
    'p.tanggal_mulai_maintenance',
    'p.tanggal_akhir_maintenance',
    'pm.nama_pm as pm_maintenance_nama',
    DB::raw('COUNT(ptm.id) as total_ba'),
    DB::raw("COUNT(CASE WHEN ptm.status = 'paid' THEN 1 END) as total_paid")
)

        ->where('p.status_maintenance', 1)

        // filter tahun
        ->when($tahun, function ($query) use ($tahun) {
            $query->whereYear('p.tanggal_akhir_maintenance', $tahun);
        })

        // filter PM (pakai ID, bukan nama)
        ->when($pm_maintenance, function ($query) use ($pm_maintenance) {
            $query->where('p.pm_maintenance', $pm_maintenance);
        })

->groupBy(
    'p.id_project',
    'p.nomor_spk',
    'p.nama_project',
    'p.tanggal_mulai_maintenance',
    'p.tanggal_akhir_maintenance',
    'pm.nama_pm'
)
        ->orderBy('p.tanggal_akhir_maintenance', 'asc')
        ->paginate(10)
        ->appends($request->query());

   $listPM = DB::table('m_project_manager')
    ->where('status', 1)
    ->where('user_id',2)
    ->orderBy('nama_pm')
    ->get();

    // dropdown tahun
$listTahun = DB::table('m_project')
    ->whereNotNull('tanggal_akhir_maintenance')
    ->selectRaw('DISTINCT YEAR(tanggal_akhir_maintenance) as tahun')
    ->orderBy('tahun', 'desc')
    ->pluck('tahun');

    return view('project.maintenance', compact('projects', 'listTahun', 'tahun','listPM','pm_maintenance'));
}



public function store(Request $request)
{
    // ================= VALIDASI =================
    $validated = $request->validate([
        'tanggal_spk' => 'required|date_format:Y-m-d',
        'nama_project' => 'required|string|max:255',
        'nomor_spk' => 'required|string|max:255',
        'link_dokumen' => 'nullable|string|max:255',
        'keterangan' => 'nullable|string',

        'category' => 'required|exists:categories,id',
        'customer' => 'nullable|string',
        'sales' => 'nullable|string',

        'status_implementation' => 'required|exists:m_status,id_status',
        'status_maintenance' => 'nullable|exists:m_status,id_status',

        'pm_implementasi' => 'nullable|exists:m_project_manager,id_pm',
        'pm_maintenance' => 'nullable|exists:m_project_manager,id_pm',

        'tanggal_kom'                => 'nullable|date_format:Y-m-d',
        'tanggal_akhir_implementasi' => 'nullable|date_format:Y-m-d',
        'tanggal_mulai_maintenance'  => 'nullable|date_format:Y-m-d',
        'tanggal_akhir_maintenance'  => 'nullable|date_format:Y-m-d',

        // TOP IMPLEMENTASI (MASTER)
        'top_impl_total' => 'nullable|integer|min:1|max:20',
    ]);

    DB::transaction(function () use ($validated, $request) {

        // ================= SIMPAN PROJECT =================
        $validated['tahun'] = Carbon::createFromFormat(
            'Y-m-d',
            $validated['tanggal_spk']
        )->year;

        $validated['user_id'] = Auth::id();

        // default jika kosong
        $validated['top_impl_total'] = $validated['top_impl_total'] ?? 0;

        $project = Project::create($validated);

        // ================= SIMPAN TOP IMPLEMENTASI =================
        if ($project->top_impl_total > 0) {

            $topData = $request->top_impl ?? [];

            for ($i = 1; $i <= $project->top_impl_total; $i++) {

                ProjectTopImplementasi::create([
                    'project_id'   => $project->id_project,
                    'top_ke'       => $i,
                    'tanggal_top'  => $topData[$i]['tanggal'] ?? null,
                    'status'       => $topData[$i]['status'] ?? 'planned',
                    'berita_acara' => $topData[$i]['berita_acara'] ?? null,
                ]);
            }
        }
    });

    flash('Project & TOP Implementasi berhasil disimpan')->success();
    return redirect()->route('project.index');
}






    //
    public function index(Request $request)
    {
    $tipepm = [
        'implementasi' => 'Implementasi',
        'maintenance' => 'Maintenance'
    ];

    $query = Project::leftjoin('m_status as s', 'm_project.status_maintenance', '=', 's.id_status')
        ->leftjoin('m_status as ss', 'm_project.status_implementation', '=', 'ss.id_status')
        ->leftjoin('m_project_manager as pmi', 'm_project.pm_implementasi', '=', 'pmi.id_pm')
        ->leftjoin('m_project_manager as pmm', 'm_project.pm_maintenance', '=', 'pmm.id_pm')
        ->select(
            'm_project.id_project',
            'm_project.tanggal_spk',
            'm_project.tahun',
            'm_project.nama_project',
            'm_project.nomor_spk',
            'm_project.customer',
            'm_project.status_maintenance',
            's.nama_status as sta_m',
            'pmm.nama_pm as pm_maintenance',
            'm_project.status_implementation',
            'ss.nama_status as sta_i',
            'pmi.nama_pm as pm_implementasi'
        );
    
    // Filter status yang is_type = 1 (sesuai dengan kode existing)
    $query->where('s.is_type', 1)
          ->where('ss.is_type', 1)
          ->where('pmi.status', 1)
          ->where('pmm.status', 1);
    
    // Filter berdasarkan tipe (Implementasi/Maintenance)
    if ($request->has('status_maintenance') && !empty($request->status_maintenance)) {
        if ($request->status_maintenance == 'implementasi') {
            // Filter hanya project dengan status_implementation = 1
            $query->where('m_project.status_implementation', 1);
        } elseif ($request->status_maintenance == 'maintenance') {
            // Filter hanya project dengan status_maintenance = 1
            $query->where('m_project.status_maintenance', 1);
        }
    }
    
    // Search dengan OR (pakai grouping untuk aman)
    if ($request->has('search') && !empty($request->search)) {
        $query->where(function($q) use ($request) {
            $q->where('m_project.nama_project', 'like', '%'.$request->search.'%')
              ->orWhere('m_project.tahun', 'like', '%'.$request->search.'%');
        });
    }
    
    // Order by
    $query->orderBy('m_project.tanggal_spk', 'desc');
    
    // Paginate
    $projects = $query->paginate(15);
    
    $title = 'Manage Project Infodata'; 
    $statusma = \App\Status::where('is_type', 1)->pluck('nama_status', 'id_status');
    
    return view('project.index', compact('projects', 'title', 'statusma', 'tipepm'));
    }


/*    public function edit(Project $project)
    {
        $title = "Informasi Project Details";
        $project->load(['statusImplementation','statusMaintenance','mcategory']);
        $categories = Category::pluck('category_name', 'id');
        $mstatus = Status::pluck('nama_status','id_status');
        $mprojectmanager = Mprojectmanager::pluck('nama_pm','id_pm');
        return view('project.edit', compact('mprojectmanager','title','categories', 'project','mstatus'));
    }
*/


public function edit(Project $project)
{
    $title = 'Edit Project';

    $project->load([
        'topImplementasi',
        'topMaintenance',
        'reports'
    ]);
    $clients = [
            'BRI' => 'BRI',
            'BJB' => 'BJB', 
            'PT Timah' => 'PT Timah',
            'Allo Bank' => 'Allo Bank',
            'PNM' => 'PNM',
            'Bank DKI' => 'Bank DKI',
            'Jamkrindo Syariah' => 'Jamkrindo Syariah',
            'Askrindo' => 'Askrindo'
        ];
        $saless = [
            'Putri' => 'Putri',
            'Sigit' => 'Sigit',
            'Wandi' => 'Wandi'
        ];

    $categories = Category::pluck('category_name', 'id');
    $statusim   = Status::where('is_type', 1)->pluck('nama_status', 'id_status');
    $statusma = \App\Status::where('is_type',1)->pluck('nama_status','id_status');
    $mprojectmanager = Mprojectmanager::pluck('nama_pm', 'id_pm');

    return view('project.edit', compact(
        'project',
        'title',
        'categories',
        'statusim',
        'statusma',
        'clients',
        'saless',
        'mprojectmanager'
    ));
}


public function update(Request $request, Project $project)
{
    DB::transaction(function () use ($request, $project) {

        /* ===============================
         * 1️⃣ UPDATE PROJECT INFO
         * =============================== */
        $project->update([
            'nama_project'          => $request->nama_project,
            'nomor_spk'             => $request->nomor_spk,
            'tanggal_spk'           => $request->tanggal_spk,
            'status_implementation' => $request->status_implementation,
            'status_maintenance'    => $request->status_maintenance,
            'customer'              => $request->customer,
            'category'              => $request->category,
            'pm_implementasi'       => $request->pm_implementasi,
            'pm_maintenance'        => $request->pm_maintenance,
            'sales'                 => $request->sales,
            'link_dokumen'          => $request->link_dokumen,
            'keterangan'            => $request->keterangan,
            'tanggal_akhir_implementasi'    => $request->tanggal_akhir_implementasi,
            'tanggal_mulai_maintenance'     => $request->tanggal_mulai_maintenance,
            'tanggal_akhir_maintenance'     => $request->tanggal_akhir_maintenance,
        ]);

        /* ===============================
         * 2️⃣ UPDATE TOP IMPLEMENTASI
         * =============================== */
        if ($request->filled('top_impl_total')) {

            $project->update([
                'top_impl_total' => $request->top_impl_total
            ]);

            $rows = $request->top_impl ?? [];

            for ($i = 1; $i <= $request->top_impl_total; $i++) {

                if (!isset($rows[$i])) continue;

                ProjectTopImplementasi::updateOrCreate(
                    [
                        'project_id' => $project->id_project,
                        'top_ke'     => $i,
                    ],
                    [
                        'tanggal_top'  => $rows[$i]['tanggal'] ?? null,
                        'status'       => $rows[$i]['status'] ?? 'planned',
                        'berita_acara' => $rows[$i]['berita_acara'] ?? null,
                    ]
                );
            }

            // hapus TOP yg dikurangi
            ProjectTopImplementasi::where('project_id', $project->id_project)
                ->where('top_ke', '>', $request->top_impl_total)
                ->delete();
        }

        /* ===============================
         * 3️⃣ UPDATE TOP MAINTENANCE
         * =============================== */
        if ($request->filled('top_maintenance_total')) {

            $project->update([
                'top_maintenance_total' => $request->top_maintenance_total
            ]);

            $rows = $request->top_maintenance ?? [];

            for ($i = 1; $i <= $request->top_maintenance_total; $i++) {

                if (!isset($rows[$i]['start'])) continue;

                ProjectTopMaintenance::updateOrCreate(
                    [
                        'project_id' => $project->id_project,
                        'top_ke'     => $i,
                    ],
                    [
                        'periode_start' => $rows[$i]['start'],
                        'periode_end'   => $rows[$i]['end'] ?? null,
                        'status'        => $rows[$i]['status'] ?? 'planned',
                        'keterangan'    => $rows[$i]['keterangan'] ?? null,
                    ]
                );
            }

            ProjectTopMaintenance::where('project_id', $project->id_project)
                ->where('top_ke', '>', $request->top_maintenance_total)
                ->delete();
        }

        /* ===============================
         * 4️⃣ UPDATE PROJECT REPORTS
         * =============================== */
        if ($request->filled('project_report_total')) {

            $project->update([
                'project_report_total' => $request->project_report_total
            ]);

            $rows = $request->project_reports ?? [];

            for ($i = 1; $i <= $request->project_report_total; $i++) {

                if (!isset($rows[$i])) continue;

                \App\ProjectReport::updateOrCreate(
                    [
                        'project_id' => $project->id_project,
                        'report_ke'  => $i,
                    ],
                    [
                        'period_start'    => $rows[$i]['start'] ?? null,
                        'period_end'      => $rows[$i]['end'] ?? null,
                        'submission_date' => $rows[$i]['submission_date'] ?? null,
                        'status'          => $rows[$i]['status'] ?? 'progress',
                        'keterangan'      => $rows[$i]['keterangan'] ?? null,
                    ]
                );
            }

            \App\ProjectReport::where('project_id', $project->id_project)
                ->where('report_ke', '>', $request->project_report_total)
                ->delete();
        }
    });

    flash('Project berhasil diperbarui')->success();
    return redirect()->back();
}




    public function show(Project $project)
    {
        $title = "Informasi Detail Project";
        $project->load(['statusImplementation','statusMaintenance','mcategory','mPMImplementasi','mPMMaintenance']);
        return view('project.show', compact('title', 'project'));
    }



    public function destroy($id)
    {
    // atau jika menggunakan route model binding
    // public function destroy(Project $project)
        $project = Project::findOrFail($id);
        $project->delete();
    
        return redirect()->route('project.index')
        ->with('success', 'Project deleted successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

         $clients = [
            'BRI' => 'BRI',
            'BJB' => 'BJB', 
            'PT Timah' => 'PT Timah',
            'Allo Bank' => 'Allo Bank',
            'PNM' => 'PNM',
            'Bank DKI' => 'Bank DKI',
            'Jamkrindo Syariah' => 'Jamkrindo Syariah',
            'Askrindo' => 'Askrindo'
        ];
        $saless = [
            'Putri' => 'Putri',
            'Sigit' => 'Sigit',
            'Wandi' => 'Wandi'
        ];

        $title = 'Tambah Project Baru';
        $categories = Category::pluck('category_name', 'id');
        $statusim = \App\Status::where('is_type',1)->pluck('nama_status','id_status');
        $statusma = \App\Status::where('is_type',1)->pluck('nama_status','id_status');
        $mprojectmanager = \App\Mprojectmanager::pluck('nama_pm','id_pm');
        
        return view('project.create', compact('categories','statusim','statusma','title','mprojectmanager','clients','saless'));
       
    }


    public function reportMonitoring()
    {
        $reports = \App\ProjectReport::with('project')
            ->orderBy('period_end', 'desc')
            ->get();
            
        $title = 'Monitoring Laporan';
        return view('project.monitoring-laporan', compact('reports', 'title'));
    }

    public function reportMonitoringUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:project_reports,id',
            'field' => 'required|in:status,keterangan',
            'value' => 'nullable|string'
        ]);

        $report = \App\ProjectReport::findOrFail($request->id);
        $report->{$request->field} = $request->value;
        $report->save();

        return response()->json(['success' => true]);
    }
}

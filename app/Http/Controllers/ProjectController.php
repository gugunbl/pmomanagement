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
use App\ProjectReportComment;
use App\ProjectReportStatusLog;
use App\ProjectTopMaintenanceComment;
use App\ProjectTopMaintenanceStatusLog;
use App\ProjectComment;
use App\Mprojectmanager;
use App\MCustomer;
use App\MSales;
use App\TeamSupport;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Jika ingin transaction
use Illuminate\Support\Facades\Storage;
use App\Exports\MonitoringBAExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:send-maintenance-reminder', ['only' => ['previewMaintenanceReminder', 'sendMaintenanceReminder']]);
    }

    public function previewMaintenanceReminder()
    {
        $recipientEmails = setting('maintenance_reminder_to');
        if (empty($recipientEmails)) {
            flash('Error: Recipient email is not configured in Settings!')->error();
            return back();
        }

        $subject = setting('maintenance_reminder_subject', 'Reminder Maintenance Project');
        $bodyMessage = setting('maintenance_reminder_body', "Izin reminder kembali untuk data project yang sudah berakhir dan akan berakhir dalam 5 bulan ke depan.");

        $today = Carbon::today();
        $fiveMonthsFromNow = Carbon::today()->addMonthsNoOverflow(5)->endOfMonth();

        // 1. Expired Projects
        $expiredProjects = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->whereNotNull('tanggal_akhir_maintenance')
            ->where('tanggal_akhir_maintenance', '<', $today->toDateString())
            ->orderBy('tanggal_akhir_maintenance', 'desc')
            ->get();

        // 2. Ending Soon (<= 5 Months)
        $endingSoonProjects = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->whereNotNull('tanggal_akhir_maintenance')
            ->whereBetween('tanggal_akhir_maintenance', [$today->toDateString(), $fiveMonthsFromNow->toDateString()])
            ->orderBy('tanggal_akhir_maintenance', 'asc')
            ->get();

        if ($expiredProjects->isEmpty() && $endingSoonProjects->isEmpty()) {
            flash('No projects found ending soon or expired. Email not sent.')->info();
            return back();
        }

        $title = 'Preview Maintenance Reminder';
        return view('project.preview-reminder', compact(
            'expiredProjects', 'endingSoonProjects', 'bodyMessage', 'subject', 'recipientEmails', 'title'
        ));
    }

    public function sendMaintenanceReminder(Request $request)
    {
        $recipientEmails = setting('maintenance_reminder_to');
        if (empty($recipientEmails)) {
            flash('Error: Recipient email is not configured in Settings!')->error();
            return redirect()->route('home');
        }

        // Convert list to array
        $toArr = array_map('trim', explode(',', $recipientEmails));
        $subject = setting('maintenance_reminder_subject', 'Reminder Maintenance Project');
        $bodyMessage = setting('maintenance_reminder_body', "Izin reminder kembali untuk data project yang sudah berakhir dan akan berakhir dalam 5 bulan ke depan.");

        $today = Carbon::today();
        $fiveMonthsFromNow = Carbon::today()->addMonthsNoOverflow(5)->endOfMonth();

        // 1. Expired Projects
        $expiredProjects = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->whereNotNull('tanggal_akhir_maintenance')
            ->where('tanggal_akhir_maintenance', '<', $today->toDateString())
            ->orderBy('tanggal_akhir_maintenance', 'desc')
            ->get();

        // 2. Ending Soon (<= 5 Months)
        $endingSoonProjects = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->whereNotNull('tanggal_akhir_maintenance')
            ->whereBetween('tanggal_akhir_maintenance', [$today->toDateString(), $fiveMonthsFromNow->toDateString()])
            ->orderBy('tanggal_akhir_maintenance', 'asc')
            ->get();

        if ($expiredProjects->isEmpty() && $endingSoonProjects->isEmpty()) {
            flash('No projects found ending soon or expired. Email not sent.')->info();
            return redirect()->route('home');
        }

        try {
            $fromAddress = env('MAIL_FROM_ADDRESS', 'manage.service.isdu@gmail.com');
            $fromName = env('MAIL_FROM_NAME', 'PMO Maintenance');

            Mail::send('emails.maintenance-reminder', [
                'bodyMessage' => $bodyMessage,
                'expiredProjects' => $expiredProjects,
                'endingSoonProjects' => $endingSoonProjects,
            ], function ($message) use ($toArr, $subject, $fromAddress, $fromName) {
                $message->to($toArr)
                        ->subject($subject)
                        ->from($fromAddress, $fromName);
            });

            // LOG SUCCESS to Activity Log
            activity('email-reminder')
                ->causedBy(Auth::user())
                ->withProperties([
                    'recipients' => $recipientEmails,
                    'subject' => $subject,
                    'total_expired' => $expiredProjects->count(),
                    'total_ending_soon' => $endingSoonProjects->count(),
                    'status' => 'success'
                ])
                ->log('Maintenance reminder email sent successfully');

            flash('Maintenance reminder email sent successfully to: ' . $recipientEmails)->success();
        } catch (\Exception $e) {
            // LOG FAILURE to Activity Log
            activity('email-reminder')
                ->causedBy(Auth::user())
                ->withProperties([
                    'recipients' => $recipientEmails,
                    'subject' => $subject,
                    'error_message' => $e->getMessage(),
                    'status' => 'failed'
                ])
                ->log('Failed to send maintenance reminder email');

            flash('Error sending email: ' . $e->getMessage())->error();
        }

        return redirect()->route('home');
    }

public function maintenance(Request $request)
{
    $tahun = $request->tahun;
    $pm_maintenance = $request->pm_maintenance;
    $customer = $request->customer;

    $projects = DB::table('m_project as p')
        ->leftJoin('project_top_maintenance as ptm', 'ptm.project_id', '=', 'p.id_project')
        ->leftJoin('m_project_manager as pm', 'pm.id_pm', '=', 'p.pm_maintenance')
        ->select(
    'p.id_project',
    'p.nomor_spk',
    'p.nama_project',
    'p.tanggal_mulai_maintenance',
    'p.tanggal_akhir_maintenance',
    'p.status_maintenance',
    'p.customer',
    'pm.nama_pm as pm_maintenance_nama',
    DB::raw('COUNT(ptm.id) as total_ba'),
    DB::raw("COUNT(CASE WHEN ptm.status = 'paid' THEN 1 END) as total_paid")
)

        ->whereIn('p.status_maintenance', [1, 2])

        // filter tahun
        ->when($tahun, function ($query) use ($tahun) {
            $query->whereYear('p.tanggal_akhir_maintenance', $tahun);
        })

        // filter PM (pakai ID, bukan nama)
        ->when($pm_maintenance, function ($query) use ($pm_maintenance) {
            $query->where('p.pm_maintenance', $pm_maintenance);
        })

        // filter Customer
        ->when($customer, function ($query) use ($customer) {
            $query->where('p.customer', $customer);
        })

        ->groupBy(
            'p.id_project',
            'p.nomor_spk',
            'p.nama_project',
            'p.tanggal_mulai_maintenance',
            'p.tanggal_akhir_maintenance',
            'p.status_maintenance',
            'p.customer',
            'pm.nama_pm'
        )
        ->orderBy('p.status_maintenance', 'asc')
        ->orderBy('p.tanggal_akhir_maintenance', 'asc')
        ->paginate(10)
        ->appends($request->query());

   $listPM = DB::table('m_project_manager')
    ->where('status', 1)
    ->where('user_id',2)
    ->orderBy('nama_pm')
    ->get();

    // dropdown customer
    $listCustomer = DB::table('m_customers')
        ->where('status', 1)
        ->orderBy('name')
        ->pluck('name', 'name');

    // dropdown tahun
$listTahun = DB::table('m_project')
    ->whereNotNull('tanggal_akhir_maintenance')
    ->selectRaw('DISTINCT YEAR(tanggal_akhir_maintenance) as tahun')
    ->orderBy('tahun', 'desc')
    ->pluck('tahun');

    // Count active projects per PM Maintenance
    $pmProjectCounts = DB::table('m_project as p')
        ->join('m_project_manager as pm', 'pm.id_pm', '=', 'p.pm_maintenance')
        ->where('p.status_maintenance', 1)
        ->where('pm.status', 1)
        ->select('pm.nama_pm', DB::raw('COUNT(p.id_project) as total_projects'))
        ->groupBy('pm.nama_pm')
        ->orderBy('pm.nama_pm')
        ->get();

    return view('project.maintenance', compact('projects', 'listTahun', 'tahun','listPM','pm_maintenance', 'listCustomer', 'customer', 'pmProjectCounts'));
}



public function store(Request $request)
{
    // ================= VALIDASI =================
    $validated = $request->validate([
        'tanggal_spk' => 'required|date_format:Y-m-d',
        'nama_project' => 'required|string|max:255',
        'nomor_spk' => 'required|string|max:255',
        'link_dokumen' => config('filesystems.project_report_disk') === 'nextcloud'
            ? Project::nextcloudFolderValidationRules()
            : 'nullable|string|max:255',
        'keterangan' => 'nullable|string',

        'category' => 'required|exists:categories,id',
        'customer' => 'nullable|string',
        'sales' => 'nullable|string',

        'status_implementation' => 'required|exists:m_status,id_status',
        'status_maintenance' => 'nullable|exists:m_status,id_status',

        'pm_implementasi' => 'required|exists:m_project_manager,id_pm',
        'pm_maintenance' => 'required|exists:m_project_manager,id_pm',

        'tanggal_kom'                => 'nullable|date_format:Y-m-d',
        'tanggal_akhir_implementasi' => 'nullable|date_format:Y-m-d',
        'tanggal_mulai_maintenance'  => 'nullable|date_format:Y-m-d',
        'tanggal_akhir_maintenance'  => 'nullable|date_format:Y-m-d',
        'tipe_maintenance'           => 'nullable|string|in:Manage Service,PM-CM',
        'team_support'               => 'nullable|array',
        'team_support.*'             => 'exists:m_team_supports,id',

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

        // Remove team_support from validated before creating project (it's a pivot)
        $teamSupportIds = $validated['team_support'] ?? [];
        unset($validated['team_support']);

        $project = Project::create($validated);

        // Sync team supports pivot
        $project->teamSupports()->sync($teamSupportIds);

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
    if (!Auth::user()->canEditProject($project)) {
        abort(403, 'Akses ditolak: Anda bukan PM Implementasi atau PM Maintenance untuk project ini. Hanya Admin, PM Implementasi, atau PM Maintenance yang dapat mengedit project ini.');
    }

    $title = 'Edit Project';

    $project->load([
        'topImplementasi',
        'topMaintenance',
        'reports',
        'teamSupports'
    ]);
    $clients = MCustomer::where('status', 1)->pluck('name', 'name');
    $saless = MSales::where('status', 1)->pluck('name', 'name');

    $categories = Category::pluck('category_name', 'id');
    $statusim   = Status::where('is_type', 1)->pluck('nama_status', 'id_status');
    $statusma = \App\Status::where('is_type',1)->pluck('nama_status','id_status');
    $mprojectmanager = Mprojectmanager::pluck('nama_pm', 'id_pm');
    $teamSupports = TeamSupport::where('status', 1)->pluck('name', 'id');
    $selectedTeamSupports = $project->teamSupports->pluck('id')->toArray();

    return view('project.edit', compact(
        'project',
        'title',
        'categories',
        'statusim',
        'statusma',
        'clients',
        'saless',
        'mprojectmanager',
        'teamSupports',
        'selectedTeamSupports'
    ));
}


public function update(Request $request, Project $project)
{
    if (!Auth::user()->canEditProject($project)) {
        abort(403, 'Akses ditolak: Anda bukan PM Implementasi atau PM Maintenance untuk project ini. Hanya Admin, PM Implementasi, atau PM Maintenance yang dapat mengedit project ini.');
    }

    $request->validate([
        'pm_implementasi'          => 'required|exists:m_project_manager,id_pm',
        'pm_maintenance'           => 'required|exists:m_project_manager,id_pm',
        'nama_project'             => 'required|string|max:255',
        'nomor_spk'                => 'required|string|max:255',
        'tanggal_spk'              => 'required|date_format:Y-m-d',
        'link_dokumen'             => config('filesystems.project_report_disk') === 'nextcloud'
            ? Project::nextcloudFolderValidationRules()
            : 'nullable|string|max:255',
        'tipe_maintenance'         => 'nullable|string|in:Manage Service,PM-CM',
        'top_maintenance_total'    => 'required|integer|min:1|max:60',
        'top_maintenance_signer_total' => 'nullable|integer|min:0|max:10',
        'top_maintenance_notes'    => 'nullable|string|max:1000',
        'top_maintenance_signers'  => 'nullable|array',
        'top_maintenance_signers.*'=> 'nullable|array',
        'top_maintenance_signers.*.*' => 'nullable|in:1',
        'team_support'             => 'nullable|array',
        'team_support.*'           => 'exists:m_team_supports,id',
    ]);

    DB::transaction(function () use ($request, $project) {

        /* ===============================
         * 1️⃣ UPDATE PROJECT INFO
         * =============================== */
        $project->update([
            'nama_project'                  => $request->nama_project,
            'nomor_spk'                     => $request->nomor_spk,
            'tanggal_spk'                   => $request->tanggal_spk,
            'status_implementation'         => $request->status_implementation,
            'status_maintenance'            => $request->status_maintenance,
            'customer'                      => $request->customer,
            'category'                      => $request->category,
            'pm_implementasi'               => $request->pm_implementasi,
            'pm_maintenance'                => $request->pm_maintenance,
            'sales'                         => $request->sales,
            'link_dokumen'                  => $request->link_dokumen,
            'keterangan'                    => $request->keterangan,
            'tanggal_kom'                   => $request->tanggal_kom,
            'tanggal_akhir_implementasi'    => $request->tanggal_akhir_implementasi,
            'tanggal_mulai_maintenance'     => $request->tanggal_mulai_maintenance,
            'tanggal_akhir_maintenance'     => $request->tanggal_akhir_maintenance,
            'tipe_maintenance'              => $request->tipe_maintenance,
            'top_maintenance_signer_total'  => $request->top_maintenance_signer_total ?? 0,
            'top_maintenance_notes'         => $request->top_maintenance_notes ?? null,
        ]);

        // Sync team supports (many-to-many)
        $project->teamSupports()->sync($request->team_support ?? []);

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

            $signerRows = $request->top_maintenance_signers ?? [];

            for ($i = 1; $i <= $request->top_maintenance_total; $i++) {

                if (!isset($rows[$i]['start'])) continue;

                $signers = isset($signerRows[$i]) ? array_keys(array_filter($signerRows[$i], function ($value) {
                    return $value == '1';
                })) : [];

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
                        'signers'       => !empty($signers) ? json_encode($signers) : null,
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

                $existingReport = \App\ProjectReport::where('project_id', $project->id_project)
                    ->where('report_ke', $i)
                    ->first();

                $newStatus = $rows[$i]['status'] ?? 'progress';

                $updatedReport = \App\ProjectReport::updateOrCreate(
                    [
                        'project_id' => $project->id_project,
                        'report_ke'  => $i,
                    ],
                    [
                        'period_start'    => $rows[$i]['start'] ?? null,
                        'period_end'      => $rows[$i]['end'] ?? null,
                        'submission_date' => $rows[$i]['submission_date'] ?? null,
                        'status'          => $newStatus,
                        'keterangan'      => $rows[$i]['keterangan'] ?? null,
                    ]
                );

                if ($existingReport && $existingReport->status !== $newStatus) {
                    ProjectReportStatusLog::create([
                        'project_report_id' => $updatedReport->id,
                        'user_id' => Auth::id(),
                        'old_status' => $existingReport->status,
                        'new_status' => $newStatus,
                    ]);
                }
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
        $project->load(['statusImplementation','statusMaintenance','mcategory','mPMImplementasi','mPMMaintenance','teamSupports','reports.comments.user','reports.statusLogs.user']);
        return view('project.show', compact('title', 'project'));
    }



    public function destroy($project)
    {
    // atau jika menggunakan route model binding
    // public function destroy(Project $project)
        $project = Project::findOrFail($project);
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
        $project = new Project();
        $project->tanggal_spk = '1900-01-01';
        $project->tanggal_kom = '1900-01-01';
        $project->tanggal_akhir_implementasi = '1900-01-01';
        $project->tanggal_mulai_maintenance = '1900-01-01';
        $project->tanggal_akhir_maintenance = '1900-01-01';
        
        $defaultValue = \App\Mprojectmanager::where('nama_pm', 'Belum ada PM')->first();
        if ($defaultValue) {
            $project->pm_maintenance = $defaultValue->id_pm;
        }

        $clients = MCustomer::where('status', 1)->pluck('name', 'name');
        $saless = MSales::where('status', 1)->pluck('name', 'name');

        $title = 'Tambah Project Baru';
        $categories = Category::pluck('category_name', 'id');
        $statusim = \App\Status::where('is_type',1)->pluck('nama_status','id_status');
        $statusma = \App\Status::where('is_type',1)->pluck('nama_status','id_status');
        $mprojectmanager = \App\Mprojectmanager::pluck('nama_pm','id_pm');
        $teamSupports = TeamSupport::where('status', 1)->pluck('name', 'id');
        $selectedTeamSupports = [];
        
        return view('project.create', compact('project','categories','statusim','statusma','title','mprojectmanager','clients','saless', 'teamSupports', 'selectedTeamSupports'));
       
    }


    public function reportMonitoring(Request $request)
    {
        $request->validate([
            'customer' => 'nullable|string|max:255',
            'project' => 'nullable|integer|min:1',
        ]);

        $selectedCustomer = trim((string) $request->query('customer', ''));
        $selectedCustomer = $selectedCustomer !== '' ? $selectedCustomer : null;
        $selectedProjectId = $selectedCustomer !== null && $request->filled('project')
            ? (int) $request->query('project')
            : null;

        $query = ProjectReport::with(['project.comments.user', 'comments.user'])
            ->whereHas('project');

        if ($selectedCustomer !== null) {
            $query->whereHas('project', function ($projectQuery) use ($selectedCustomer) {
                $projectQuery->where('customer', $selectedCustomer);
            });
        }

        if ($selectedProjectId !== null) {
            $query->where('project_id', $selectedProjectId);
        }

        $mainQuery = clone $query;
        $mainQuery->join('m_project', 'project_reports.project_id', '=', 'm_project.id_project')
            ->select('project_reports.*')
            ->selectRaw('(SELECT MIN(period_end) FROM project_reports as pr2 WHERE pr2.project_id = project_reports.project_id) as min_period_end')
            ->orderBy('min_period_end', 'asc')
            ->orderBy('m_project.nama_project', 'asc')
            ->orderBy('project_reports.period_end', 'asc');

        $queryForOffset = clone $mainQuery;

        $reports = $mainQuery->paginate(15)->appends($request->query());

        $offset = ($reports->currentPage() - 1) * $reports->perPage();
        $startProjectCounter = 0;
        if ($offset > 0) {
            $prevReports = $queryForOffset->limit($offset)->pluck('project_reports.project_id');
            $startProjectCounter = collect($prevReports)->unique()->count();
            if ($reports->count() > 0 && collect($prevReports)->last() == $reports->first()->project_id) {
                $startProjectCounter--;
            }
        }

        $customerOptions = Project::query()
            ->whereHas('reports')
            ->whereNotNull('customer')
            ->where('customer', '<>', '')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');

        $projectOptions = collect();
        if ($selectedCustomer !== null) {
            $projectOptions = Project::query()
                ->whereHas('reports')
                ->where('customer', $selectedCustomer)
                ->orderBy('nama_project')
                ->get(['id_project', 'nama_project']);
        }

        $title = 'Monitoring Laporan';
        return view('project.monitoring-laporan', compact(
            'reports',
            'title',
            'startProjectCounter',
            'customerOptions',
            'projectOptions',
            'selectedCustomer',
            'selectedProjectId'
        ));
    }

    public function reportMonitoringUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:project_reports,id',
            'field' => 'required|in:status',
            'value' => 'nullable|string'
        ]);

        $report = ProjectReport::findOrFail($request->id);
        $oldStatus = $report->status;
        $newStatus = $request->value;

        if ($oldStatus !== $newStatus) {
            $report->status = $newStatus;
            $report->save();

            ProjectReportStatusLog::create([
                'project_report_id' => $report->id,
                'user_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            $statusNote = sprintf(
                'Laporan %s: Status changed from %s to %s oleh %s',
                $report->report_ke,
                ucfirst($oldStatus),
                ucfirst($newStatus),
                Auth::user()->name ?? 'Unknown'
            );

            ProjectComment::create([
                'project_id' => $report->project_id,
                'user_id' => Auth::id(),
                'comment' => $statusNote,
            ]);

            return response()->json(['success' => true, 'reload' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function reportCommentStore(Request $request, ProjectReport $report)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        ProjectReportComment::create([
            'project_report_id' => $report->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        flash('Komentar laporan berhasil disimpan!')->success();
        return back();
    }

    public function reportUploadForm(ProjectReport $report)
    {
        abort_unless(Auth::user()->can('monitoring-laporan-upload-laporan'), 403);
        abort_unless($report->status === 'signoff', 403);

        $uploadFileName = $report->uploadFilename();

        return view('project.report-upload', compact('report', 'uploadFileName'));
    }

    public function reportUpload(Request $request, ProjectReport $report)
    {
        abort_unless(Auth::user()->can('monitoring-laporan-upload-laporan'), 403);
        abort_unless($report->status === 'signoff', 403);

        $maxUploadKb = intval(config('filesystems.project_report_max_upload_kb', 51200));
        $uploadFile = $request->file('upload_file');

        if ($uploadFile && !$uploadFile->isValid()) {
            $errorCode = $uploadFile->getError();
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi upload_max_filesize pada konfigurasi PHP.',
                UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas MAX_FILE_SIZE pada form.',
                UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian. Periksa koneksi lalu coba kembali.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diterima oleh server.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload PHP tidak tersedia.',
                UPLOAD_ERR_CANT_WRITE => 'PHP tidak dapat menulis file ke folder temporary.',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.',
            ];
            $errorMessage = $uploadErrors[$errorCode] ?? 'PHP menolak file karena kesalahan upload yang tidak dikenal.';

            Log::warning('Project report upload rejected by PHP.', [
                'report_id' => $report->id,
                'error_code' => $errorCode,
                'upload_tmp_dir' => ini_get('upload_tmp_dir'),
                'system_tmp_dir' => sys_get_temp_dir(),
                'temporary_directory_exists' => is_dir(sys_get_temp_dir()),
                'temporary_directory_writable' => is_writable(sys_get_temp_dir()),
            ]);

            return back()->withErrors([
                'upload_file' => $errorMessage . ' (kode upload PHP: ' . $errorCode . ')',
            ]);
        }

        $request->validate([
            'upload_file' => 'required|file|mimes:pdf|max:' . $maxUploadKb
        ], [
            'upload_file.uploaded' => 'File gagal diterima oleh PHP. Pastikan ukuran file tidak melebihi batas upload server dan coba kembali.',
            'upload_file.max' => 'Ukuran PDF tidak boleh melebihi ' . number_format($maxUploadKb / 1024) . ' MB.',
            'upload_file.mimes' => 'File laporan harus berformat PDF.',
        ]);

        $uploadFileName = $report->uploadFilename();
        if (!$uploadFileName) {
            return back()->withErrors([
                'upload_file' => 'Periode mulai dan periode selesai harus diisi sebelum laporan dapat diupload.',
            ]);
        }

        if ($request->hasFile('upload_file')) {
            $disk = config('filesystems.project_report_disk', 'public');
            $upload = $request->file('upload_file');
            $oldPath = $report->uploaded_file;
            $oldDisk = $report->uploaded_disk ?: 'public';

            if ($disk === 'nextcloud') {
                try {
                    $nextcloud = app(\App\Services\NextcloudWebDav::class);
                    $path = $nextcloud->reportPath($report, $uploadFileName);
                    $nextcloud->upload($upload->getRealPath(), $path);
                } catch (\App\Services\NextcloudWebDavException $e) {
                    flash('Upload ke Nextcloud gagal: ' . $e->getMessage())->error();
                    return back()->withInput();
                }
            } else {
                $directory = trim(config('filesystems.project_report_path', 'project_reports/files'), '/');
                $path = $upload->storeAs($directory, $uploadFileName, $disk);
                if (!$path) {
                    flash('File laporan gagal disimpan ke storage.')->error();
                    return back()->withInput();
                }
            }

            $report->uploaded_file = $path;
            $report->uploaded_disk = $disk;
            $report->uploaded_original_name = $uploadFileName;
            $report->save();

            if ($oldPath && $oldDisk !== 'nextcloud' && ($oldPath !== $path || $oldDisk !== $disk)) {
                Storage::disk($oldDisk)->delete($oldPath);
            }
        }

        flash('File laporan berhasil diupload!')->success();
        return redirect()->route('project.report.upload.form', $report->id);
    }

    public function reportDownload(ProjectReport $report)
    {
        abort_unless(Auth::user()->can('monitoring-laporan-upload-laporan'), 403);
        abort_unless($report->uploaded_file, 404);

        $disk = $report->uploaded_disk ?: 'public';
        if ($disk === 'nextcloud') {
            try {
                $response = app(\App\Services\NextcloudWebDav::class)->download($report->uploaded_file);
            } catch (\App\Services\NextcloudWebDavException $e) {
                abort($e->getStatusCode() === 404 ? 404 : 502, $e->getMessage());
            }

            $stream = $response->getBody();
            return response()->streamDownload(function () use ($stream) {
                while (!$stream->eof()) {
                    echo $stream->read(8192);
                }
            }, $report->uploaded_original_name ?: basename($report->uploaded_file), [
                'Content-Type' => 'application/pdf',
            ]);
        }

        abort_unless(Storage::disk($disk)->exists($report->uploaded_file), 404);

        return Storage::disk($disk)->download(
            $report->uploaded_file,
            $report->uploaded_original_name ?: basename($report->uploaded_file)
        );
    }

    public function monitoringBA(Request $request)
    {
        $search = $request->search;
        $pm_maintenance = $request->pm_maintenance;
        $activeTab = $request->tab ?: 'progress';

        $query = \App\ProjectTopMaintenance::with(['project.mPMMaintenance', 'project.comments.user', 'comments.user', 'statusLogs.user'])
            ->where('status', '!=', 'paid')
            ->whereHas('project');

        // Tab filtering logic
        $today = \Carbon\Carbon::today()->format('Y-m-d');
        if ($activeTab == 'progress') {
            // Overdue or exactly today
            $query->where('periode_end', '<=', $today);
        } else {
            // Future
            $query->where('periode_end', '>', $today);
        }

        // Filter PM Maintenance
        if ($pm_maintenance) {
            $query->whereHas('project', function ($q) use ($pm_maintenance) {
                $q->where('pm_maintenance', $pm_maintenance);
            });
        }

        // Search Nama Project
        if ($search) {
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('nama_project', 'like', "%{$search}%");
            });
        }

        // --- CALCULATE SUMMARY ---
        // clone query to count before pagination
        $countQuery = clone $query;
        $totalBA = $countQuery->count();
        $totalProjects = $countQuery->distinct('project_id')->count('project_id');
        // --- END CALCULATE ---

        $mainQuery = clone $query;
        $mainQuery->join('m_project', 'project_top_maintenance.project_id', '=', 'm_project.id_project')
            ->select('project_top_maintenance.*')
            ->selectRaw('(SELECT MIN(periode_end) FROM project_top_maintenance as ptm2 WHERE ptm2.project_id = project_top_maintenance.project_id AND ptm2.status != "paid") as min_periode_end')
            ->orderBy('min_periode_end', 'asc')
            ->orderBy('m_project.nama_project', 'asc')
            ->orderBy('project_top_maintenance.periode_end', 'asc');

        $queryForOffset = clone $mainQuery;

        $listBA = $mainQuery->paginate(15)->appends($request->query());

        $offset = ($listBA->currentPage() - 1) * $listBA->perPage();
        $startProjectCounter = 0;
        if ($offset > 0) {
            $prevBAs = $queryForOffset->limit($offset)->pluck('project_top_maintenance.project_id');
            $startProjectCounter = collect($prevBAs)->unique()->count();
            if ($listBA->count() > 0 && collect($prevBAs)->last() == $listBA->first()->project_id) {
                $startProjectCounter--;
            }
        }

        $listPM = DB::table('m_project_manager')
            ->where('status', 1)
            ->where('user_id', 2)
            ->orderBy('nama_pm')
            ->get();

        $title = 'Monitoring BA (TOP Maintenance)';

        return view('project.monitoring-ba', compact('listBA', 'listPM', 'search', 'pm_maintenance', 'title', 'activeTab', 'totalProjects', 'totalBA', 'startProjectCounter'));
    }

    public function exportBAPdf(Request $request)
    {
        $search = $request->search;
        $pm_maintenance = $request->pm_maintenance;
        $activeTab = $request->tab ?: 'progress';

        $query = \App\ProjectTopMaintenance::with(['project.mPMMaintenance'])
            ->where('status', '!=', 'paid')
            ->whereHas('project');

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        if ($activeTab == 'progress') {
            $query->where('periode_end', '<=', $today);
        } else {
            $query->where('periode_end', '>', $today);
        }

        if ($pm_maintenance) {
            $query->whereHas('project', function ($q) use ($pm_maintenance) {
                $q->where('pm_maintenance', $pm_maintenance);
            });
        }

        if ($search) {
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('nama_project', 'like', "%{$search}%");
            });
        }

        $listBA = $query->join('m_project', 'project_top_maintenance.project_id', '=', 'm_project.id_project')
            ->select('project_top_maintenance.*')
            ->selectRaw('(SELECT MIN(periode_end) FROM project_top_maintenance as ptm2 WHERE ptm2.project_id = project_top_maintenance.project_id AND ptm2.status != "paid") as min_periode_end')
            ->orderBy('min_periode_end', 'asc')
            ->orderBy('m_project.nama_project', 'asc')
            ->orderBy('project_top_maintenance.periode_end', 'asc')
            ->get();

        $title = 'Monitoring BA (TOP Maintenance) - ' . ($activeTab == 'progress' ? 'Progress BA' : 'BA Open');

        $pdf = Pdf::loadView('project.monitoring-ba-pdf', compact('listBA', 'title'))
                  ->setPaper('A4', 'landscape');

        return $pdf->download('Monitoring-BA-'.$activeTab.'-'.date('Ymd').'.pdf');
    }

    public function exportBAExcel(Request $request)
    {
        $search = $request->search;
        $pm_maintenance = $request->pm_maintenance;
        $activeTab = $request->tab ?: 'progress';

        $query = \App\ProjectTopMaintenance::with(['project.mPMMaintenance'])
            ->where('status', '!=', 'paid')
            ->whereHas('project');

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        if ($activeTab == 'progress') {
            $query->where('periode_end', '<=', $today);
        } else {
            $query->where('periode_end', '>', $today);
        }

        if ($pm_maintenance) {
            $query->whereHas('project', function ($q) use ($pm_maintenance) {
                $q->where('pm_maintenance', $pm_maintenance);
            });
        }

        if ($search) {
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('nama_project', 'like', "%{$search}%");
            });
        }

        $listBA = $query->join('m_project', 'project_top_maintenance.project_id', '=', 'm_project.id_project')
            ->select('project_top_maintenance.*')
            ->selectRaw('(SELECT MIN(periode_end) FROM project_top_maintenance as ptm2 WHERE ptm2.project_id = project_top_maintenance.project_id AND ptm2.status != "paid") as min_periode_end')
            ->orderBy('min_periode_end', 'asc')
            ->orderBy('m_project.nama_project', 'asc')
            ->orderBy('project_top_maintenance.periode_end', 'asc')
            ->get();

        $filename = 'Monitoring-BA-'.$activeTab.'-'.date('Ymd').'.xlsx';
        return Excel::download(new MonitoringBAExport($listBA), $filename);
    }

    public function monitoringBAUpdate(Request $request, ProjectTopMaintenance $ba)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $oldStatus = $ba->status;
        $newStatus = $request->status;
        $commentText = trim($request->comment ?? '');

        if ($oldStatus !== $newStatus) {
            ProjectTopMaintenanceStatusLog::create([
                'project_top_maintenance_id' => $ba->id,
                'user_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            $ba->status = $newStatus;
        }

        if ($commentText) {
            ProjectTopMaintenanceComment::create([
                'project_top_maintenance_id' => $ba->id,
                'user_id' => Auth::id(),
                'comment' => $commentText,
            ]);
            $ba->keterangan = $commentText;
        } elseif ($oldStatus !== $newStatus) {
            $statusNote = sprintf(
                'TOP %s: Status changed from %s to %s oleh %s',
                $ba->top_ke,
                ucfirst($oldStatus),
                ucfirst($newStatus),
                Auth::user()->name ?? 'Unknown'
            );
            ProjectTopMaintenanceComment::create([
                'project_top_maintenance_id' => $ba->id,
                'user_id' => Auth::id(),
                'comment' => $statusNote,
            ]);
            ProjectComment::create([
                'project_id' => $ba->project_id,
                'user_id' => Auth::id(),
                'comment' => $statusNote,
            ]);
            $ba->keterangan = $statusNote;
        }

        if ($ba->isDirty()) {
            $ba->save();
        }

        flash('Perubahan BA berhasil disimpan!')->success();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'reload' => true]);
        }

        return redirect()->route('project.ba.monitoring', array_merge(request()->query(), ['tab' => $request->tab ?: 'progress']));
    }

    public function monitoringBASignersUpdate(Request $request, ProjectTopMaintenance $ba)
    {
        $request->validate([
            'signers' => 'nullable|array',
            'signers.*' => 'nullable|integer|min:1',
        ]);

        $oldSigners = json_decode($ba->signers ?? '[]', true) ?: [];
        $oldSigners = array_map('intval', $oldSigners);

        $signers = array_values(array_filter($request->input('signers', []), function ($value) {
            return is_numeric($value) && intval($value) > 0;
        }));
        $signers = array_map('intval', $signers);

        $addedSigners = array_values(array_diff($signers, $oldSigners));
        $removedSigners = array_values(array_diff($oldSigners, $signers));

        $ba->signers = !empty($signers) ? json_encode($signers) : null;
        $ba->save();

        if (!empty($addedSigners) || !empty($removedSigners)) {
            $signerLabels = [];
            if (!empty($ba->project->top_maintenance_notes)) {
                $signerLabels = array_map('trim', explode(';', $ba->project->top_maintenance_notes));
            }

            foreach ($addedSigners as $signerIndex) {
                $label = $signerLabels[$signerIndex - 1] ?? 'User ' . $signerIndex;
                ProjectComment::create([
                    'project_id' => $ba->project_id,
                    'user_id' => Auth::id(),
                    'comment' => sprintf('TOP %s: %s dicentang oleh %s', $ba->top_ke, $label, Auth::user()->name ?? 'Unknown'),
                ]);
            }

            foreach ($removedSigners as $signerIndex) {
                $label = $signerLabels[$signerIndex - 1] ?? 'User ' . $signerIndex;
                ProjectComment::create([
                    'project_id' => $ba->project_id,
                    'user_id' => Auth::id(),
                    'comment' => sprintf('TOP %s: %s di-uncheck oleh %s', $ba->top_ke, $label, Auth::user()->name ?? 'Unknown'),
                ]);
            }
        }

        return response()->json(['success' => true, 'reload' => true]);
    }

    public function monitoringBACommentStore(Request $request, ProjectTopMaintenance $ba)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        ProjectTopMaintenanceComment::create([
            'project_top_maintenance_id' => $ba->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        $ba->keterangan = $request->comment;
        $ba->save();

        flash('Komentar BA berhasil disimpan!')->success();
        return back();
    }

    public function projectCommentStore(Request $request, $projectId)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        ProjectComment::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        flash('Komentar Project berhasil disimpan!')->success();
        return back();
    }

    public function getTeamSupports(Request $request)
    {
        $projectIds = $request->project_ids;

        if (empty($projectIds)) {
            // If no projects selected, return all active team supports
            $teamSupports = TeamSupport::where('status', 1)->orderBy('name')->get(['id', 'name']);
        } else {
            // Get team supports linked to the selected projects
            $teamSupports = TeamSupport::where('status', 1)
                ->whereHas('projects', function ($q) use ($projectIds) {
                    $q->whereIn('id_project', $projectIds);
                })
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return response()->json($teamSupports);
    }
}

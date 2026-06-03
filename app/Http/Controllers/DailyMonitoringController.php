<?php

namespace App\Http\Controllers;

use App\DailyMonitoring;
use App\MCase;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DailyMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sub-menu-daily-monitoring');
    }

    public function index()
    {
        $title = 'Daily Monitoring';
        $monitorings = DailyMonitoring::with(['project', 'user'])
            ->orderBy('report_datetime', 'desc')
            ->paginate(10);

        return view('daily_monitoring.index', compact('title', 'monitorings'));
    }

    public function create()
    {
        $title = 'Tambah Daily Monitoring';
        $monitoring = new DailyMonitoring([
            'report_name' => 'Report Daily Monitoring',
            'report_datetime' => now(),
            'health_indicator' => 'normal',
            'status' => 'healthy',
        ]);
        $projects = $this->projectOptions();

        return view('daily_monitoring.create', compact('title', 'monitoring', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMonitoring($request);
        $validated['user_id'] = Auth::id();
        $validated['images'] = $this->storeImages($request);

        DailyMonitoring::create($validated);

        flash('Data daily monitoring berhasil disimpan!')->success();
        return redirect()->route('daily-monitoring.index')->with('daily_monitoring_saved', true);
    }

    public function show(DailyMonitoring $daily_monitoring)
    {
        $title = 'Detail Daily Monitoring';
        $daily_monitoring->load(['project', 'user']);
        $projectCases = $this->casesForProject($daily_monitoring->id_project);

        return view('daily_monitoring.show', compact('title', 'daily_monitoring', 'projectCases'));
    }

    public function image(DailyMonitoring $daily_monitoring, $imageIndex)
    {
        $images = $daily_monitoring->images ?? [];
        abort_unless(isset($images[$imageIndex]), 404);
        abort_unless(Storage::disk('public')->exists($images[$imageIndex]), 404);

        return Storage::disk('public')->response($images[$imageIndex]);
    }

    public function projectCases(Project $project)
    {
        $cases = $this->casesForProject($project->id_project);

        return response()->json([
            'total_warning' => $cases->count(),
            'cases' => $cases,
        ]);
    }

    private function casesForProject($projectId)
    {
        return MCase::where('id_project', $projectId)
            ->orderByRaw('CASE WHEN open_date IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('open_date', 'asc')
            ->orderBy('id_case', 'asc')
            ->get()
            ->map(function ($case) {
                return [
                    'id_case' => $case->id_case,
                    'type' => $case->type,
                    'open_date' => $case->open_date ? date('d-m-Y H:i', strtotime($case->open_date)) : '-',
                    'subject' => $case->subject,
                    'lokasi_perangkat' => $case->affected_sn ?: ($case->location ?: '-'),
                    'closed_date' => $case->closed_date ? date('d-m-Y H:i', strtotime($case->closed_date)) : '-',
                    'severity' => $case->severity,
                    'status' => $case->status,
                    'principal_ticket_no' => $case->principal_ticket_no,
                ];
            });
    }

    public function edit(DailyMonitoring $daily_monitoring)
    {
        $title = 'Edit Daily Monitoring';
        $projects = $this->projectOptions();

        return view('daily_monitoring.edit', compact('title', 'daily_monitoring', 'projects'));
    }

    public function update(Request $request, DailyMonitoring $daily_monitoring)
    {
        $validated = $this->validateMonitoring($request);

        if ($request->hasFile('images')) {
            $this->deleteImages($daily_monitoring->images ?? []);
            $validated['images'] = $this->storeImages($request);
        }

        $daily_monitoring->update($validated);

        flash('Data daily monitoring berhasil diperbarui!')->success();
        return redirect()->route('daily-monitoring.index')->with('daily_monitoring_saved', true);
    }

    private function validateMonitoring(Request $request)
    {
        return $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'site_environment_name' => 'nullable|string|max:255',
            'report_name' => 'required|string|max:255',
            'report_datetime' => 'required|date',
            'total_server' => 'required|integer|min:0',
            'server_on' => 'required|integer|min:0',
            'offline' => 'required|integer|min:0',
            'total_server_off' => 'required|integer|min:0',
            'health_indicator' => 'required|in:normal,warning',
            'server_warning' => 'nullable|integer|min:0',
            'alert_status' => 'nullable|string',
            'status' => 'required|in:healthy,not_healthy,warning,ok',
            'farm' => 'nullable|integer|min:0',
            'total_machine_farm' => 'nullable|integer|min:0',
            'status_machine' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);
    }

    private function storeImages(Request $request)
    {
        if (!$request->hasFile('images')) {
            return [];
        }

        $paths = [];
        foreach ($request->file('images') as $image) {
            $paths[] = $image->store('daily_monitoring/images', 'public');
        }

        return $paths;
    }

    private function deleteImages(array $images)
    {
        foreach ($images as $image) {
            Storage::disk('public')->delete($image);
        }
    }

    private function projectOptions()
    {
        $userProjectIds = Auth::user()->projects->pluck('id_project')->toArray();

        if (count($userProjectIds) > 0) {
            return Project::whereIn('id_project', $userProjectIds)
                ->orderBy('nama_project')
                ->pluck('nama_project', 'id_project');
        }

        return Project::orderBy('nama_project')->pluck('nama_project', 'id_project');
    }
}

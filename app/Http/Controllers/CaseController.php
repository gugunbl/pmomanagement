<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MCase;
use App\Project;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-case');
        $this->middleware('permission:create-case', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-case', ['only' => ['edit', 'update']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $title = 'Daftar Case';

        $userProjects = $user->projects->pluck('id_project')->toArray();
        $query = MCase::with(['project', 'user']);

        // Base project access filter
        if (count($userProjects) > 0) {
            $query->whereIn('id_project', $userProjects);
            $projects = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
        } else {
            $projects = Project::pluck('nama_project', 'id_project');
        }

        // Project filter from request
        if ($request->filled('id_project')) {
            $query->where('id_project', $request->id_project);
        }

        // Custom sorting as requested
        $cases = $query->orderByRaw("
            CASE WHEN status IN ('Open', 'In Progress') THEN 1 ELSE 2 END ASC,
            CASE WHEN status IN ('Open', 'In Progress') THEN open_date ELSE '1970-01-01' END DESC,
            CASE WHEN status = 'Closed' THEN closed_date ELSE '1970-01-01' END DESC
        ")->get();

        return view('cases.index', compact('title', 'cases', 'projects'));
    }

    public function create()
    {
        $user = Auth::user();
        $title = 'Tambah Case Baru';

        $userProjects = $user->projects->pluck('id_project')->toArray();

        // Check if user has specific assigned projects
        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->where('status_maintenance', 1)->pluck('nama_project', 'id_project');
            $fixedProject = count($userProjects) === 1;
        } else {
            // Admin or user with all project access
            $projects = Project::where('status_maintenance', 1)->pluck('nama_project', 'id_project');
            $fixedProject = false;
        }

        return view('cases.create', compact('title', 'projects', 'fixedProject'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'open_date' => 'required|date',
            'closed_date' => 'nullable|date',
            'subject' => 'required|string|max:255',
            'detail' => 'required|string',
            'severity' => 'required|in:Minor,Major,Critical',
            'affected_sn' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'principal_ticket_no' => 'nullable|string|max:255',
            'resolution_detail' => 'nullable|string',
            'status' => 'required|in:Open,In Progress,Closed',
        ]);

        $validated['user_id'] = Auth::id();

        MCase::create($validated);

        flash('Case berhasil ditambahkan!')->success();
        
        return redirect()->route('cases.index');
    }

    public function getSerialNumbers($id_project)
    {
        $devices = \App\Perangkat::with('jenisPerangkat')
            ->where('id_project', $id_project)
            ->whereNotNull('serialnumber')
            ->get();

        $sns = [];
        foreach ($devices as $device) {
            $jenis = optional($device->jenisPerangkat)->jns_perangkat ?? '-';
            $merk = $device->merk ?? '-';
            $sn = $device->serialnumber;
            $sns[$sn] = "{$jenis} - {$merk} - {$sn}";
        }
            
        return response()->json($sns);
    }

    public function show($case)
    {
        $case = MCase::with(['project', 'user'])->findOrFail($case);
        $title = 'Detail Case';
        return view('cases.show', compact('title', 'case'));
    }

    public function edit($case)
    {
        $case = MCase::findOrFail($case);
        $title = 'Edit Case';
        $user = Auth::user();
        
        $userProjects = $user->projects->pluck('id_project')->toArray();
        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
        } else {
            $projects = Project::pluck('nama_project', 'id_project');
        }

        return view('cases.edit', compact('title', 'case', 'projects'));
    }

    public function update(Request $request, $case)
    {
        $caseModel = MCase::findOrFail($case);

        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'open_date' => 'required|date',
            'closed_date' => 'nullable|date',
            'subject' => 'required|string|max:255',
            'detail' => 'required|string',
            'severity' => 'required|in:Minor,Major,Critical',
            'affected_sn' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'principal_ticket_no' => 'nullable|string|max:255',
            'resolution_detail' => 'nullable|string',
            'status' => 'required|in:Open,In Progress,Closed',
        ]);

        $caseModel->update($validated);

        flash('Case berhasil diupdate!')->success();
        return redirect()->route('cases.index');
    }
}

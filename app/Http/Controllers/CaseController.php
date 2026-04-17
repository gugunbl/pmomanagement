<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MCase;
use App\Project;
use App\Perangkat;
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
        $query = MCase::with(['project', 'user', 'perangkat']);

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

        // Search by subject or location
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%");
            });
        }

        // Custom sorting as requested
        $cases = $query->orderByRaw("
            CASE WHEN status IN ('Open', 'In Progress') THEN 1 ELSE 2 END ASC,
            CASE WHEN status IN ('Open', 'In Progress') THEN open_date ELSE '1970-01-01' END DESC,
            CASE WHEN status = 'Closed' THEN closed_date ELSE '1970-01-01' END DESC
        ")->paginate(setting('record_per_page', 15))->withQueryString();

        $lokasis = \App\Lokasi::pluck('nama_lokasi', 'nama_lokasi'); // Using name as value for the text field in MCase

        return view('cases.index', compact('title', 'cases', 'projects', 'lokasis'));
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

        $lokasis = \App\Lokasi::all();

        return view('cases.create', compact('title', 'projects', 'fixedProject', 'lokasis'));
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
            $lantai = $device->lantai ?? '-';
            $sns[$sn] = "{$jenis} - {$merk} - {$sn} ({$lantai})";
        }
            
        return response()->json($sns);
    }

    public function show($case)
    {
        $case = MCase::with(['project', 'user'])->findOrFail($case);
        $title = 'Detail Case';

        // Fetch affected devices based on SN
        $affectedPerangkats = [];
        if ($case->affected_sn) {
            // Split by common delimiters (comma, semicolon, space, newline)
            $sns = preg_split('/[,\s;]+/', $case->affected_sn, -1, PREG_SPLIT_NO_EMPTY);
            $affectedPerangkats = Perangkat::whereIn('serialnumber', $sns)->get();
        }

        return view('cases.show', compact('title', 'case', 'affectedPerangkats'));
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

        $lokasis = \App\Lokasi::all();

        // Fetch affected devices based on current SN for the replacement button
        $affectedPerangkats = [];
        if ($case->affected_sn) {
            $sns = preg_split('/[,\s;]+/', $case->affected_sn, -1, PREG_SPLIT_NO_EMPTY);
            $affectedPerangkats = Perangkat::whereIn('serialnumber', $sns)->get();
        }

        return view('cases.edit', compact('title', 'case', 'projects', 'lokasis', 'affectedPerangkats'));
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

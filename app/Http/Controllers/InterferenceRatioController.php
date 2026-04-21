<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\InterferenceRatio;
use App\Project;
use Illuminate\Support\Facades\Auth;

class InterferenceRatioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu-interferance-view', ['only' => ['index', 'show']]);
        $this->middleware('permission:menu-interferance-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:menu-interferance-edit', ['only' => ['edit', 'update']]);
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $title = 'List Interference Ratio';

        $userProjects = $user->projects->pluck('id_project')->toArray();
        $query = InterferenceRatio::with(['project', 'user']);

        // Check if user has specific assigned projects
        if (count($userProjects) > 0) {
            $query->whereIn('id_project', $userProjects);
        }

        // Apply filters if needed
        if ($request->filled('id_project')) {
            $query->where('id_project', $request->id_project);
        }

        $ratios = $query->orderBy('tanggal', 'desc')->paginate(setting('record_per_page', 15))->withQueryString();

        return view('interference_ratio.index', compact('title', 'ratios'));
    }

    public function create()
    {
        $user = Auth::user();
        $title = 'Tambah Interference Ratio';

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

        return view('interference_ratio.create', compact('title', 'projects', 'fixedProject'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'tanggal' => 'required|date',
            'ap_name' => 'required|string|max:255',
            'channel_before' => 'required|integer',
            'channel_after' => 'required|integer',
            'utilisasi_before' => 'required|integer',
            'utilisasi_after' => 'required|integer',
            'waktu' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        InterferenceRatio::create($validated);

        flash('Interference Ratio berhasil ditambahkan!')->success();
        
        return redirect()->route('interference-ratio.index');
    }

    public function show($id)
    {
        $ratio = InterferenceRatio::with(['project', 'user'])->findOrFail($id);
        $title = 'Detail Interference Ratio';

        return view('interference_ratio.show', compact('title', 'ratio'));
    }

    public function edit($id)
    {
        $ratio = InterferenceRatio::findOrFail($id);
        $title = 'Edit Interference Ratio';
        
        $user = Auth::user();
        $userProjects = $user->projects->pluck('id_project')->toArray();

        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
        } else {
            $projects = Project::pluck('nama_project', 'id_project');
        }

        return view('interference_ratio.edit', compact('title', 'ratio', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $ratio = InterferenceRatio::findOrFail($id);

        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'tanggal' => 'required|date',
            'ap_name' => 'required|string|max:255',
            'channel_before' => 'required|integer',
            'channel_after' => 'required|integer',
            'utilisasi_before' => 'required|integer',
            'utilisasi_after' => 'required|integer',
            'waktu' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $ratio->update($validated);

        flash('Interference Ratio berhasil diupdate!')->success();
        
        return redirect()->route('interference-ratio.index');
    }
}

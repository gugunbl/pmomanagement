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

        $ratios = $query->orderBy('tanggal', 'desc')->orderBy('waktu', 'desc')->paginate(setting('record_per_page', 15))->withQueryString();

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
            'waktu' => 'required|string',
            'keterangan' => 'nullable|string',
            'entries' => 'required|array|min:1',
            'entries.*.ap_name' => 'required|string|max:255',
            'entries.*.channel_before' => 'required|integer',
            'entries.*.channel_after' => 'required|integer',
            'entries.*.utilisasi_before' => 'required|integer',
            'entries.*.utilisasi_after' => 'required|integer',
        ]);

        foreach ($request->entries as $entry) {
            InterferenceRatio::create([
                'id_project' => $request->id_project,
                'tanggal' => $request->tanggal,
                'waktu' => $request->waktu,
                'keterangan' => $request->keterangan,
                'user_id' => Auth::id(),
                'ap_name' => $entry['ap_name'],
                'channel_before' => $entry['channel_before'],
                'channel_after' => $entry['channel_after'],
                'utilisasi_before' => $entry['utilisasi_before'],
                'utilisasi_after' => $entry['utilisasi_after'],
            ]);
        }

        flash('Interference Ratio berhasil ditambahkan!')->success();
        
        return redirect()->route('interference-ratio.index');
    }

    public function show($id)
    {
        $ratio = InterferenceRatio::with(['project', 'user'])->findOrFail($id);
        
        $ratios = InterferenceRatio::with(['user'])->where('id_project', $ratio->id_project)
            ->where('tanggal', $ratio->tanggal)
            ->where('waktu', $ratio->waktu)
            ->get();

        $title = 'Detail Interference Ratio';

        return view('interference_ratio.show', compact('title', 'ratio', 'ratios'));
    }

    public function edit($id)
    {
        $ratio = InterferenceRatio::findOrFail($id);
        
        $ratios = InterferenceRatio::where('id_project', $ratio->id_project)
            ->where('tanggal', $ratio->tanggal)
            ->where('waktu', $ratio->waktu)
            ->get();

        $title = 'Edit Interference Ratio';
        
        $user = Auth::user();
        $userProjects = $user->projects->pluck('id_project')->toArray();

        if (count($userProjects) > 0) {
            $projects = Project::whereIn('id_project', $userProjects)->pluck('nama_project', 'id_project');
        } else {
            $projects = Project::pluck('nama_project', 'id_project');
        }

        return view('interference_ratio.edit', compact('title', 'ratio', 'ratios', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $ratio = InterferenceRatio::findOrFail($id);

        $validated = $request->validate([
            'id_project' => 'required|exists:m_project,id_project',
            'tanggal' => 'required|date',
            'waktu' => 'required|string',
            'keterangan' => 'nullable|string',
            'ap_data' => 'required|array',
            'ap_data.*.id' => 'required|exists:interference_ratios,id',
            'ap_data.*.ap_name' => 'required|string|max:255',
            'ap_data.*.channel_before' => 'required|integer',
            'ap_data.*.channel_after' => 'required|integer',
            'ap_data.*.utilisasi_before' => 'required|integer',
            'ap_data.*.utilisasi_after' => 'required|integer',
        ]);

        foreach ($request->ap_data as $data) {
            $r = InterferenceRatio::findOrFail($data['id']);
            $r->update([
                'id_project' => $request->id_project,
                'tanggal' => $request->tanggal,
                'waktu' => $request->waktu,
                'keterangan' => $request->keterangan,
                'ap_name' => $data['ap_name'],
                'channel_before' => $data['channel_before'],
                'channel_after' => $data['channel_after'],
                'utilisasi_before' => $data['utilisasi_before'],
                'utilisasi_after' => $data['utilisasi_after'],
            ]);
        }

        flash('Interference Ratio berhasil diupdate!')->success();
        
        return redirect()->route('interference-ratio.index');
    }
}

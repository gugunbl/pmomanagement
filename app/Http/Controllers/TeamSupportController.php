<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TeamSupport;

class TeamSupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu-team-support');
    }

    public function index(Request $request)
    {
        $search = $request->search;

        $teams = TeamSupport::with('projects')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        $title = 'Master Data Team Support';
        return view('team_support.index', compact('teams', 'title', 'search'));
    }

    public function create()
    {
        $title = 'Tambah Team Support';
        return view('team_support.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:100|unique:m_team_supports,name',
            'status' => 'required|boolean',
        ]);

        TeamSupport::create($request->only('name', 'status'));

        flash('Team Support berhasil ditambahkan.')->success();
        return redirect()->route('team-support.index');
    }

    public function edit(TeamSupport $teamSupport)
    {
        $teamSupport->load('projects');
        $title = 'Edit Team Support';
        return view('team_support.edit', compact('title', 'teamSupport'));
    }

    public function update(Request $request, TeamSupport $teamSupport)
    {
        $request->validate([
            'name'   => 'required|string|max:100|unique:m_team_supports,name,' . $teamSupport->id,
            'status' => 'required|boolean',
        ]);

        $teamSupport->update($request->only('name', 'status'));

        flash('Team Support berhasil diperbarui.')->success();
        return redirect()->route('team-support.index');
    }

    public function destroy(TeamSupport $teamSupport)
    {
        // Detach from all projects first
        $teamSupport->projects()->detach();
        $teamSupport->delete();

        flash('Team Support berhasil dihapus.')->success();
        return redirect()->route('team-support.index');
    }
}

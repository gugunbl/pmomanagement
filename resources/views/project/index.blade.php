@extends('layouts.app')

@section('body_class', 'project-workspace-body')

@section('content')
<div class="maintenance-workspace project-list-workspace">
    <section class="maintenance-overview" aria-labelledby="project-list-title">
        <div class="maintenance-overview-copy">
            <span class="project-kicker">Project portfolio</span>
            <h2 id="project-list-title">Semua Project</h2>
            <p>Cari, pantau status, dan buka detail seluruh project dari satu workspace.</p>
        </div>
        @can('create-project')
            <a href="{{ route('project.create') }}" class="btn project-create-btn"><i class="fas fa-plus"></i> Project Baru</a>
        @endcan
    </section>

<section class="card app-panel mb-5">
    <div class="app-panel-header">
        <div>
            <h2>Daftar Project</h2>
            <p>Gunakan pencarian dan filter untuk menemukan project yang dibutuhkan.</p>
        </div>
        <span class="badge badge-light">{{ $projects->total() }} data</span>
    </div>

    <form action="{{ route('project.index') }}" method="GET" class="app-filter-bar">
        <div class="app-filter-field">
            <label for="project_search">Cari Project</label>
            <input id="project_search" name="search" type="search" class="form-control" value="{{ request('search') }}" placeholder="Nama project, customer, atau tahun">
        </div>
        <div class="app-filter-field">
            <label for="status_maintenance">Tipe Project</label>
            <select id="status_maintenance" name="status_maintenance" class="form-control">
                <option value="">Semua tipe</option>
                @foreach($tipepm as $value => $label)
                    <option value="{{ $value }}" @selected((string) request('status_maintenance') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="app-filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Cari</button>
            @if(request()->hasAny(['search', 'status_maintenance']))
                <a href="{{ route('project.index') }}" class="btn btn-secondary text-dark" title="Reset filter"><i class="fas fa-undo"></i></a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table responsive-data-table project-data-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Tahun</th>
                    <th>Nomor SPK</th>
                    <th>Status</th>
                    <th>Project Manager</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td data-label="Project" class="app-primary-cell">
                            <span class="app-record-title">{{ $project->nama_project }}</span>
                            <span class="app-record-subtitle">{{ $project->customer ?: 'Customer belum diisi' }}</span>
                        </td>
                        <td data-label="Tahun">{{ $project->tahun ?: '-' }}</td>
                        <td data-label="Nomor SPK">{{ $project->nomor_spk ?: '-' }}</td>
                        <td data-label="Status">
                            @if($project->status_implementation == 1)
                                <span class="badge badge-primary">Implementation</span>
                                <span class="app-record-subtitle">{{ $project->sta_i ?? 'N/A' }}</span>
                            @elseif($project->status_implementation == 2)
                                <span class="badge badge-success">Maintenance</span>
                                <span class="app-record-subtitle">{{ $project->sta_m ?? 'N/A' }}</span>
                            @else
                                <span class="badge badge-secondary">Other</span>
                                <span class="app-record-subtitle">{{ $project->sta_i ?? $project->sta_m ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td data-label="Project Manager">
                            @if($project->status_implementation == 1)
                                <span class="app-record-title">{{ $project->pm_implementasi ?? 'N/A' }}</span>
                                <span class="app-record-subtitle">Implementation PM</span>
                            @elseif($project->status_implementation == 2 && $project->status_maintenance == 2)
                                <span class="app-record-title">{{ $project->pm_implementasi ?? 'N/A' }}</span>
                                <span class="app-record-subtitle">Implementasi · {{ $project->pm_maintenance ?? 'N/A' }} (Maintenance)</span>
                            @elseif($project->status_implementation == 2)
                                <span class="app-record-title">{{ $project->pm_maintenance ?? 'N/A' }}</span>
                                <span class="app-record-subtitle">Maintenance PM</span>
                            @else
                                {{ $project->pm_implementasi ?? $project->pm_maintenance ?? 'N/A' }}
                            @endif
                        </td>
                        <td data-label="Aksi" class="text-center app-actions-cell">
                            <div class="app-action-group">
                                @can('view-project')
                                    <a class="btn btn-outline-primary" title="Lihat detail" href="{{ route('project.show', $project->id_project) }}"><i class="fas fa-eye"></i></a>
                                @endcan
                                @if(Auth::check() && Auth::user()->canEditProject($project))
                                    <a class="btn btn-outline-info" title="Edit project" href="{{ route('project.edit', $project->id_project) }}"><i class="fas fa-edit"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Project tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="app-pagination">
        <span class="app-pagination-summary">
            Menampilkan {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} dari {{ $projects->total() }} project
        </span>
        {{ $projects->withQueryString()->links() }}
    </div>
</section>
</div>
@endsection

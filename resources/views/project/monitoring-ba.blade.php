@extends('layouts.app')

@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            <div class="card">
                <!-- Nav tabs -->
                <div class="card-header border-0 pb-0">
                    <ul class="nav nav-tabs border-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'progress' ? 'active font-weight-bold text-primary border-bottom' : '' }}" 
                               href="{{ route('project.ba.monitoring', array_merge(request()->query(), ['tab' => 'progress'])) }}">
                               Progress BA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'open' ? 'active font-weight-bold text-primary border-bottom' : '' }}" 
                               href="{{ route('project.ba.monitoring', array_merge(request()->query(), ['tab' => 'open'])) }}">
                               BA Open
                            </a>
                        </li>
                        <li class="nav-item ml-auto align-self-center pr-3">
                            <div class="recap-summary d-flex align-items-center">
                                <div class="recap-item mr-4">
                                    <span class="recap-label text-muted">Banyak Project</span>
                                    <span class="recap-value text-primary font-weight-bold ml-1">{{ $totalProjects }}</span>
                                </div>
                                <div class="recap-item">
                                    <span class="recap-label text-muted">Banyak BA</span>
                                    <span class="recap-value text-info font-weight-bold ml-1">{{ $totalBA }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="card-header border-0 mt--2">
                    <form action="{{ route('project.ba.monitoring') }}" method="GET">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">PM Maintenance</label>
                                    <select name="pm_maintenance" class="form-control form-control-sm">
                                        <option value="">Semua PM</option>
                                        @foreach($listPM as $pm)
                                            <option value="{{ $pm->id_pm }}" {{ $pm_maintenance == $pm->id_pm ? 'selected' : '' }}>
                                                {{ $pm->nama_pm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Search Project</label>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama Project..." value="{{ $search }}">
                                </div>
                            </div>
                            <div class="col-md-6 mt-4">
                                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                <a href="{{ route('project.ba.export.pdf', array_merge(request()->query(), ['tab' => $activeTab])) }}" class="btn btn-danger btn-sm">Export PDF</a>
                                <a href="{{ route('project.ba.export.excel', array_merge(request()->query(), ['tab' => $activeTab])) }}" class="btn btn-success btn-sm">Export Excel</a>
                                <a href="{{ route('project.ba.monitoring', ['tab' => $activeTab]) }}" class="btn btn-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Project</th>
                                <th scope="col">Nomor SPK</th>
                                <th scope="col">PM Maintenance</th>
                                <th scope="col">TOP Ke</th>
                                <th scope="col">Start Date BA</th>
                                <th scope="col">End Date BA</th>
                                <th scope="col">Status</th>
                                <th scope="col">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @php 
                                $projectCounts = $listBA->getCollection()->groupBy('project_id')->map->count();
                                $projectCounter = $startProjectCounter ?? 0;
                                $prevProject = null;
                                $isSilver = false;
                            @endphp
                            @forelse($listBA as $ba)
                                @php
                                    $isFirst = false;
                                    // Toggle silver background when project changes
                                    if ($ba->project_id !== $prevProject) {
                                        $isSilver = !$isSilver;
                                        $prevProject = $ba->project_id;
                                        $isFirst = true;
                                        $projectCounter++;
                                    }

                                    $highlight = '';
                                    if ($activeTab == 'progress' && $ba->periode_end) {
                                        $endDate = \Carbon\Carbon::parse($ba->periode_end);
                                        $threeMonthsAgo = \Carbon\Carbon::now()->subMonths(3);
                                        $today = \Carbon\Carbon::now();

                                        if ($endDate->lt($threeMonthsAgo)) {
                                            $highlight = 'bg-danger-dark'; 
                                        } elseif ($endDate->lte($today)) {
                                            $highlight = 'bg-warning-custom';
                                        }
                                    }
                                    
                                    $rowspan = $projectCounts[$ba->project_id] ?? 1;
                                @endphp
                                <tr class="{{ $isSilver ? 'bg-silver' : '' }}">
                                    @if($isFirst)
                                        <td rowspan="{{ $rowspan }}">{{ $projectCounter }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-wrap font-weight-bold" style="min-width: 200px; font-size: 1.05rem;">
                                            <a href="{{ route('project.edit', $ba->project_id) }}">
                                                {{ $ba->project->nama_project ?? '-' }}
                                            </a>
                                        </td>
                                        <td rowspan="{{ $rowspan }}">{{ $ba->project->nomor_spk ?? '-' }}</td>
                                        <td rowspan="{{ $rowspan }}" class="font-weight-bold" style="font-size: 1.05rem;">{{ $ba->project->mPMMaintenance->nama_pm ?? '-' }}</td>
                                    @endif
                                    <td class="text-center">{{ $ba->top_ke }}</td>
                                    <td class="{{ $highlight }}">{{ $ba->periode_start ? \Carbon\Carbon::parse($ba->periode_start)->format('d M Y') : '-' }}</td>
                                    <td class="{{ $highlight }}">{{ $ba->periode_end ? \Carbon\Carbon::parse($ba->periode_end)->format('d M Y') : '-' }}</td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            <i class="{{ $ba->status == 'paid' ? 'bg-success' : ($ba->status == 'submitted' ? 'bg-info' : 'bg-warning') }}"></i>
                                            <span class="status">{{ strtoupper($ba->status) }}</span>
                                        </span>
                                    </td>
                                    <td style="min-width: 250px;">
                                        <div style="max-height: 4.5em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.5em; white-space: normal;">
                                            {{ $ba->keterangan ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data BA di tab ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-4">
                    {{ $listBA->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        white-space: normal !important;
        vertical-align: top;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #8898aa;
    }
    
    .nav-tabs .nav-link.active {
        background-color: transparent;
        color: #5e72e4;
        border-bottom: 3px solid #5e72e4 !important;
    }

    /* Merah Gelap untuk > 3 bulan overdue */
    .bg-danger-dark {
        background-color: #8B0000 !important;
        color: white !important;
    }
    
    /* Kuning untuk <= 3 bulan overdue */
    .bg-warning-custom {
        background-color: #FFFF00 !important;
        color: black !important;
    }

    .bg-silver {
        background-color: #f4f4f4 !important; /* Silver/Light Gray */
    }

    .recap-label {
        font-size: 0.9rem; /* Same as progress BA / standard nav-link */
        text-transform: uppercase;
        letter-spacing: .025em;
    }

    .recap-value {
        font-size: 1.8rem; /* 2x the label (approx) */
        line-height: 1;
        vertical-align: middle;
    }
</style>
@endpush

@extends('layouts.app')

@section('content')
<div class="row cases-page">
    <div class="col">
        <div class="card">
            <div class="card-header border-0 cases-header">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-12 mb-3 mb-lg-0">
                        <h3 class="mb-0 cases-title">{{ $title }}</h3>
                    </div>
                    <div class="col-lg-7 col-md-12 mb-3 mb-lg-0">
                        <div class="case-stats">
                            <span class="case-stat">Case Open <strong class="text-danger">{{ $openCasesCount ?? 0 }}</strong></span>
                            <span class="case-stat">Hari Ini <strong class="text-primary">{{ $todayCasesCount ?? 0 }}</strong></span>
                            <span class="case-stat">Kemarin <strong class="text-warning">{{ $yesterdayCasesCount ?? 0 }}</strong></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-12 text-lg-right">
                        <a href="{{ route('cases.create') }}" class="btn btn-sm btn-primary cases-add-btn">Tambah Case</a>
                    </div>
                </div>
            </div>

            <div class="card-body border-top">
                <form action="{{ route('cases.index') }}" method="GET" class="case-filter mb-4">
                    @php
                        $selectedProjects = (array) request('id_project', []);
                    @endphp
                    <div class="row align-items-end">
                        <div class="col-xl-4 col-lg-6 mb-3">
                            <div class="form-group mb-0">
                                <label for="id_project" class="form-control-label">Filter Project</label>
                                <select name="id_project[]" id="id_project" class="form-control select2" multiple data-placeholder="-- Semua Project --">
                                    @foreach($projects as $id => $nama)
                                    <option value="{{ $id }}" {{ in_array($id, $selectedProjects) ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 mb-3">
                            <div class="form-group mb-0">
                                <label for="type" class="form-control-label">Filter Type</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">-- Semua Type --</option>
                                    <option value="Problem/Incident" {{ request('type') == 'Problem/Incident' ? 'selected' : '' }}>Problem/Incident</option>
                                    <option value="Request/Kegiatan" {{ request('type') == 'Request/Kegiatan' ? 'selected' : '' }}>Request/Kegiatan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 mb-3">
                            <div class="form-group mb-0">
                                <label for="search" class="form-control-label">Search Subject / Lokasi</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-6 mb-3">
                            <div class="case-filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                            <a href="{{ route('cases.index') }}" class="btn btn-secondary text-dark">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Type</th>
                                <th>Tgl Open Case</th>
                                <th>Subject Case</th>
                                <th>Lokasi Perangkat</th>
                                <th>Tgl & Jam Closed</th>
                                <th>Team Support</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($cases as $key => $row)
                                @php
                                    $isGray = in_array($row->status, ['Open', 'In Progress']);
                                    $rowClass = $isGray ? 'bg-light text-dark' : '';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ ($cases->currentPage() - 1) * $cases->perPage() + $key + 1 }}</td>
                                    <td>
                                        @if($row->type == 'Problem/Incident')
                                            <span class="badge badge-warning">Problem</span>
                                        @elseif($row->type == 'Request/Kegiatan')
                                            <span class="badge badge-info">Request</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $row->open_date ? date('d-m-Y H:i', strtotime($row->open_date)) : '-' }}</td>
                                    <td title="{{ $row->subject }}">{{ Str::limit($row->subject, 20) }}</td>
                                    <td>
                                        {{ $row->location ?? '-' }}
                                        @if($row->perangkat && $row->perangkat->lantai)
                                            - {{ $row->perangkat->lantai }}
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $row->closed_date ? date('d-m-Y H:i', strtotime($row->closed_date)) : '-' }}</td>
                                    <td>
                                        @if($row->project && $row->project->teamSupports && $row->project->teamSupports->count() > 0)
                                            @php $teams = $row->project->teamSupports->take(2); @endphp
                                            @foreach($teams as $idx => $team)
                                                @if($row->project->teamSupports->count() > 1)
                                                    {{ Str::limit($team->name, 10) }} {{ $idx + 1 }}{{ !$loop->last ? ',' : '' }}
                                                @else
                                                    {{ Str::limit($team->name, 10) }}
                                                @endif
                                            @endforeach
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            @if($row->severity == 'Minor')
                                                <i class="bg-success"></i>
                                            @elseif($row->severity == 'Major')
                                                <i class="bg-warning"></i>
                                            @elseif($row->severity == 'Critical')
                                                <i class="bg-danger"></i>
                                            @else
                                                <i class="bg-info"></i>
                                            @endif
                                            <span class="status">{{ $row->severity }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            @if($row->status == 'Open')
                                                <i class="bg-danger"></i>
                                            @elseif($row->status == 'In Progress')
                                                <i class="bg-warning"></i>
                                            @elseif($row->status == 'Closed')
                                                <i class="bg-success"></i>
                                            @endif
                                            <span class="status">{{ $row->status }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('cases.show', $row->id_case) }}" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                        <a href="{{ route('cases.edit', $row->id_case) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data case.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer py-4">
                    {{ $cases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .cases-page .card {
        border: 0;
        box-shadow: 0 12px 30px rgba(31, 45, 61, .08);
    }

    .cases-header {
        padding: 1.35rem 1.5rem;
    }

    .cases-title {
        color: #172b4d;
        font-size: 1rem;
        font-weight: 700;
    }

    .case-stats {
        display: flex;
        justify-content: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .case-stat {
        display: inline-flex;
        align-items: baseline;
        gap: .35rem;
        color: #8898aa;
        font-size: .8rem;
        white-space: nowrap;
    }

    .case-stat strong {
        font-size: 1rem;
    }

    .cases-add-btn {
        min-width: 104px;
    }

    .case-filter {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: 1.25rem 1.25rem .5rem;
    }

    .case-filter .form-control-label {
        color: #172b4d;
        font-size: .78rem;
        font-weight: 600;
        margin-bottom: .45rem;
    }

    .case-filter .form-control,
    .case-filter .select2-container--bootstrap4 .select2-selection {
        border-color: #d9e2ec;
        min-height: 44px;
    }

    .case-filter .select2-container--bootstrap4 .select2-selection--multiple {
        max-height: 88px;
        overflow-y: auto;
        padding: .25rem .4rem;
    }

    .case-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        max-width: 100%;
        margin: .12rem .2rem .12rem 0;
        border: 1px solid #d9e2ec;
        border-radius: .25rem;
        background: #f8fafc;
        color: #334e68;
        font-size: .76rem;
        line-height: 1.4;
        padding: .18rem .45rem .18rem 1.15rem;
        white-space: normal;
    }

    .case-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-search--inline {
        display: inline-block;
        width: auto;
    }

    .case-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
        min-width: 180px;
        width: auto !important;
        margin-top: .18rem;
    }

    .case-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #829ab1;
        left: .35rem;
        margin-right: .25rem;
    }

    .case-filter-actions {
        display: flex;
        gap: .5rem;
        align-items: center;
    }

    .case-filter-actions .btn {
        min-height: 44px;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    @media (max-width: 991.98px) {
        .case-stats {
            justify-content: flex-start;
        }

        .case-filter-actions {
            justify-content: flex-start;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            closeOnSelect: false
        });
    });
</script>
@endpush

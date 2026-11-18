@extends('layouts.app')

@section('hide_page_header', '1')

@section('content')
<div class="ops-page">
    <section class="ops-hero">
        <div class="ops-hero-row">
            <div>
                <div class="ops-eyebrow">Service Operations</div>
                <h1 class="ops-title">Case Management</h1>
                <p class="ops-subtitle">Pantau incident, request, dan aktivitas relokasi dalam satu ruang kerja operasional.</p>
            </div>
            @can('create-case')
                <a href="{{ route('cases.create') }}" class="ops-primary-action"><i class="fas fa-plus"></i><span>Tambah Case</span></a>
            @endcan
        </div>
        <div class="ops-stats">
            <div class="ops-stat"><span class="ops-stat-icon"><i class="fas fa-exclamation-circle"></i></span><span><span class="ops-stat-label">Case aktif</span><strong class="ops-stat-value">{{ $openCasesCount ?? 0 }}</strong></span></div>
            <div class="ops-stat"><span class="ops-stat-icon"><i class="fas fa-calendar-day"></i></span><span><span class="ops-stat-label">Dibuat hari ini</span><strong class="ops-stat-value">{{ $todayCasesCount ?? 0 }}</strong></span></div>
            <div class="ops-stat"><span class="ops-stat-icon"><i class="fas fa-history"></i></span><span><span class="ops-stat-label">Dibuat kemarin</span><strong class="ops-stat-value">{{ $yesterdayCasesCount ?? 0 }}</strong></span></div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div><h2 class="ops-card-title"><i class="fas fa-filter mr-2 text-primary"></i>Temukan case</h2><p class="ops-card-description">Gunakan satu atau beberapa filter untuk mempersempit daftar.</p></div>
        </div>
        <div class="ops-card-body">
            <form action="{{ route('cases.index') }}" method="GET" class="ops-filter-grid">
                @php $selectedProjects = (array) request('id_project', []); @endphp
                <div class="ops-field">
                    <label for="id_project">Project</label>
                    <select name="id_project[]" id="id_project" class="form-control select2" multiple data-placeholder="Semua project">
                        @foreach($projects as $id => $nama)
                            <option value="{{ $id }}" {{ in_array($id, $selectedProjects) ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ops-field">
                    <label for="type">Jenis case</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">Semua jenis</option>
                        <option value="Problem/Incident" {{ request('type') === 'Problem/Incident' ? 'selected' : '' }}>Problem / Incident</option>
                        <option value="Request/Kegiatan" {{ request('type') === 'Request/Kegiatan' ? 'selected' : '' }}>Request / Kegiatan</option>
                        <option value="Relokasi/Reposisi" {{ request('type') === 'Relokasi/Reposisi' ? 'selected' : '' }}>Relokasi / Reposisi</option>
                    </select>
                </div>
                <div class="ops-field">
                    <label for="search">Kata kunci</label>
                    <input type="search" name="search" id="search" class="form-control" placeholder="Cari subject atau lokasi…" value="{{ request('search') }}">
                </div>
                <div class="ops-filter-actions">
                    <button type="submit" class="ops-button ops-button-primary"><i class="fas fa-search"></i>Terapkan</button>
                    <a href="{{ route('cases.index') }}" class="ops-button ops-button-muted"><i class="fas fa-undo-alt"></i>Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div><h2 class="ops-card-title">Daftar case</h2><p class="ops-card-description">Case aktif ditampilkan lebih dulu berdasarkan waktu terbaru.</p></div>
            <span class="ops-pill ops-pill-primary">{{ $cases->total() }} data</span>
        </div>
        <div class="ops-table-wrap">
            <table class="ops-table">
                <thead><tr><th>No</th><th>Case</th><th>Dibuka</th><th>Lokasi / perangkat</th><th>Team support</th><th>Severity</th><th>Status</th><th>Ditutup</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($cases as $key => $row)
                        <tr>
                            <td>{{ ($cases->currentPage() - 1) * $cases->perPage() + $key + 1 }}</td>
                            <td>
                                <span class="ops-main-cell" title="{{ $row->subject }}">{{ Str::limit($row->subject, 48) }}</span>
                                <span class="ops-sub-cell">
                                    @if($row->type === 'Problem/Incident')
                                        <span class="ops-pill ops-pill-warning">Problem / Incident</span>
                                    @elseif($row->type === 'Request/Kegiatan')
                                        <span class="ops-pill ops-pill-info">Request / Kegiatan</span>
                                    @else
                                        <span class="ops-pill ops-pill-primary">Relokasi / Reposisi</span>
                                    @endif
                                </span>
                            </td>
                            <td class="text-nowrap"><span class="ops-main-cell">{{ $row->open_date ? date('d M Y', strtotime($row->open_date)) : '-' }}</span><span class="ops-sub-cell">{{ $row->open_date ? date('H:i', strtotime($row->open_date)) . ' WIB' : '' }}</span></td>
                            <td>
                                <span class="ops-main-cell">{{ $row->location ?: ($row->affected_sn ?: '-') }}</span>
                                @if($row->perangkat && $row->perangkat->lantai)<span class="ops-sub-cell">Lantai {{ $row->perangkat->lantai }}</span>@endif
                            </td>
                            <td>
                                @if($row->project && $row->project->teamSupports && $row->project->teamSupports->count())
                                    <span class="ops-main-cell">{{ $row->project->teamSupports->take(2)->pluck('name')->implode(', ') }}</span>
                                    @if($row->project->teamSupports->count() > 2)<span class="ops-sub-cell">+{{ $row->project->teamSupports->count() - 2 }} anggota</span>@endif
                                @else
                                    <span class="ops-sub-cell">Belum ditentukan</span>
                                @endif
                            </td>
                            <td><span class="ops-pill {{ $row->severity === 'Critical' ? 'ops-pill-danger' : ($row->severity === 'Major' ? 'ops-pill-warning' : 'ops-pill-success') }}"><i class="fas fa-circle" style="font-size:5px"></i>{{ $row->severity }}</span></td>
                            <td><span class="ops-pill {{ $row->status === 'Closed' ? 'ops-pill-success' : ($row->status === 'In Progress' ? 'ops-pill-warning' : 'ops-pill-danger') }}">{{ $row->status }}</span></td>
                            <td class="text-nowrap">
                                @if($row->closed_date)<span class="ops-main-cell">{{ date('d M Y', strtotime($row->closed_date)) }}</span><span class="ops-sub-cell">{{ date('H:i', strtotime($row->closed_date)) }} WIB</span>@else<span class="ops-sub-cell">Belum ditutup</span>@endif
                            </td>
                            <td>
                                <div class="ops-row-actions">
                                    <a href="{{ route('cases.show', $row->id_case) }}" class="ops-icon-action" title="Lihat detail" aria-label="Lihat detail"><i class="fas fa-eye"></i></a>
                                    @can('edit-case')<a href="{{ route('cases.edit', $row->id_case) }}" class="ops-icon-action is-edit" title="Edit case" aria-label="Edit case"><i class="fas fa-pen"></i></a>@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="ops-empty"><i class="fas fa-inbox fa-2x mb-3 d-block"></i>Tidak ada case yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ops-pagination">
            <span class="ops-pagination-label">@if($cases->total()) Menampilkan {{ $cases->firstItem() }}–{{ $cases->lastItem() }} dari {{ $cases->total() }} case @else Belum ada data @endif</span>
            {{ $cases->links() }}
        </div>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/case-operations.css') }}?v={{ @filemtime(public_path('assets/css/case-operations.css')) ?: '1' }}">
@endpush

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%', allowClear: true, closeOnSelect: false });
});
</script>
@endpush

@extends('layouts.app')

@section('body_class', 'perangkat-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/perangkat.css') }}?v={{ @filemtime(public_path('assets/css/perangkat.css')) ?: '1' }}">
@endpush

@push('pg_btn')
    @can('create-perangkat')
        <a href="{{ route('pergantian.index') }}" class="btn btn-sm btn-neutral"><i class="fas fa-exchange-alt mr-1"></i> Riwayat Pergantian</a>
        <a href="{{ route('perangkat.create') }}" class="btn btn-sm btn-neutral"><i class="fas fa-plus mr-1"></i> Perangkat Baru</a>
    @endcan
@endpush

@section('content')
@php($selectedProjects = (array) request('id_project', []))
<div class="device-workspace">
@include('perangkat._workspace_nav', ['activeDevicePage' => 'inventory'])
<section class="card device-panel mb-5">
    <div class="device-page-header">
        <div class="device-page-heading">
            <span class="device-page-heading__icon"><i class="fas fa-server"></i></span>
            <div><h2>Inventaris Perangkat</h2><p>Temukan perangkat, lokasi, status, dan masa garansi dalam satu tampilan.</p></div>
        </div>
        <div class="device-page-actions">
            <span class="badge badge-light px-3 py-2">{{ $perangkats->total() }} perangkat</span>
            @can('create-perangkat')
                <a href="{{ route('perangkat.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Tambah perangkat</a>
            @endcan
        </div>
    </div>

    <form action="{{ route('perangkat.index') }}" method="GET" id="filterForm" class="app-filter-bar asset-filter device-filter">
        <div class="app-filter-field">
            <label for="search_input">Cari Perangkat</label>
            <input id="search_input" name="search" type="search" class="form-control" value="{{ request('search') }}" placeholder="Serial number, merk, atau tipe">
        </div>
        <div class="app-filter-field">
            <label for="id_project">Filter Project</label>
            <select name="id_project[]" id="id_project" class="form-control select2" multiple data-placeholder="Semua project">
                @foreach($projectes as $id => $nama)
                    <option value="{{ $id }}" @selected(in_array((string) $id, array_map('strval', $selectedProjects), true))>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="app-filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Cari</button>
            <a href="{{ route('perangkat.export', request()->all()) }}" class="btn btn-success" title="Export hasil filter"><i class="fas fa-file-excel"></i></a>
            @if(request()->hasAny(['search', 'id_project']))
                <a href="{{ route('perangkat.index') }}" class="btn btn-secondary text-dark" title="Reset filter"><i class="fas fa-undo"></i></a>
            @endif
        </div>
    </form>

    <div class="table-responsive device-table-wrap">
        <table class="table responsive-data-table device-table">
            <thead>
                <tr>
                    <th>Project / Perangkat</th>
                    <th>Serial Number</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Warranty</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perangkats as $perangkat)
                    <tr>
                        <td data-label="Perangkat" class="app-primary-cell">
                            <span class="app-record-title">{{ $perangkat->merk }} {{ $perangkat->type }}</span>
                            <span class="app-record-subtitle">{{ $perangkat->project->nama_project ?? 'Tanpa project' }}</span>
                            @if($perangkat->type_lisensi)
                                <span class="badge badge-light mt-2">
                                    @if(isset($perangkat->jenisPerangkat) && str_contains(strtolower($perangkat->jenisPerangkat->jns_perangkat), 'virtualisasi'))
                                        <i class="fas fa-microchip mr-1"></i>{{ $perangkat->type_lisensi }} Cores
                                    @else
                                        <i class="fas fa-id-card mr-1"></i>{{ $perangkat->type_lisensi }}
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td data-label="Serial Number"><code>{{ $perangkat->serialnumber ?: '-' }}</code></td>
                        <td data-label="Lokasi">
                            <span class="app-record-title">{{ $perangkat->site ?: '-' }} / {{ $perangkat->lokasi->nama_lokasi ?? '-' }}</span>
                            <span class="app-record-subtitle">
                                @if($perangkat->lokasi && $perangkat->lokasi->alamat)
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($perangkat->lokasi->alamat, 55) }}
                                @else
                                    {{ trim(($perangkat->koordinat ?? '').' '.($perangkat->lantai ?? '')) ?: 'Alamat belum diisi' }}
                                @endif
                            </span>
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-pill badge-primary">{{ $perangkat->status_server ?: 'N/A' }}</span>
                            @if($perangkat->foto_1 || $perangkat->foto_2 || $perangkat->foto_3)
                                <span class="app-record-subtitle text-success"><i class="fas fa-camera mr-1"></i>Foto tersedia</span>
                            @endif
                        </td>
                        <td data-label="Warranty">
                            <span class="small text-nowrap">{{ $perangkat->start_warranty ? \Carbon\Carbon::parse($perangkat->start_warranty)->format('d M y') : '-' }}</span>
                            <span class="app-record-subtitle">s/d {{ $perangkat->end_warranty ? \Carbon\Carbon::parse($perangkat->end_warranty)->format('d M y') : '-' }}</span>
                        </td>
                        <td data-label="Aksi" class="text-center app-actions-cell">
                            <div class="app-action-group">
                                <a href="{{ route('perangkat.show', $perangkat->id_perangkat) }}" class="btn btn-outline-primary" title="Lihat detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('pergantian.create', $perangkat->id_perangkat) }}" class="btn btn-outline-warning" title="Pergantian perangkat"><i class="fas fa-exchange-alt"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Perangkat tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="app-pagination">
        <span class="app-pagination-summary">
            Menampilkan {{ $perangkats->firstItem() ?? 0 }}–{{ $perangkats->lastItem() ?? 0 }} dari {{ $perangkats->total() }} perangkat
        </span>
        {{ $perangkats->withQueryString()->links() }}
    </div>
</section>
</div>
@endsection

@push('styles')
<style>
    .asset-filter .select2-container--bootstrap4 .select2-selection--multiple { min-height: 46px; max-height: 92px; overflow-y: auto; padding: .25rem .45rem; }
    .asset-filter .select2-container--bootstrap4 .select2-selection__choice { max-width: 100%; border: 0; border-radius: 8px; background: #f1f3f6; font-size: .72rem; white-space: normal; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%', allowClear: true, closeOnSelect: false });
});
</script>
@endpush

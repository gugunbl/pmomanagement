@extends('layouts.app')

@section('body_class', 'perangkat-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/perangkat.css') }}?v={{ @filemtime(public_path('assets/css/perangkat.css')) ?: '1' }}">
@endpush

@push('pg_btn')
@can('view-perangkat')
    <a href="{{ route('perangkat.index') }}" class="btn btn-sm btn-neutral"><i class="fas fa-list mr-1"></i> Inventaris</a>
@endcan
@endpush

@section('content')
<div class="device-workspace">
    @include('perangkat._workspace_nav', ['activeDevicePage' => 'summary'])
    <section class="card device-panel mb-5">
        <div class="device-page-header">
            <div class="device-page-heading">
                <span class="device-page-heading__icon"><i class="fas fa-chart-pie"></i></span>
                <div><h2>Summary Perangkat</h2><p>Analisis inventaris berdasarkan kategori atau project.</p></div>
            </div>
            <div class="device-page-actions"><span class="badge badge-light px-3 py-2">{{ $summaryperangkats->total() }} kelompok data</span></div>
        </div>

        {!! Form::open(['route' => 'perangkat.summary', 'method' => 'get', 'class' => 'device-filter']) !!}
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            @if($activeTab == 'project')
                <div class="app-filter-field">
                    <label for="summary-customer">Customer</label>
                    {!! Form::select('customer', ['' => 'Semua customer'] + $customers->toArray(), $customerFilter, ['id' => 'summary-customer', 'class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
                </div>
            @else
                <div class="app-filter-field">
                    <label>Mode Ringkasan</label>
                    <input class="form-control" value="Kategori perangkat per project" readonly>
                </div>
            @endif
            <div class="app-filter-field">
                <label for="summary-search">Cari Project</label>
                {{ Form::text('search', $search, ['id' => 'summary-search', 'class' => 'form-control', 'placeholder'=>'Ketik nama project...']) }}
            </div>
            <div class="app-filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Cari</button>
                @if($search || $customerFilter)
                    <a href="{{ route('perangkat.summary', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-undo-alt"></i></a>
                @endif
            </div>
        {!! Form::close() !!}

        <ul class="nav nav-tabs border-0 device-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link {{ $activeTab == 'category' ? 'active' : '' }}" href="{{ route('perangkat.summary', ['tab' => 'category']) }}"><i class="fas fa-tags mr-1"></i> Berdasarkan Kategori</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab == 'project' ? 'active' : '' }}" href="{{ route('perangkat.summary', ['tab' => 'project']) }}"><i class="fas fa-project-diagram mr-1"></i> Berdasarkan Project</a></li>
        </ul>

        <div class="table-responsive device-table-wrap mt-3">
            @if($activeTab == 'category')
                <table class="table device-table responsive-data-table">
                    <thead><tr><th>No</th><th>Nama Project</th><th>Jenis Perangkat</th><th>Merk</th><th>Tipe</th><th>Jumlah</th></tr></thead>
                    <tbody>
                    @forelse($summaryperangkats as $key => $perangkat)
                        <tr>
                            <td data-label="No">{{ ($summaryperangkats->currentPage() - 1) * $summaryperangkats->perPage() + $key + 1 }}</td>
                            <th data-label="Project" scope="row"><a href="{{ route('perangkat.view-detail-summary', ['id_project' => $perangkat->id_project, 'merk' => $perangkat->merk, 'type' => $perangkat->type]) }}" title="{{ $perangkat->project->nama_project ?? '' }}">{{ Str::limit($perangkat->project->nama_project ?? '-', 90) }}</a></th>
                            <td data-label="Jenis">{{ $perangkat->jenisPerangkat->jns_perangkat ?? '-' }}</td>
                            <td data-label="Merk">{{ $perangkat->merk ?: '-' }}</td>
                            <td data-label="Tipe">{{ $perangkat->type ?: '-' }}</td>
                            <td data-label="Jumlah"><span class="device-count">{{ $perangkat->jml }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Data perangkat tidak ditemukan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            @else
                <table class="table device-table responsive-data-table">
                    <thead><tr><th>No</th><th>Nama Project</th><th>End Maintenance</th><th>PM Maintenance</th><th>Jumlah Perangkat</th></tr></thead>
                    <tbody>
                    @forelse($summaryperangkats as $key => $project)
                        <tr class="{{ $project->status_maintenance != '1' ? 'device-danger-row' : '' }}">
                            <td data-label="No">{{ ($summaryperangkats->currentPage() - 1) * $summaryperangkats->perPage() + $key + 1 }}</td>
                            <th data-label="Project" scope="row"><a href="{{ route('perangkat.view-detail-summary', ['id_project' => $project->id_project]) }}" title="{{ $project->nama_project }}">{{ Str::limit($project->nama_project, 90) }}</a></th>
                            <td data-label="End maintenance">{{ $project->tanggal_akhir_maintenance ? strtoupper(date('d-M-Y', strtotime($project->tanggal_akhir_maintenance))) : '-' }}</td>
                            <td data-label="PM">{{ $project->mPMMaintenance->nama_pm ?? '-' }}</td>
                            <td data-label="Jumlah"><span class="device-count">{{ $project->jml }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Data project tidak ditemukan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            @endif
        </div>
        <div class="app-pagination">
            <span class="app-pagination-summary">Menampilkan {{ $summaryperangkats->firstItem() ?? 0 }}–{{ $summaryperangkats->lastItem() ?? 0 }} dari {{ $summaryperangkats->total() }} data</span>
            {{ $summaryperangkats->withQueryString()->links() }}
        </div>
    </section>
</div>
@endsection

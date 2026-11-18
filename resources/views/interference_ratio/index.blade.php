@extends('layouts.app')

@section('hide_page_header', '1')

@section('content')
@php
    $groupedRatios = [];
    $pageImprovement = 0;
    foreach ($ratios as $ratioRow) {
        $groupKey = $ratioRow->tanggal . '_' . $ratioRow->waktu;
        $groupedRatios[$groupKey][] = $ratioRow;
        $pageImprovement += ((int) $ratioRow->utilisasi_before - (int) $ratioRow->utilisasi_after);
    }
    $averageImprovement = $ratios->count() ? round($pageImprovement / $ratios->count(), 1) : 0;
@endphp

<div class="ops-page">
    <section class="ops-hero">
        <div class="ops-hero-row">
            <div>
                <div class="ops-eyebrow">Wireless Quality</div>
                <h1 class="ops-title">Interference Ratio</h1>
                <p class="ops-subtitle">Bandingkan channel dan utilisasi sebelum serta sesudah optimasi access point.</p>
            </div>
            @can('menu-interferance-create')
                <a href="{{ route('interference-ratio.create') }}" class="ops-primary-action"><i class="fas fa-plus"></i><span>Tambah Pengukuran</span></a>
            @endcan
        </div>
        <div class="ops-stats">
            <div class="ops-stat"><span class="ops-stat-icon"><i class="fas fa-layer-group"></i></span><span><span class="ops-stat-label">Sesi halaman ini</span><strong class="ops-stat-value">{{ count($groupedRatios) }}</strong></span></div>
            <div class="ops-stat"><span class="ops-stat-icon"><i class="fas fa-wifi"></i></span><span><span class="ops-stat-label">Access point</span><strong class="ops-stat-value">{{ $ratios->count() }}</strong></span></div>
            <div class="ops-stat"><span class="ops-stat-icon"><i class="fas fa-chart-line"></i></span><span><span class="ops-stat-label">Rata-rata perbaikan</span><strong class="ops-stat-value">{{ $averageImprovement }}%</strong></span></div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div><h2 class="ops-card-title"><i class="fas fa-filter mr-2 text-primary"></i>Filter pengukuran</h2><p class="ops-card-description">Pilih project untuk menampilkan data access point yang relevan.</p></div>
        </div>
        <div class="ops-card-body">
            <form method="GET" action="{{ route('interference-ratio.index') }}" class="ops-filter-grid ops-filter-grid--compact">
                <div class="ops-field">
                    <label for="ratio_project">Project</label>
                    <select name="id_project" id="ratio_project" class="form-control select2" data-placeholder="Semua project">
                        <option value="">Semua project</option>
                        @foreach($projects as $id => $name)<option value="{{ $id }}" {{ (string) request('id_project') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="ops-filter-actions">
                    <button type="submit" class="ops-button ops-button-primary"><i class="fas fa-search"></i>Terapkan</button>
                    <a href="{{ route('interference-ratio.index') }}" class="ops-button ops-button-muted"><i class="fas fa-undo-alt"></i>Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div><h2 class="ops-card-title">Riwayat optimasi</h2><p class="ops-card-description">Setiap sesi dapat berisi beberapa access point pada tanggal dan waktu yang sama.</p></div>
            <span class="ops-pill ops-pill-primary">{{ $ratios->total() }} data AP</span>
        </div>
        <div class="ops-table-wrap">
            <table class="ops-table" style="min-width:960px">
                <thead>
                    <tr><th rowspan="2">No</th><th rowspan="2">Sesi pengukuran</th><th rowspan="2">Access point</th><th colspan="2" class="text-center">Channel</th><th colspan="2" class="text-center">Interference</th><th rowspan="2">Perubahan</th><th rowspan="2">Aksi</th></tr>
                    <tr><th>Before</th><th>After</th><th>Before</th><th>After</th></tr>
                </thead>
                <tbody>
                    @php $pageOffset = ($ratios->currentPage() - 1) * $ratios->perPage(); $groupIndex = 0; @endphp
                    @forelse($groupedRatios as $group)
                        @php $groupSize = count($group); $firstItem = $group[0]; $groupIndex++; @endphp
                        @foreach($group as $index => $row)
                            @php
                                $apParts = explode(' - ', $row->ap_name);
                                $hostname = count($apParts) >= 4 ? trim($apParts[3]) : $row->ap_name;
                                if (($hostname === '' || $hostname === '-') && count($apParts) >= 5) { $hostname = trim($apParts[4]) !== '-' ? trim($apParts[4]) : $row->ap_name; }
                                if ($hostname === '' || $hostname === '-') { $hostname = $row->ap_name; }
                                $delta = (int) $row->utilisasi_before - (int) $row->utilisasi_after;
                            @endphp
                            <tr>
                                @if($index === 0)
                                    <td rowspan="{{ $groupSize }}">{{ $pageOffset + $groupIndex }}</td>
                                    <td rowspan="{{ $groupSize }}">
                                        <span class="ops-main-cell">{{ date('d M Y', strtotime($firstItem->tanggal)) }}</span>
                                        <span class="ops-sub-cell"><i class="far fa-clock mr-1"></i>{{ $firstItem->waktu }} · {{ $firstItem->project->nama_project ?? '-' }}</span>
                                    </td>
                                @endif
                                <td><span class="ops-main-cell" title="{{ $row->ap_name }}">{{ Str::limit($hostname, 36) }}</span>@if($hostname !== $row->ap_name)<span class="ops-sub-cell">{{ Str::limit($row->ap_name, 48) }}</span>@endif</td>
                                <td><span class="ops-pill ops-pill-info">{{ $row->channel_before }}</span></td>
                                <td><span class="ops-pill ops-pill-primary">{{ $row->channel_after }}</span></td>
                                <td><span class="ops-pill {{ $row->utilisasi_before > 50 ? 'ops-pill-danger' : ($row->utilisasi_before > 30 ? 'ops-pill-warning' : 'ops-pill-success') }}">{{ $row->utilisasi_before }}%</span></td>
                                <td><span class="ops-pill {{ $row->utilisasi_after > 50 ? 'ops-pill-danger' : ($row->utilisasi_after > 30 ? 'ops-pill-warning' : 'ops-pill-success') }}">{{ $row->utilisasi_after }}%</span></td>
                                <td><span class="ops-delta {{ $delta < 0 ? 'is-worse' : '' }}"><i class="fas {{ $delta < 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>{{ abs($delta) }}%</span></td>
                                @if($index === 0)
                                    <td rowspan="{{ $groupSize }}">
                                        <div class="ops-row-actions">
                                            <a href="{{ route('interference-ratio.show', $firstItem->id) }}" class="ops-icon-action" title="Lihat detail" aria-label="Lihat detail"><i class="fas fa-eye"></i></a>
                                            @can('menu-interferance-edit')<a href="{{ route('interference-ratio.edit', $firstItem->id) }}" class="ops-icon-action is-edit" title="Edit pengukuran" aria-label="Edit pengukuran"><i class="fas fa-pen"></i></a>@endcan
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="9" class="ops-empty"><i class="fas fa-broadcast-tower fa-2x mb-3 d-block"></i>Belum ada data interference ratio untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ops-pagination">
            <span class="ops-pagination-label">@if($ratios->total()) Menampilkan {{ $ratios->firstItem() }}–{{ $ratios->lastItem() }} dari {{ $ratios->total() }} data AP @else Belum ada data @endif</span>
            {{ $ratios->links() }}
        </div>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/case-operations.css') }}?v={{ @filemtime(public_path('assets/css/case-operations.css')) ?: '1' }}">
@endpush

@push('scripts')
<script>$(function () { $('.select2').select2({ theme: 'bootstrap4', width: '100%', allowClear: true }); });</script>
@endpush

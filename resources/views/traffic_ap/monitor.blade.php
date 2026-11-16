@extends('layouts.app')

@section('body_class', 'traffic-ap-page')

@push('pg_btn')
    <a href="{{ route('traffic-ap.input', ['id_project' => $selectedProjectId, 'id_lokasi' => $selectedLokasiId]) }}" class="btn btn-sm btn-neutral">
        <i class="fas fa-edit mr-1 text-primary"></i> Input Traffic AP
    </a>
    <a href="{{ route('traffic-ap.mapping.index', ['id_project' => $selectedProjectId, 'id_lokasi' => $selectedLokasiId]) }}" class="btn btn-sm btn-neutral">
        <i class="fas fa-layer-group mr-1 text-primary"></i> Mapping Lantai & AP
    </a>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow traffic-shell">
            @include('traffic_ap._workspace_nav', ['activeTrafficPage' => 'monitor'])
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center flex-wrap traffic-page-heading" style="gap: 12px;">
                            <span class="traffic-page-heading__icon"><i class="fas fa-chart-line"></i></span>
                            <div>
                                <h3 class="mb-0">{{ $title }}</h3>
                                <p class="text-sm text-muted mb-0">Bandingkan traffic dan jumlah pengguna berdasarkan lantai dan periode.</p>
                            </div>
                            @if($totalTraffic !== null)
                                <div class="total-traffic-chip">
                                    <span class="chip-icon"><i class="fas fa-tachometer-alt"></i></span>
                                    <span class="chip-label">TOTAL TRAFFIC</span>
                                    <span class="chip-divider"></span>
                                    <span class="chip-value">{{ $totalTraffic['display'] }}<span class="chip-unit"> {{ $totalTraffic['satuan'] }}</span></span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 text-md-right mt-3 mt-md-0">
                        @if($selectedLokasi && !empty($floors))
                            <a href="{{ route('traffic-ap.monitor.export', request()->all()) }}" class="btn btn-sm btn-success shadow-sm">
                                <i class="fas fa-file-excel mr-1"></i> Export to Excel
                            </a>
                        @endif
                    </div>
                </div>

                {{-- FILTER CARD --}}
                <div class="card bg-secondary shadow-none border p-3 mb-3 traffic-filter-card">
                    <form method="GET" action="{{ route('traffic-ap.monitor') }}" id="filterForm">
                        <div class="row align-items-end">
                            {{-- Dropdown Project --}}
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">
                                        <i class="fas fa-project-diagram mr-1 text-primary"></i> Project
                                    </label>
                                    @if($fixedProject)
                                        <input type="text" class="form-control bg-white font-weight-bold" value="{{ $selectedProject->nama_project ?? '-' }}" readonly>
                                        <input type="hidden" name="id_project" id="filter_id_project" value="{{ $selectedProjectId }}">
                                    @else
                                        <select name="id_project" id="filter_id_project" class="form-control select2">
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->id_project }}" {{ $selectedProjectId == $proj->id_project ? 'selected' : '' }}>
                                                    {{ $proj->nama_project }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>

                            {{-- Dropdown Lokasi --}}
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">
                                        <i class="fas fa-building mr-1 text-primary"></i> Lokasi
                                    </label>
                                    <select name="id_lokasi" id="filter_id_lokasi" class="form-control select2">
                                        @foreach($lokasis as $lok)
                                            <option value="{{ $lok->id_lokasi }}" {{ $selectedLokasiId == $lok->id_lokasi ? 'selected' : '' }}>
                                                {{ $lok->nama_lokasi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">Mode Filter</label>
                                    <select name="filter_mode" id="filter_mode" class="form-control select2">
                                        <option value="month" {{ $filterMode == 'month' ? 'selected' : '' }}>Bulanan</option>
                                        <option value="range" {{ $filterMode == 'range' ? 'selected' : '' }}>Rentang Tanggal</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3" id="filter_month_group" style="display: {{ $filterMode == 'month' ? 'block' : 'none' }};">
                                <div class="row">
                                    <div class="col-7 pr-1">
                                        <div class="form-group mb-md-0">
                                            <label class="form-control-label font-weight-bold">Bulan</label>
                                            @php
                                                $months = [
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ];
                                            @endphp
                                            <select name="bulan" id="filter_bulan" class="form-control select2">
                                                @foreach($months as $mNum => $mName)
                                                    <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>
                                                        {{ $mName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-5 pl-1">
                                        <div class="form-group mb-md-0">
                                            <label class="form-control-label font-weight-bold">Tahun</label>
                                            <select name="tahun" id="filter_tahun" class="form-control select2">
                                                @for($y = date('Y') + 1; $y >= 2024; $y--)
                                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3" id="filter_range_group" style="display: {{ $filterMode == 'range' ? 'block' : 'none' }};">
                                <div class="row">
                                    <div class="col-6 pr-1">
                                        <div class="form-group mb-md-0">
                                            <label class="form-control-label font-weight-bold">Start Date</label>
                                            <input type="date" name="start_date" id="start_date" class="form-control p-2" value="{{ $startDate ?? date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <div class="form-group mb-md-0">
                                            <label class="form-control-label font-weight-bold">End Date</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control p-2" value="{{ $endDate ?? date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-1">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">Satuan</label>
                                    <select name="satuan" id="filter_satuan" class="form-control select2">
                                        <option value="TB" {{ ($selectedSatuan ?? 'GB') == 'TB' ? 'selected' : '' }}>TB</option>
                                        <option value="GB" {{ ($selectedSatuan ?? 'GB') == 'GB' ? 'selected' : '' }}>GB</option>
                                        <option value="MB" {{ ($selectedSatuan ?? 'GB') == 'MB' ? 'selected' : '' }}>MB</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 text-md-right mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter mr-1"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body pt-0">
                @if(!$selectedProject)
                    <div class="alert alert-warning py-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Belum ada project yang dapat diakses oleh akun Anda.
                    </div>
                @elseif(!$selectedLokasi)
                    <div class="alert alert-warning py-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Silakan pilih lokasi terlebih dahulu.
                    </div>
                @elseif(empty($floors))
                    <div class="text-center py-5 traffic-empty-state">
                        <i class="fas fa-table fa-3x text-light mb-3"></i>
                        <h4 class="text-muted">Belum ada data lantai atau traffic pada lokasi <strong>{{ $selectedLokasi->nama_lokasi }}</strong></h4>
                        <p class="text-sm text-muted">Silakan input data traffic terlebih dahulu melalui menu <strong>Traffic AP</strong>.</p>
                        <a href="{{ route('traffic-ap.input') }}?id_project={{ $selectedProjectId }}&id_lokasi={{ $selectedLokasiId }}" class="btn btn-primary mt-2">
                            <i class="fas fa-edit mr-1"></i> Input Data Traffic Sekarang
                        </a>
                    </div>
                @else
                    {{-- TAB NAVIGATION --}}
                    <ul class="nav nav-tabs nav-fill mb-0" id="monitorTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="tab-traffic-tab" data-toggle="tab" href="#tab-traffic" role="tab">
                                <i class="fas fa-chart-bar mr-1"></i> Traffic
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="tab-user-tab" data-toggle="tab" href="#tab-user" role="tab">
                                <i class="fas fa-users mr-1"></i> Jumlah User
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="monitorTabsContent">
                        {{-- TAB 1: TRAFFIC --}}
                        <div class="tab-pane fade show active" id="tab-traffic" role="tabpanel">
                            <div class="table-responsive table-matrix-container shadow-sm border rounded" style="border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;">
                                <table class="table table-matrix table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th class="matrix-th-lantai">LANTAI</th>
                                            @foreach($dateColumns as $dateKey => $col)
                                                <th class="matrix-th-date text-center" title="{{ $col['label'] }}">
                                                    {{ $col['label'] }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($floors as $floorName)
                                            <tr>
                                                <td class="matrix-td-lantai font-weight-bold">
                                                    {{ $floorName }}
                                                </td>
                                                @foreach($dateColumns as $dateKey => $col)
                                                    @php
                                                        $cell = $matrixTraffic[$floorName][$dateKey] ?? null;
                                                        $display = $cell['display'] ?? '-';
                                                        $hasValue = ($display !== '-');
                                                    @endphp
                                                    <td class="matrix-td-val text-center {{ $hasValue ? 'font-weight-600' : 'text-muted' }}">
                                                        {{ $display }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th class="matrix-td-lantai font-weight-bold" style="background-color: #8ea9db !important; color: #000000 !important; border-top: 2px solid #000 !important;">TOTAL</th>
                                            @foreach($dateColumns as $dateKey => $col)
                                                <th class="matrix-td-val text-center font-weight-bold" style="background-color: #e2e8f0; border-top: 2px solid #000 !important; font-size: 0.85rem; color: #000;">
                                                    {{ $dailyTotalsTraffic[$dateKey]['display'] ?? '-' }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 2: JUMLAH USER --}}
                        <div class="tab-pane fade" id="tab-user" role="tabpanel">
                            <div class="table-responsive table-matrix-container shadow-sm border rounded" style="border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;">
                                <table class="table table-matrix table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th class="matrix-th-lantai">LANTAI</th>
                                            @foreach($dateColumns as $dateKey => $col)
                                                <th class="matrix-th-date text-center" title="{{ $col['label'] }}">
                                                    {{ $col['label'] }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($floors as $floorName)
                                            <tr>
                                                <td class="matrix-td-lantai font-weight-bold">
                                                    {{ $floorName }}
                                                </td>
                                                @foreach($dateColumns as $dateKey => $col)
                                                    @php
                                                        $cell = $matrixUser[$floorName][$dateKey] ?? null;
                                                        $display = $cell['display'] ?? '-';
                                                        $hasValue = ($display !== '-');
                                                    @endphp
                                                    <td class="matrix-td-val text-center {{ $hasValue ? 'font-weight-600' : 'text-muted' }}">
                                                        {{ $display }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th class="matrix-td-lantai font-weight-bold" style="background-color: #8ea9db !important; color: #000000 !important; border-top: 2px solid #000 !important;">TOTAL</th>
                                            @foreach($dateColumns as $dateKey => $col)
                                                <th class="matrix-td-val text-center font-weight-bold" style="background-color: #e2e8f0; border-top: 2px solid #000 !important; font-size: 0.85rem; color: #000;">
                                                    {{ $dailyTotalsUser[$dateKey]['display'] ?? '-' }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center mt-3">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i> Menampilkan total <strong>{{ count($floors) }} Lantai</strong> untuk periode
                                @if($filterMode == 'range')
                                    <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>.
                                @else
                                    <strong>{{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}</strong>.
                                @endif
                            </small>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <a href="{{ route('traffic-ap.input') }}?id_project={{ $selectedProjectId }}&id_lokasi={{ $selectedLokasiId }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus mr-1"></i> Input / Ubah Nilai Traffic
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/traffic-ap.css') }}?v={{ @filemtime(public_path('assets/css/traffic-ap.css')) ?: '1' }}">
<style>
#monitorTabs .nav-link {
    color: #525f7f;
    border: 1px solid #dee2e6;
    border-bottom: none;
    background-color: #f1f3f5;
    margin-right: 2px;
    border-radius: .375rem .375rem 0 0;
    padding: .6rem 1.2rem;
    transition: all .15s ease;
}
#monitorTabs .nav-link.active {
    color: #1a237e;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    font-weight: 800;
}
#monitorTabs .nav-link:hover:not(.active) {
    background-color: #e9ecef;
}
.select2-container--bootstrap4 .select2-selection {
    min-height: 44px;
    border: 1px solid #cad1d7;
    border-radius: .375rem;
    padding: .375rem .75rem;
}

/* === MATRIX TABLE STYLES SESUAI GAMBAR 1 === */
.table-matrix-container {
    max-height: 650px;
    overflow: auto;
    position: relative;
    background-color: #ffffff;
}

.table-matrix {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100%;
}

.table-matrix th, .table-matrix td {
    padding: 0.55rem 0.85rem;
    font-size: 0.875rem;
    border: 1px solid #c0c0c0 !important;
    white-space: nowrap;
    vertical-align: middle;
}

/* Sticky top header (Kuning / Yellow) */
.matrix-th-date {
    background-color: #fff200 !important;
    color: #000000 !important;
    font-weight: 700;
    font-size: 0.85rem;
    position: sticky;
    top: 0;
    z-index: 10;
    min-width: 135px;
    text-align: center;
}

/* Top-Left intersection header */
.matrix-th-lantai {
    background-color: #8ea9db !important;
    color: #000000 !important;
    font-weight: 800;
    text-align: center;
    position: sticky;
    top: 0;
    left: 0;
    z-index: 20;
    min-width: 150px;
}

/* Sticky Left column (Lantai - Biru Lembut) */
.matrix-td-lantai {
    background-color: #bdd7ee !important;
    color: #1a202c !important;
    position: sticky;
    left: 0;
    z-index: 5;
    text-align: left;
    font-weight: 700;
    border-right: 2px solid #6b8299 !important;
}

/* Data cells */
.matrix-td-val {
    background-color: #ffffff;
    color: #2d3748;
    text-align: center;
    font-size: 0.875rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.matrix-td-val:hover {
    background-color: #e2e8f0;
}

.table-matrix tbody tr:hover .matrix-td-val {
    background-color: #f7fafc;
}
.table-matrix tbody tr:hover .matrix-td-lantai {
    background-color: #9ec5e8 !important;
}

/* =============================================
   TOTAL TRAFFIC CHIP — Eye-catching Premium
   ============================================= */
.total-traffic-chip {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(45deg, #ff0844 0%, #ffb199 100%);
    background-size: 200% 200%;
    border-radius: 50px;
    padding: 8px 20px 8px 12px;
    box-shadow:
        0 4px 15px rgba(255, 8, 68, 0.4),
        0 2px 6px rgba(255, 177, 153, 0.3),
        inset 0 1px 0 rgba(255,255,255,0.3);
    animation: gradientMove 3s ease infinite, chipPulse 2.5s ease-in-out infinite;
    cursor: default;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.total-traffic-chip:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow:
        0 8px 25px rgba(255, 8, 68, 0.6),
        0 4px 12px rgba(255, 177, 153, 0.5),
        inset 0 1px 0 rgba(255,255,255,0.4);
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes chipPulse {
    0%, 100% {
        box-shadow:
            0 4px 15px rgba(255, 8, 68, 0.4),
            0 2px 6px rgba(255, 177, 153, 0.3),
            inset 0 1px 0 rgba(255,255,255,0.3);
    }
    50% {
        box-shadow:
            0 6px 22px rgba(255, 8, 68, 0.7),
            0 3px 10px rgba(255, 177, 153, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.3),
            0 0 15px rgba(255, 255, 255, 0.4);
    }
}

.chip-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #ffffff;
    color: #ff0844;
    font-size: 0.95rem;
    margin-right: 12px;
    flex-shrink: 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.chip-label {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 1px;
    color: #ffffff;
    text-transform: uppercase;
    line-height: 1;
    margin-right: 4px;
    white-space: nowrap;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
}

.chip-divider {
    width: 2px;
    height: 22px;
    background: rgba(255, 255, 255, 0.5);
    margin: 0 12px;
    flex-shrink: 0;
    border-radius: 2px;
}

.chip-value {
    font-size: 1.15rem;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: -0.3px;
    white-space: nowrap;
    text-shadow: 0 2px 4px rgba(0,0,0,0.25);
    line-height: 1;
}

.chip-unit {
    font-size: 0.8rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    vertical-align: baseline;
    margin-left: 2px;
    opacity: 0.9;
}

/* Visual refresh: keep dense reporting readable without spreadsheet-like glare. */
#monitorTabs {
    gap: 8px;
    padding: 5px;
    border: 0;
    border-radius: 12px;
    background: #eef2f8;
}
#monitorTabs .nav-item { flex: 1; }
#monitorTabs .nav-link,
#monitorTabs .nav-link.active {
    margin: 0;
    border: 0;
    border-radius: 9px;
}
#monitorTabs .nav-link.active { box-shadow: 0 4px 12px rgba(34,55,95,.09); }
.table-matrix-container {
    margin-top: 12px;
    border-color: #dfe5ef !important;
    border-radius: 12px !important;
    box-shadow: none !important;
}
.table-matrix th,
.table-matrix td { border-color: #e2e7ef !important; }
.matrix-th-date {
    background: #243f76 !important;
    color: #fff !important;
    border-color: #375388 !important;
}
.matrix-th-lantai {
    background: #173363 !important;
    color: #fff !important;
    border-color: #375388 !important;
}
.matrix-td-lantai {
    background: #eef3fb !important;
    color: #243856 !important;
    border-right-color: #c8d4e7 !important;
}
.table-matrix tbody tr:nth-child(even) .matrix-td-val { background: #fafbfe; }
.total-traffic-chip,
.total-traffic-chip:hover {
    padding: 7px 14px 7px 8px;
    border: 1px solid #cbe8e4;
    background: #eaf8f6;
    box-shadow: none;
    animation: none;
    transform: none;
}
.total-traffic-chip .chip-icon { width: 28px; height: 28px; margin-right: 8px; color: #0e8b86; box-shadow: none; }
.total-traffic-chip .chip-label,
.total-traffic-chip .chip-value,
.total-traffic-chip .chip-unit { color: #176864; text-shadow: none; }
.total-traffic-chip .chip-divider { height: 18px; margin: 0 9px; background: #b4dcd8; }

@media (max-width: 767.98px) {
    #monitorTabs .nav-link { padding: .7rem .5rem; }
    .total-traffic-chip { margin-left: 58px; }
    .table-matrix th, .table-matrix td { padding: .5rem .65rem; }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // When Project changes, load locations for the new project
    $('#filter_id_project').on('change', function() {
        var projectId = $(this).val();
        if (!projectId) {
            $('#filter_id_lokasi').empty().trigger('change');
            return;
        }

        $.ajax({
            url: "{{ route('traffic-ap.ajax.project-locations', [], false) }}",
            type: 'GET',
            data: { id_project: projectId },
            success: function(locations) {
                var select = $('#filter_id_lokasi');
                select.empty();
                if (locations && locations.length > 0) {
                    locations.forEach(function(lok) {
                        select.append(`<option value="${lok.id_lokasi}">${lok.nama_lokasi}</option>`);
                    });
                    select.val(locations[0].id_lokasi).trigger('change');
                } else {
                    select.trigger('change');
                }
            }
        });
    });
    // Toggle Filter Mode
    $('#filter_mode').on('change', function() {
        if ($(this).val() === 'range') {
            $('#filter_month_group').hide();
            $('#filter_range_group').show();
        } else {
            $('#filter_range_group').hide();
            $('#filter_month_group').show();
        }
    });
});
</script>
@endpush

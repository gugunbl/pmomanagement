@extends('layouts.app')

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
        <div class="card shadow">
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                            <h3 class="mb-0" style="font-weight:800; color:#1a237e; letter-spacing:-0.5px;">{{ $title }}</h3>
                            @if($totalTraffic !== null)
                                <div class="total-traffic-chip">
                                    <span class="chip-icon"><i class="fas fa-tachometer-alt"></i></span>
                                    <span class="chip-label">TOTAL TRAFFIC</span>
                                    <span class="chip-divider"></span>
                                    <span class="chip-value">{{ $totalTraffic['display'] }}<span class="chip-unit"> {{ $totalTraffic['satuan'] }}</span></span>
                                </div>
                            @endif
                        </div>
                        <p class="text-sm text-muted mb-0 mt-1">Laporan matriks traffic AP harian per lantai sesuai project &amp; lokasi terpilih.</p>
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
                <div class="card bg-secondary shadow-none border p-3 mb-3">
                    <form method="GET" action="{{ route('traffic-ap.monitor') }}" id="filterForm">
                        <div class="row align-items-end">
                            {{-- Dropdown Project --}}
                            <div class="col-md-3">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">
                                        <i class="fas fa-project-diagram mr-1 text-primary"></i> Filter Project
                                    </label>
                                    @if($fixedProject)
                                        <input type="text" class="form-control bg-white font-weight-bold" value="{{ $selectedProject->nama_project ?? '-' }}" readonly>
                                        <input type="hidden" name="id_project" id="filter_id_project" value="{{ $selectedProjectId }}">
                                        <small class="text-info d-block mt-1"><i class="fas fa-lock mr-1"></i> Project akun Anda.</small>
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
                            <div class="col-md-3">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">
                                        <i class="fas fa-building mr-1 text-primary"></i> Filter Lokasi
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
                                    <label class="form-control-label font-weight-bold">Bulan</label>
                                    @php
                                        $months = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                    @endphp
                                    <select name="bulan" class="form-control select2">
                                        @foreach($months as $mNum => $mName)
                                            <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>
                                                {{ $mName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label class="form-control-label font-weight-bold">Tahun</label>
                                    <select name="tahun" class="form-control select2">
                                        @for($y = date('Y') + 1; $y >= 2024; $y--)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
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

                            <div class="col-md-1 text-md-right mt-3 mt-md-0">
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
                    <div class="text-center py-5">
                        <i class="fas fa-table fa-3x text-light mb-3"></i>
                        <h4 class="text-muted">Belum ada data lantai atau traffic pada lokasi <strong>{{ $selectedLokasi->nama_lokasi }}</strong></h4>
                        <p class="text-sm text-muted">Silakan input data traffic terlebih dahulu melalui menu <strong>Traffic AP</strong>.</p>
                        <a href="{{ route('traffic-ap.input') }}?id_project={{ $selectedProjectId }}&id_lokasi={{ $selectedLokasiId }}" class="btn btn-primary mt-2">
                            <i class="fas fa-edit mr-1"></i> Input Data Traffic Sekarang
                        </a>
                    </div>
                @else
                    {{-- MATRIKS MONITORING TRAFFIC AP SESUAI GAMBAR 1 --}}
                    <div class="table-responsive table-matrix-container shadow-sm border rounded">
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
                                                $cell = $matrix[$floorName][$dateKey] ?? null;
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
                        </table>
                    </div>

                    <div class="row align-items-center mt-3">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i> Menampilkan total <strong>{{ count($floors) }} Lantai</strong> untuk periode <strong>{{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}</strong>.
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
<style>
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
});
</script>
@endpush

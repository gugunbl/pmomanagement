@extends('layouts.app')

@section('content')

{{-- ================= KPI CARDS ================= --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="row">

            {{-- Tiket Bulan Lalu --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Tiket Bulan Lalu</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $tiketBulanLalu ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Tiket Bulan Ini --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Tiket Bulan ini</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $tiketBulanIni ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Tiket Open --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Tiket Open</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $tiketOpen ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Tiket Closed Bulan Ini --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Tiket Closed Bulan ini</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $tiketClosedBulanIni ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Project Maintenance Active --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Maintenance Active</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $projectMaintenanceActive ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Manage Service --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Manage Service</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $manageService ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- PM CM --}}
            <div class="col">
                <div class="card border" style="border-color: #5dade2 !important;">
                    <div class="card-header text-center py-2" style="background-color: #5dade2; border-bottom: 2px solid #5dade2;">
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">PM CM</span>
                    </div>
                    <div class="card-body text-center py-3">
                        <span class="display-3 font-weight-bold" style="color: #333;">{{ $pmCm ?? 0 }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================= DIVIDER ================= --}}
<div class="row">
    <div class="col-12">
        <hr style="border-top: 3px solid #8e44ad; margin-top: 0;">
    </div>
</div>

{{-- ================= CHART + TOP 10 ================= --}}
<div class="row">
    {{-- CHART --}}
    <div class="col-xl-7 col-lg-7 col-12 mb-4">
        <div class="card h-100">
            <div class="card-header border-0">
                <h3 class="mb-0">Total Tickets v/s Closed Ticket</h3>
            </div>
            <div class="card-body">
                <canvas id="ticketChart" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- TOP 10 PROJECT --}}
    <div class="col-xl-5 col-lg-5 col-12 mb-4">
        <div class="card h-100">
            <div class="card-header border-0">
                <h3 class="mb-0">Top 10 Project, Ticket Open</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr style="background-color: #2c3e50; color: #fff;">
                            <th style="font-size: 0.8rem;">Nama Project</th>
                            <th class="text-center" style="font-size: 0.8rem; width: 70px;">Open</th>
                            <th class="text-center" style="font-size: 0.8rem; width: 70px;">Closed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($top10Projects as $proj)
                        @php
                            if ($proj->tipe_maintenance === 'Manage Service') {
                                $rowBg = '#DEB887'; // Light Brown
                            } elseif ($proj->tipe_maintenance === 'PM-CM') {
                                $rowBg = '#4169E1'; // Royal Blue
                                $textColor = '#fff';
                            } else {
                                $rowBg = '#fff';
                                $textColor = '#333';
                            }

                            // For Manage Service, text stays dark
                            if ($proj->tipe_maintenance === 'Manage Service') {
                                $textColor = '#333';
                            }
                        @endphp
                        <tr>
                            <td style="background-color: {{ $rowBg }}; color: {{ $textColor }}; font-size: 0.8rem;">
                                <a href="{{ route('cases.index', ['id_project' => $proj->id_project]) }}"
                                   style="color: {{ $textColor }}; text-decoration: underline;"
                                   title="{{ $proj->nama_project }}">
                                    {{ Str::limit($proj->nama_project, 60, '...') }}
                                </a>
                            </td>
                            <td class="text-center font-weight-bold" style="font-size: 0.85rem;">{{ $proj->open_count }}</td>
                            <td class="text-center" style="font-size: 0.85rem;">{{ $proj->closed_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-2 px-3">
                <small class="d-flex align-items-center" style="gap: 6px;">
                    <span style="display:inline-block; width:14px; height:14px; background-color:#DEB887; border:1px solid #ccc; border-radius:2px;"></span>
                    Manage Service
                    <span class="mx-2">|</span>
                    <span style="display:inline-block; width:14px; height:14px; background-color:#4169E1; border:1px solid #ccc; border-radius:2px;"></span>
                    PM-CM
                </small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('ticketChart').getContext('2d');

const chartLabels = {!! json_encode($chartLabels ?? []) !!};
const chartTotalTickets = {!! json_encode($chartTotalTickets ?? []) !!};
const chartClosedTickets = {!! json_encode($chartClosedTickets ?? []) !!};

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartLabels,
        datasets: [
            {
                label: 'Total Tickets',
                data: chartTotalTickets,
                backgroundColor: '#000080',
                borderColor: '#000080',
                borderWidth: 1,
                barPercentage: 0.6,
                categoryPercentage: 0.7
            },
            {
                label: 'Closed Ticket',
                data: chartClosedTickets,
                type: 'line',
                borderColor: '#e74c3c',
                backgroundColor: '#e74c3c',
                pointBackgroundColor: '#e74c3c',
                pointBorderColor: '#e74c3c',
                pointRadius: 5,
                borderWidth: 2,
                borderDash: [5, 3],
                fill: false,
                tension: 0
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                labels: {
                    usePointStyle: true,
                    boxWidth: 10
                }
            }
        },
        scales: {
            x: {
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>
@endpush

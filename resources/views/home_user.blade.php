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
                        <span class="text-white font-weight-bold" style="font-size: 0.75rem;">Project Maintenance Active</span>
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

{{-- ================= CHART ================= --}}
<div class="row">
    <div class="col-xl-8 col-lg-10 col-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Total Tickets v/s Closed Ticket</h3>
            </div>
            <div class="card-body">
                <canvas id="ticketChart" height="100"></canvas>
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

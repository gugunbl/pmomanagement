@extends('layouts.app')

@section('content')

{{-- ================= KPI CARDS ================= --}}
<div class="row">

    {{-- TOTAL PROJECT IMPLEMENTASI AKTIF --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">
                            Project Implementasi Aktif
                        </h5>
                        <span class="h2 font-weight-bold mb-0">
                            {{ $totalImplementasiAktif }}
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="ni ni-briefcase-24"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm text-muted">
                    Total project implementasi berjalan
                </p>
            </div>
        </div>
    </div>

    {{-- TOTAL PROJECT MAINTENANCE AKTIF --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">
                            Project Maintenance Aktif
                        </h5>
                        <span class="h2 font-weight-bold mb-0">
                            {{ $totalMaintenanceAktif }}
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="ni ni-settings-gear-65"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm text-muted">
                    Total kontrak maintenance aktif
                </p>
            </div>
        </div>
    </div>

    {{-- OPTIONAL CARD --}}
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">
                            Total Project
                        </h5>
                        <span class="h2 font-weight-bold mb-0">
                            {{ $totalImplementasiAktif + $totalMaintenanceAktif }}
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="ni ni-chart-bar-32"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm text-muted">
                    Akumulasi seluruh project
                </p>
            </div>
        </div>
    </div>

</div>

{{-- ================= CHART & TABLE ================= --}}
<div class="row mt-4">

    {{-- STACKED COLUMN CHART --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Tren Project per Tahun (per Customer)</h3>
            </div>
            <div class="card-body">
                <canvas id="projectChart" height="120"></canvas>
                
<div class="mt-4">
    <h5 class="text-muted mb-3">Total Project per Tahun</h5>

    <div class="table-responsive">
        <table class="table table-sm table-bordered text-center">
            <thead class="thead-light">
                <tr>
                    @foreach($totalPerTahun as $row)
                        <th>{{ $row->tahun }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($totalPerTahun as $row)
                        <td class="font-weight-bold">
                            {{ $row->total }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>



            </div>
        </div>
    </div>

    {{-- TABLE MAINTENANCE PER CUSTOMER --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Project Maintenance Aktif per Customer</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th class="text-right">Jumlah Project</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceByCustomer as $row)
                        <tr>
                            <td>{{ $row->customer }}</td>
                            <td class="text-right font-weight-bold">
                                {{ $row->total }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('projectChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($years) !!},
        datasets: {!! json_encode($datasets) !!}
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            x: { stacked: true },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>
@endpush

@extends('layouts.app')

@section('content')

{{-- ================= KPI CARDS ================= --}}
<div class="row mt-4">

    {{-- ================= KIRI ================= --}}
    <div class="col-xl-8">

        {{-- KPI CARDS SEJAJAR DENGAN CHART --}}
        <div class="row">
            <div class="col-md-3">
                {{-- Total --}}
                <div class="card card-stats">
                    <div class="card-body">
                        <h5 class="text-muted text-uppercase">Total Project</h5>
                        <span class="h2 font-weight-bold">
                            {{ $totalImplementasiAktif + $totalMaintenanceAktif }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                {{-- Implementasi --}}
                <a href="{{ route('project.index', ['search' => '', 'status_maintenance' => 'implementasi']) }}" class="card card-stats btn-link text-left p-0 border-0">
                    <div class="card-body">
                        <h5 class="text-muted text-uppercase">Implementasi Aktif</h5>
                        <span class="h2 font-weight-bold text-primary">{{ $totalImplementasiAktif }}</span>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                {{-- Maintenance --}}
                <a href="{{ route('project.maintenance') }}" class="card card-stats btn-link text-left p-0 border-0">
                    <div class="card-body">
                        <h5 class="text-muted text-uppercase">Maintenance Aktif</h5>
                        <span class="h2 font-weight-bold text-primary">{{ $totalMaintenanceAktif }}</span>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                {{-- BA Maintenance On Progress --}}
                <a href="{{ route('project.ba.monitoring') }}" class="card card-stats btn-link text-left p-0 border-0">
                    <div class="card-body">
                        <h5 class="text-muted text-uppercase">BA Maintenance On Progress</h5>
                        <span class="h2 font-weight-bold text-primary">{{ $totalBAMaintenanceOnProgress }}</span>
                    </div>
                </a>
            </div>
        </div>

        {{-- CHART --}}
        <div class="card mt-4">
            <div class="card-header border-0">
                <h3 class="mb-0">Tren Project per Tahun (per Customer)</h3>
            </div>
            <div class="card-body">
                <canvas id="projectChart" height="120"></canvas>

                {{-- TOTAL PER TAHUN --}}
                <div class="mt-4">
                    <h5 class="text-muted mb-3">Total Project per Tahun</h5>
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
                                    <td class="font-weight-bold">{{ $row->total }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    {{-- ================= KANAN ================= --}}
    <div class="col-xl-4">

        {{-- MAINTENANCE PER CUSTOMER --}}
        <div class="card mb-4">
            <div class="card-header border-0">
                <h4 class="mb-0">Maintenance Aktif per Customer</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-flush">
                    <tbody>
                        @foreach($maintenanceByCustomer as $row)
                        <tr>
                            <td>{{ $row->customer }}</td>
                            <td class="text-right font-weight-bold">{{ $row->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PROJECT AKAN BERAKHIR --}}
        <div class="card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-danger">
                    Maintenance Akan Berakhir (≤ 5 Bulan)
                </h4>
                @can('send-maintenance-reminder')
                <a href="{{ route('project.maintenance.preview-reminder') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Preview Reminder
                </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th>Project</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceEndingSoon as $p)
                        <tr>
                            <td>{{ $p->customer }}</td>
                            <td>{{ $p->nama_project }}</td>
                            <td class="text-danger">
                                {{ \Carbon\Carbon::parse($p->tanggal_akhir_maintenance)->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Tidak ada project yang akan berakhir
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

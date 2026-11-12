@extends('layouts.app')

@section('content')
@php
    $ticketKpis = [
        ['label' => 'Tiket Bulan Lalu', 'value' => $tiketBulanLalu ?? 0, 'icon' => 'fa-history', 'meta' => 'Periode sebelumnya'],
        ['label' => 'Tiket Bulan Ini', 'value' => $tiketBulanIni ?? 0, 'icon' => 'fa-calendar-check', 'meta' => 'Periode berjalan'],
        ['label' => 'Tiket Open', 'value' => $tiketOpen ?? 0, 'icon' => 'fa-inbox', 'meta' => 'Perlu ditangani'],
        ['label' => 'Closed Bulan Ini', 'value' => $tiketClosedBulanIni ?? 0, 'icon' => 'fa-check-circle', 'meta' => 'Terselesaikan'],
        ['label' => 'Maintenance Aktif', 'value' => $projectMaintenanceActive ?? 0, 'icon' => 'fa-tools', 'meta' => 'Project aktif'],
        ['label' => 'Manage Service', 'value' => $manageService ?? 0, 'icon' => 'fa-headset', 'meta' => 'Layanan terkelola'],
        ['label' => 'PM-CM', 'value' => $pmCm ?? 0, 'icon' => 'fa-sync-alt', 'meta' => 'Preventive & corrective'],
    ];
@endphp

<div class="app-kpi-grid user-kpis">
    @foreach($ticketKpis as $kpi)
        <div class="app-kpi-card">
            <div class="app-kpi-content">
                <span class="app-kpi-label">{{ $kpi['label'] }}</span>
                <strong class="app-kpi-value">{{ $kpi['value'] }}</strong>
                <span class="app-kpi-meta">{{ $kpi['meta'] }}</span>
            </div>
            <span class="app-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></span>
        </div>
    @endforeach
</div>

<div class="app-dashboard-grid">
    <section class="card app-panel mb-0">
        <div class="app-panel-header">
            <div><h2>Performa Tiket</h2><p>Total tiket dibandingkan tiket yang sudah selesai</p></div>
            <span class="badge badge-light">Bulanan</span>
        </div>
        <div class="app-panel-body"><div class="app-chart-wrap"><canvas id="ticketChart"></canvas></div></div>
    </section>

    <section class="card app-panel mb-0">
        <div class="app-panel-header"><div><h2>Top 10 Project</h2><p>Project dengan tiket terbanyak</p></div></div>
        <div class="app-panel-body py-2">
            <ul class="app-list">
                @forelse($top10Projects as $project)
                    <li class="app-list-item align-items-start">
                        <div class="pr-2">
                            <a class="app-list-item-title" href="{{ route('cases.index', ['id_project' => $project->id_project]) }}" title="{{ $project->nama_project }}">{{ Str::limit($project->nama_project, 48) }}</a>
                            <span class="app-list-item-meta">{{ $project->tipe_maintenance }}</span>
                        </div>
                        <div class="d-flex align-items-center" style="gap: .35rem">
                            <span class="app-count-pill" title="Open">{{ $project->open_count }}</span>
                            <span class="badge badge-success" title="Closed">{{ $project->closed_count }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-center text-muted small">Belum ada data tiket.</li>
                @endforelse
            </ul>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ticketCanvas = document.getElementById('ticketChart');
if (ticketCanvas) {
    new Chart(ticketCanvas, {
        type: 'bar',
        data: {
            labels: @json($chartLabels ?? []),
            datasets: [
                { label: 'Total Tickets', data: @json($chartTotalTickets ?? []), backgroundColor: '#333333', borderRadius: 7, barPercentage: 0.55, categoryPercentage: 0.72 },
                { label: 'Closed Ticket', data: @json($chartClosedTickets ?? []), type: 'line', borderColor: '#da251d', backgroundColor: '#da251d', pointBackgroundColor: '#ffffff', pointBorderColor: '#da251d', pointBorderWidth: 3, pointRadius: 4, borderWidth: 2, tension: .3 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 18 } } },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}
</script>
@endpush

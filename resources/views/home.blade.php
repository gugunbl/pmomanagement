@extends('layouts.app')

@section('body_class', 'dashboard-home')
@section('hide_page_header', '1')

@section('content')
@php
    $totalProjectAktif = $totalImplementasiAktif + $totalMaintenanceAktif;
    $implementationPercent = $totalProjectAktif > 0 ? round(($totalImplementasiAktif / $totalProjectAktif) * 100) : 0;
    $maintenancePercent = $totalProjectAktif > 0 ? round(($totalMaintenanceAktif / $totalProjectAktif) * 100) : 0;
    $yearLabels = collect($years)->map(fn ($year) => (string) $year)->values();
    $briTrend = array_fill(0, $yearLabels->count(), 0);
    $otherTrend = array_fill(0, $yearLabels->count(), 0);

    foreach ($datasets as $dataset) {
        $isBri = str_contains(strtolower($dataset['label'] ?? ''), 'bank rakyat indonesia')
            || strtolower($dataset['label'] ?? '') === 'bri';

        foreach (collect($dataset['data'])->values() as $index => $value) {
            if ($isBri) {
                $briTrend[$index] += (int) $value;
            } else {
                $otherTrend[$index] += (int) $value;
            }
        }
    }

    $maintenanceTotal = (int) $maintenanceByCustomer->sum('total');
    $maintenanceLeaders = $maintenanceByCustomer->take(3)->values();
    $maintenanceOtherTotal = max(0, $maintenanceTotal - (int) $maintenanceLeaders->sum('total'));
    $maintenanceDonutLabels = $maintenanceLeaders->pluck('customer')->push('Customer lain')->values();
    $maintenanceDonutData = $maintenanceLeaders->pluck('total')->map(fn ($total) => (int) $total)->push($maintenanceOtherTotal)->values();
    $startYear = $yearLabels->first() ?? now()->year;
    $endYear = $yearLabels->last() ?? now()->year;
    $peak = collect($totalPerTahun)->sortByDesc('total')->first();
@endphp

<main class="portfolio-dashboard">
    <header class="portfolio-toolbar">
        <div class="portfolio-title-group">
            <span class="portfolio-app-icon"><i class="fas fa-th-large"></i></span>
            <div>
                <h1>Dashboard Project &amp; Maintenance</h1>
                <p>Ringkasan portofolio per {{ now()->format('d M Y') }}</p>
            </div>
        </div>
        <div class="portfolio-toolbar-filters" aria-label="Konteks dashboard">
            <span><i class="far fa-user mr-2"></i>Semua customer</span>
            <span><i class="far fa-calendar mr-2"></i>{{ $startYear }} – {{ $endYear }}</span>
        </div>
    </header>

    <div class="portfolio-body">
        <section class="portfolio-kpi-grid" aria-label="Ringkasan project">
            <article class="portfolio-kpi">
                <div class="portfolio-kpi-head">
                    <span>Total project aktif</span>
                    <i class="fas fa-layer-group"></i>
                </div>
                <strong>{{ $totalProjectAktif }}</strong>
                <div class="portfolio-split-progress" aria-hidden="true">
                    <span style="width: {{ $implementationPercent }}%"></span>
                    <span style="width: {{ $maintenancePercent }}%"></span>
                </div>
                <div class="portfolio-legend-inline">
                    <span><i class="is-blue"></i>Implementasi {{ $totalImplementasiAktif }}</span>
                    <span><i class="is-navy"></i>Maintenance {{ $totalMaintenanceAktif }}</span>
                </div>
            </article>

            <article class="portfolio-kpi">
                <div class="portfolio-kpi-head">
                    <span>Implementasi aktif</span>
                    <i class="fas fa-rocket"></i>
                </div>
                <strong>{{ $totalImplementasiAktif }}</strong>
                <p>{{ $implementationPercent }}% dari total project aktif</p>
                <a href="{{ route('project.index', ['search' => '', 'status_maintenance' => 'implementasi']) }}">Lihat daftar project <i class="fas fa-arrow-right"></i></a>
            </article>

            <article class="portfolio-kpi">
                <div class="portfolio-kpi-head">
                    <span>Maintenance aktif</span>
                    <i class="fas fa-wrench"></i>
                </div>
                <strong>{{ $totalMaintenanceAktif }}</strong>
                <p>{{ $maintenancePercent }}% dari total project aktif</p>
                <a href="{{ route('project.maintenance') }}">Lihat masa maintenance <i class="fas fa-arrow-right"></i></a>
            </article>

            <article class="portfolio-kpi is-warning">
                <div class="portfolio-kpi-head">
                    <span>BA on progress</span>
                    <i class="far fa-file-alt"></i>
                </div>
                <strong>{{ $totalBAMaintenanceOnProgress }}</strong>
                <p><span class="portfolio-alert-pill"><i class="far fa-clock"></i> Butuh tindak lanjut</span></p>
                <a href="{{ route('project.ba.monitoring') }}">Lihat daftar BA <i class="fas fa-arrow-right"></i></a>
            </article>
        </section>

        <section class="portfolio-insight-grid">
            <article class="portfolio-panel portfolio-trend-panel">
                <div class="portfolio-panel-head">
                    <div>
                        <h2>Tren project per tahun</h2>
                        <p>Jumlah project BRI dibanding customer lain, {{ $startYear }} – {{ $endYear }}</p>
                    </div>
                    <div class="portfolio-chart-legend">
                        <span><i class="is-navy"></i>Bank Rakyat Indonesia</span>
                        <span><i class="is-amber"></i>Customer lain</span>
                    </div>
                </div>
                @if($peak)
                    <span class="portfolio-insight-pill"><i class="fas fa-chart-line"></i> Puncak di <b>{{ $peak->tahun }}</b> dengan {{ $peak->total }} project</span>
                @endif
                <div class="portfolio-trend-chart"><canvas id="portfolioTrendChart"></canvas></div>
            </article>

            <article class="portfolio-panel portfolio-maintenance-panel">
                <div class="portfolio-panel-head">
                    <div>
                        <h2>Maintenance per customer</h2>
                        <p>Project maintenance yang masih aktif</p>
                    </div>
                </div>
                <div class="portfolio-donut-wrap">
                    <div class="portfolio-donut-chart">
                        <canvas id="maintenanceDonutChart"></canvas>
                        <div class="portfolio-donut-total"><strong>{{ $maintenanceTotal }}</strong><span>project aktif</span></div>
                    </div>
                </div>
                <ul class="portfolio-customer-list">
                    @foreach($maintenanceLeaders as $index => $row)
                        <li>
                            <span><i class="donut-color-{{ $index + 1 }}"></i>{{ $row->customer }}</span>
                            <b>{{ $row->total }}</b>
                            <small>{{ $maintenanceTotal > 0 ? round(($row->total / $maintenanceTotal) * 100) : 0 }}%</small>
                        </li>
                    @endforeach
                    @if($maintenanceOtherTotal > 0)
                        <li>
                            <span><i class="donut-color-4"></i>{{ max(0, $maintenanceByCustomer->count() - 3) }} customer lain</span>
                            <b>{{ $maintenanceOtherTotal }}</b>
                            <small>{{ $maintenanceTotal > 0 ? round(($maintenanceOtherTotal / $maintenanceTotal) * 100) : 0 }}%</small>
                        </li>
                    @endif
                </ul>
                @if($maintenanceLeaders->isNotEmpty() && $maintenanceTotal > 0)
                    <div class="portfolio-customer-note">
                        {{ round(($maintenanceLeaders->first()->total / $maintenanceTotal) * 100) }}% maintenance terpusat di satu customer.
                    </div>
                @endif
            </article>
        </section>

        <section class="portfolio-panel portfolio-expiring-panel">
            <div class="portfolio-panel-head">
                <div>
                    <h2>Segera berakhir</h2>
                    <p>Maintenance yang berakhir dalam 5 bulan ke depan, diurutkan dari yang terdekat</p>
                </div>
                <span class="portfolio-contract-pill"><i class="far fa-bell"></i> {{ $maintenanceEndingSoon->count() }} kontrak</span>
            </div>
            <div class="portfolio-expiring-grid">
                @forelse($maintenanceEndingSoon->take(3) as $project)
                    @php
                        $endDate = \Carbon\Carbon::parse($project->tanggal_akhir_maintenance);
                        $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($endDate, false));
                    @endphp
                    <article class="portfolio-expiring-card {{ $daysLeft <= 60 ? 'is-urgent' : '' }}">
                        <div class="portfolio-expiring-meta">
                            <span>{{ $daysLeft }} hari lagi</span>
                            <time>{{ $endDate->format('d M Y') }}</time>
                        </div>
                        <h3>{{ $project->nama_project }}</h3>
                        <p><i></i>{{ $project->customer }}</p>
                    </article>
                @empty
                    <div class="portfolio-empty-state"><i class="far fa-check-circle"></i> Tidak ada maintenance yang segera berakhir.</div>
                @endforelse
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const trendCanvas = document.getElementById('portfolioTrendChart');
if (trendCanvas) {
    new Chart(trendCanvas, {
        type: 'bar',
        data: {
            labels: @json($yearLabels),
            datasets: [
                { label: 'Bank Rakyat Indonesia', data: @json($briTrend), backgroundColor: '#123a63', borderRadius: 4, borderSkipped: false },
                { label: 'Customer lain', data: @json($otherTrend), backgroundColor: '#eda62f', borderRadius: 4, borderSkipped: false }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: { displayColors: true } },
            scales: {
                x: { stacked: true, grid: { display: false }, border: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0, stepSize: 10 }, border: { display: false }, grid: { color: '#e8edf3' } }
            }
        }
    });
}

const donutCanvas = document.getElementById('maintenanceDonutChart');
if (donutCanvas) {
    new Chart(donutCanvas, {
        type: 'doughnut',
        data: {
            labels: @json($maintenanceDonutLabels),
            datasets: [{ data: @json($maintenanceDonutData), backgroundColor: ['#123a63', '#168db8', '#31b9aa', '#b8c3d1'], borderWidth: 2, borderColor: '#ffffff' }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '69%',
            plugins: { legend: { display: false } }
        }
    });
}
</script>
@endpush

@extends('layouts.app')

@section('content')
@php
    $openTicketProjects = $openTicketProjects ?? collect();
    $topOpenTicketProjects = $openTicketProjects->take(3);
    $remainingOpenProjects = max(0, $openTicketProjects->count() - $topOpenTicketProjects->count());
    $totalOpenProjectTickets = $openTicketProjects->sum('open_count');
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
        <div class="app-panel-header">
            <div><h2>Project dengan Tiket Open</h2><p>3 project yang membutuhkan perhatian lebih dahulu</p></div>
            @if($remainingOpenProjects > 0)
                <button type="button" class="portfolio-detail-button" data-toggle="modal" data-target="#openTicketProjectModal">
                    <i class="far fa-eye"></i> Lihat semua
                </button>
            @endif
        </div>
        <div class="app-panel-body py-2">
            <ul class="app-list">
                @forelse($topOpenTicketProjects as $project)
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
                    <li class="py-4 text-center text-muted small">Tidak ada project dengan tiket open.</li>
                @endforelse
            </ul>
            @if($remainingOpenProjects > 0)
                <div class="user-open-project-note">
                    <i class="fas fa-info-circle"></i>
                    <span>Ada <strong>{{ $remainingOpenProjects }} project lainnya</strong> yang masih memiliki tiket open.</span>
                </div>
            @endif
        </div>
    </section>
</div>

@if($remainingOpenProjects > 0)
    <div class="modal fade monitor-modal user-open-project-modal" id="openTicketProjectModal" tabindex="-1" role="dialog" aria-labelledby="openTicketProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="monitor-eyebrow">Rincian tiket aktif</span>
                        <h5 class="modal-title" id="openTicketProjectModalLabel">Project dengan tiket open</h5>
                    </div>
                    <button type="button" class="close" data-open-project-modal-close aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="monitor-modal-project-meta">
                        <div><span>Total project</span><strong>{{ $openTicketProjects->count() }}</strong></div>
                        <div><span>Total tiket open</span><strong>{{ $totalOpenProjectTickets }}</strong></div>
                    </div>
                    <div class="user-open-project-list">
                        @foreach($openTicketProjects as $index => $project)
                            <article class="user-open-project-row">
                                <span class="user-open-project-rank" aria-label="Urutan {{ $index + 1 }}">{{ $index + 1 }}</span>
                                <div class="user-open-project-copy">
                                    <strong>{{ $project->nama_project }}</strong>
                                    <span><i class="fas fa-layer-group"></i>{{ $project->tipe_maintenance ?: 'Tipe belum ditentukan' }}</span>
                                </div>
                                <div class="user-open-project-actions">
                                    <span class="user-ticket-metric is-open"><small>Open</small><strong>{{ $project->open_count }}</strong></span>
                                    <span class="user-ticket-metric is-closed"><small>Closed</small><strong>{{ $project->closed_count }}</strong></span>
                                    <a href="{{ route('cases.index', ['id_project' => $project->id_project]) }}" class="user-open-project-link" aria-label="Lihat tiket {{ $project->nama_project }}">
                                        <i class="fas fa-arrow-right"></i><span>Lihat tiket</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-open-project-modal-close>Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endif
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

const openTicketProjectModal = $('#openTicketProjectModal');
if (openTicketProjectModal.length) {
    const modalTrigger = $('[data-target="#openTicketProjectModal"]');
    openTicketProjectModal.appendTo(document.body);

    const resetOpenTicketProjectModal = () => {
        openTicketProjectModal.removeClass('show').hide().attr('aria-hidden', 'true');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        modalTrigger.trigger('focus');
    };

    openTicketProjectModal.on('hidden.bs.modal', resetOpenTicketProjectModal);
    openTicketProjectModal.on('click', '[data-open-project-modal-close]', function () {
        openTicketProjectModal.modal('hide');
        window.setTimeout(() => {
            if (openTicketProjectModal.hasClass('show') || $('.modal-backdrop').length) {
                resetOpenTicketProjectModal();
            }
        }, 400);
    });
}
</script>
@endpush

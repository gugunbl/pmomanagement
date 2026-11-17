@extends('layouts.app')

@section('body_class', 'project-workspace-body')

@section('content')
@php
    $hasFilters = filled($tahun) || filled($customer) || filled($pm_maintenance);
    $activePmCount = isset($pmProjectCounts) ? $pmProjectCounts->count() : 0;
@endphp

<div class="maintenance-workspace">
    <section class="maintenance-overview" aria-labelledby="maintenance-title">
        <div class="maintenance-overview-copy">
            <span class="project-kicker">Maintenance control center</span>
            <h2 id="maintenance-title">Project Maintenance</h2>
            <p>Pantau masa kontrak, progres berita acara, dan penanggung jawab project secara ringkas.</p>
        </div>
        <a href="{{ route('project.create') }}" class="btn maintenance-create-btn"><i class="fas fa-plus"></i> Project Baru</a>
    </section>

    <section class="maintenance-metrics" aria-label="Ringkasan maintenance">
        <article class="maintenance-metric-card">
            <span class="maintenance-metric-icon is-red"><i class="fas fa-briefcase"></i></span>
            <div><small>Total project</small><strong>{{ number_format($projects->total()) }}</strong><p>{{ $hasFilters ? 'Sesuai filter aktif' : 'Seluruh maintenance' }}</p></div>
        </article>
        <article class="maintenance-metric-card">
            <span class="maintenance-metric-icon is-amber"><i class="far fa-calendar-alt"></i></span>
            <div><small>Periode</small><strong>{{ $tahun ?: 'Semua' }}</strong><p>Tahun akhir maintenance</p></div>
        </article>
        <article class="maintenance-metric-card">
            <span class="maintenance-metric-icon is-slate"><i class="fas fa-user-tie"></i></span>
            <div><small>PM aktif</small><strong>{{ $activePmCount }}</strong><p>Penanggung jawab project</p></div>
        </article>
    </section>

    @if(isset($pmProjectCounts) && $pmProjectCounts->count() > 0)
        <section class="maintenance-pm-strip" aria-label="Jumlah project per project manager">
            <span class="maintenance-pm-label"><i class="fas fa-users"></i> Beban PM</span>
            <div class="maintenance-pm-list">
                @foreach($pmProjectCounts as $pm)
                    <span class="maintenance-pm-chip"><strong>{{ $pm->nama_pm }}</strong><b>{{ $pm->total_projects }}</b></span>
                @endforeach
            </div>
        </section>
    @endif

    <section class="maintenance-filter-card">
        <div class="maintenance-section-heading">
            <div><span class="maintenance-section-icon"><i class="fas fa-filter"></i></span><span><strong>Filter data</strong><small>Persempit daftar berdasarkan periode, customer, atau PM.</small></span></div>
            @if($hasFilters)
                <a href="{{ route('project.maintenance') }}" class="maintenance-reset-link"><i class="fas fa-undo-alt"></i> Reset filter</a>
            @endif
        </div>
        <form method="GET" action="{{ route('project.maintenance') }}" class="maintenance-filter-grid" id="maintenance-filter-form">
            <div class="maintenance-filter-field">
                <label for="maintenance-year">Tahun Maintenance</label>
                <select id="maintenance-year" name="tahun" class="form-control" data-auto-submit>
                    <option value="">Semua Tahun</option>
                    @foreach ($listTahun as $th)
                        <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
            <div class="maintenance-filter-field">
                <label for="maintenance-customer">Customer</label>
                <select id="maintenance-customer" name="customer" class="form-control" data-auto-submit>
                    <option value="">Semua Customer</option>
                    @foreach ($listCustomer as $val => $label)
                        <option value="{{ $val }}" {{ $customer == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="maintenance-filter-field">
                <label for="maintenance-pm">PM Maintenance</label>
                <select id="maintenance-pm" name="pm_maintenance" class="form-control" data-auto-submit>
                    <option value="">Semua PM</option>
                    @foreach ($listPM as $pm)
                        <option value="{{ $pm->id_pm }}" {{ $pm_maintenance == $pm->id_pm ? 'selected' : '' }}>{{ $pm->nama_pm }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary maintenance-filter-submit"><i class="fas fa-search"></i> Terapkan</button>
        </form>
    </section>

    <section class="maintenance-list-card">
        <div class="maintenance-list-header">
            <div><h3>Daftar Maintenance</h3><p>Menampilkan {{ $projects->firstItem() ?: 0 }}–{{ $projects->lastItem() ?: 0 }} dari {{ $projects->total() }} project</p></div>
            <div class="maintenance-legend"><span><i class="is-danger"></i> Berakhir</span><span><i class="is-warning"></i> ≤ 5 bulan</span><span><i class="is-info"></i> Selesai</span></div>
        </div>

        <div class="table-responsive maintenance-table-wrap">
            <table class="table maintenance-table">
                <thead><tr><th>No</th><th>Project</th><th>Nomor SPK</th><th>Berakhir</th><th>BA</th><th>Progress</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                @forelse ($projects as $key => $item)
                    @php
                        $progressBA = $item->total_ba > 0 ? ($item->total_paid / $item->total_ba) : 0;
                        $start = \Carbon\Carbon::parse($item->tanggal_mulai_maintenance);
                        $end = \Carbon\Carbon::parse($item->tanggal_akhir_maintenance);
                        $now = \Carbon\Carbon::now();
                        if ($now < $start) { $progressTime = 0; }
                        elseif ($now > $end) { $progressTime = 1; }
                        else {
                            $totalDays = $start->diffInDays($end);
                            $passedDays = $start->diffInDays($now);
                            $progressTime = $totalDays > 0 ? ($passedDays / $totalDays) : 0;
                        }
                        $persen = max(0, min(100, round((($progressBA + $progressTime) / 2) * 100)));
                        $today = \Carbon\Carbon::today();
                        $endDate = \Carbon\Carbon::parse($item->tanggal_akhir_maintenance);
                        if ($item->status_maintenance == 2) { $statusClass = 'is-complete'; $statusLabel = 'Selesai'; }
                        elseif ($endDate->lt($today)) { $statusClass = 'is-expired'; $statusLabel = 'Berakhir'; }
                        elseif ($endDate->lte($today->copy()->addMonths(5))) { $statusClass = 'is-ending'; $statusLabel = 'Segera berakhir'; }
                        else { $statusClass = 'is-active'; $statusLabel = 'Aktif'; }
                    @endphp
                    <tr>
                        <td><span class="maintenance-row-number">{{ $projects->firstItem() + $key }}</span></td>
                        <td>
                            <div class="maintenance-project-cell"><span class="maintenance-project-mark {{ $statusClass }}"><i class="fas fa-project-diagram"></i></span><span><strong title="{{ $item->nama_project }}">{{ \Illuminate\Support\Str::limit($item->nama_project, 58) }}</strong><small class="maintenance-status-label {{ $statusClass }}">{{ $statusLabel }}</small></span></div>
                        </td>
                        <td><span class="maintenance-spk">{{ $item->nomor_spk ?: '—' }}</span></td>
                        <td><strong class="maintenance-date">{{ $endDate->format('d M Y') }}</strong></td>
                        <td><span class="maintenance-ba-count"><b>{{ $item->total_paid }}</b>/{{ $item->total_ba }} paid</span></td>
                        <td>
                            <div class="maintenance-progress"><div class="maintenance-progress-meta"><span>Progres</span><strong>{{ $persen }}%</strong></div><div class="maintenance-progress-track"><span style="width: {{ $persen }}%"></span></div></div>
                        </td>
                        <td class="text-right"><a href="{{ route('project.maintenance.edit', $item->id_project) }}" class="btn maintenance-edit-btn" aria-label="Edit {{ $item->nama_project }}"><i class="fas fa-pen"></i><span>Edit</span></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="maintenance-empty"><i class="fas fa-inbox"></i><strong>Data tidak ditemukan</strong><p>Coba ubah atau reset filter yang digunakan.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="maintenance-mobile-list">
            @forelse ($projects as $key => $item)
                @php
                    $progressBA = $item->total_ba > 0 ? ($item->total_paid / $item->total_ba) : 0;
                    $start = \Carbon\Carbon::parse($item->tanggal_mulai_maintenance); $end = \Carbon\Carbon::parse($item->tanggal_akhir_maintenance); $now = \Carbon\Carbon::now();
                    if ($now < $start) { $progressTime = 0; } elseif ($now > $end) { $progressTime = 1; } else { $totalDays = $start->diffInDays($end); $progressTime = $totalDays > 0 ? ($start->diffInDays($now) / $totalDays) : 0; }
                    $persen = max(0, min(100, round((($progressBA + $progressTime) / 2) * 100)));
                    $endDate = \Carbon\Carbon::parse($item->tanggal_akhir_maintenance); $today = \Carbon\Carbon::today();
                    if ($item->status_maintenance == 2) { $statusClass = 'is-complete'; $statusLabel = 'Selesai'; } elseif ($endDate->lt($today)) { $statusClass = 'is-expired'; $statusLabel = 'Berakhir'; } elseif ($endDate->lte($today->copy()->addMonths(5))) { $statusClass = 'is-ending'; $statusLabel = 'Segera berakhir'; } else { $statusClass = 'is-active'; $statusLabel = 'Aktif'; }
                @endphp
                <article class="maintenance-mobile-card">
                    <div class="maintenance-mobile-head"><span class="maintenance-project-mark {{ $statusClass }}"><i class="fas fa-project-diagram"></i></span><span><small>Project #{{ $projects->firstItem() + $key }}</small><strong>{{ $item->nama_project }}</strong></span><span class="maintenance-status-label {{ $statusClass }}">{{ $statusLabel }}</span></div>
                    <div class="maintenance-mobile-meta"><div><small>Nomor SPK</small><strong>{{ $item->nomor_spk ?: '—' }}</strong></div><div><small>Berakhir</small><strong>{{ $endDate->format('d M Y') }}</strong></div><div><small>BA Paid</small><strong>{{ $item->total_paid }}/{{ $item->total_ba }}</strong></div></div>
                    <div class="maintenance-progress"><div class="maintenance-progress-meta"><span>Progres maintenance</span><strong>{{ $persen }}%</strong></div><div class="maintenance-progress-track"><span style="width: {{ $persen }}%"></span></div></div>
                    <a href="{{ route('project.maintenance.edit', $item->id_project) }}" class="btn maintenance-edit-btn"><i class="fas fa-pen"></i> Edit Project</a>
                </article>
            @empty
                <div class="maintenance-empty"><i class="fas fa-inbox"></i><strong>Data tidak ditemukan</strong><p>Coba ubah atau reset filter yang digunakan.</p></div>
            @endforelse
        </div>

        @if($projects->hasPages())
            <div class="maintenance-pagination">{{ $projects->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('[data-auto-submit]').on('change', function () { $('#maintenance-filter-form').trigger('submit'); });
    });
</script>
@endpush

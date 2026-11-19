@extends('layouts.app')

@section('content')
@php
    $reportGroups = $reports->getCollection()->groupBy('project_id');
    $signedOffCount = $reports->getCollection()->where('status', 'signoff')->count();
    $activeCount = $reports->count() - $signedOffCount;
    $projectCounter = $startProjectCounter ?? 0;
@endphp

<div class="monitor-workspace">
    <section class="monitor-overview">
        <div class="monitor-overview-copy">
            <span class="monitor-eyebrow"><i class="fas fa-chart-line"></i> Project reporting</span>
            <h2>Monitoring Laporan Project</h2>
            <p>Pantau periode laporan, perbarui status, kelola komentar, dan akses dokumen signoff.</p>
        </div>
        <div class="monitor-stat-grid">
            <div class="monitor-stat"><span>Total laporan</span><strong>{{ $reports->total() }}</strong></div>
            <div class="monitor-stat"><span>Project di halaman</span><strong>{{ $reportGroups->count() }}</strong></div>
            <div class="monitor-stat is-success"><span>Signoff</span><strong>{{ $signedOffCount }}</strong></div>
            <div class="monitor-stat is-warning"><span>Perlu proses</span><strong>{{ $activeCount }}</strong></div>
        </div>
    </section>

    <section class="ba-control-panel report-control-panel">
        <nav class="ba-tabs report-tabs" aria-label="Kategori monitoring laporan">
            <a href="{{ route('project.report.monitoring', array_merge(request()->query(), ['tab' => 'all', 'page' => null])) }}" class="{{ $activeTab === 'all' ? 'active' : '' }}" @if($activeTab === 'all') aria-current="page" @endif>
                <i class="fas fa-layer-group"></i>
                <span>Semua Laporan<small>{{ $allReportsCount }} laporan sesuai filter</small></span>
            </a>
            <a href="{{ route('project.report.monitoring', array_merge(request()->query(), ['tab' => 'action', 'page' => null])) }}" class="{{ $activeTab === 'action' ? 'active' : '' }}" @if($activeTab === 'action') aria-current="page" @endif>
                <i class="fas fa-clipboard-check"></i>
                <span>Perlu Ditindaklanjuti<small>{{ $actionReportsCount }} belum signoff &amp; upload</small></span>
            </a>
        </nav>

    <form id="report-monitoring-filters" method="GET" action="{{ route('project.report.monitoring') }}" class="monitor-filter-panel report-filter-panel">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <div class="monitor-filter-heading">
            <div class="monitor-filter-heading-copy">
                <span class="monitor-filter-icon"><i class="fas fa-sliders-h"></i></span>
                <div>
                    <h3>Filter laporan</h3>
                    <p>Pilih customer terlebih dahulu, kemudian persempit hasil berdasarkan project.</p>
                </div>
            </div>
            <div class="monitor-filter-heading-status">
                @if($selectedCustomer !== null || $selectedProjectId !== null)
                    <span class="monitor-filter-active"><i class="fas fa-check-circle"></i> Filter aktif</span>
                @else
                    <span class="monitor-filter-auto"><i class="fas fa-bolt"></i> Pilihan diterapkan otomatis</span>
                @endif
            </div>
        </div>

        <div class="monitor-filter-grid">
            <div class="monitor-filter-field">
                <label for="report-monitoring-customer"><span>1</span> Customer</label>
                <select id="report-monitoring-customer" name="customer" class="form-control monitor-filter-select">
                    <option value="">Semua customer</option>
                    @foreach($customerOptions as $customerOption)
                        <option value="{{ $customerOption }}" {{ $selectedCustomer === $customerOption ? 'selected' : '' }}>{{ $customerOption }}</option>
                    @endforeach
                </select>
                <small><i class="fas fa-info-circle"></i> Tampilkan semua atau fokus pada satu customer.</small>
            </div>
            <div class="monitor-filter-field {{ $selectedCustomer === null ? 'is-disabled' : '' }}">
                <label for="report-monitoring-project"><span>2</span> Project</label>
                <select id="report-monitoring-project" name="project" class="form-control monitor-filter-select" {{ $selectedCustomer === null ? 'disabled' : '' }}>
                    <option value="">{{ $selectedCustomer === null ? 'Pilih customer terlebih dahulu' : 'Semua project' }}</option>
                    @foreach($projectOptions as $projectOption)
                        <option value="{{ $projectOption->id_project }}" {{ (string) $selectedProjectId === (string) $projectOption->id_project ? 'selected' : '' }}>{{ $projectOption->nama_project }}</option>
                    @endforeach
                </select>
                <small><i class="fas {{ $selectedCustomer === null ? 'fa-lock' : 'fa-search' }}"></i> {{ $selectedCustomer === null ? 'Aktif setelah customer dipilih.' : 'Ketik untuk mencari nama project.' }}</small>
            </div>
            <div class="monitor-filter-actions">
                <button id="report-monitoring-apply" type="submit" class="btn btn-primary"><i class="fas fa-filter"></i><span>Terapkan filter</span></button>
                <a id="report-monitoring-reset" href="{{ route('project.report.monitoring', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary"><i class="fas fa-undo-alt"></i><span>Reset</span></a>
            </div>
        </div>
    </form>
    </section>

    <div class="monitor-section-heading">
        @if($activeTab === 'action')
            <div><h3>Laporan perlu ditindaklanjuti</h3><p>Belum signoff dan belum diunggah, diurutkan dari periode selesai terdekat.</p></div>
        @else
            <div><h3>Daftar seluruh laporan</h3><p>Ringkasan seluruh periode laporan berdasarkan project.</p></div>
        @endif
        <span>{{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }}</span>
    </div>

    <div class="monitor-project-list">
        @forelse($reportGroups as $projectId => $projectReports)
            @php
                $projectCounter++;
                $firstReport = $projectReports->first();
                $project = $firstReport->project;
                $latestComments = $project->comments->take(3);
            @endphp
            <article class="monitor-project-card">
                <header class="monitor-project-header">
                    <div class="monitor-project-number">{{ str_pad($projectCounter, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="monitor-project-identity">
                        <span>Project</span>
                        <h3><a href="{{ route('project.edit', $projectId) }}">{{ $project->nama_project ?? '-' }}</a></h3>
                        <div class="monitor-project-meta">
                            <span><i class="far fa-file-alt"></i>{{ $project->nomor_spk ?? 'SPK belum tersedia' }}</span>
                            <span><i class="fas fa-layer-group"></i>{{ $projectReports->count() }} laporan</span>
                        </div>
                    </div>
                    <div class="monitor-comment-summary">
                        @if($latestComments->isNotEmpty())
                            <div class="monitor-comment-carousel" data-comment-carousel>
                                <div class="monitor-comment-carousel-head">
                                    <span class="monitor-comment-label">Komentar terakhir</span>
                                    @if($latestComments->count() > 1)
                                        <div class="monitor-comment-carousel-controls" aria-label="Navigasi komentar terakhir">
                                            <button type="button" class="monitor-comment-nav" data-direction="prev" aria-label="Komentar sebelumnya"><i class="fas fa-chevron-left"></i></button>
                                            <span><strong data-comment-current>1</strong> / {{ $latestComments->count() }}</span>
                                            <button type="button" class="monitor-comment-nav" data-direction="next" aria-label="Komentar berikutnya"><i class="fas fa-chevron-right"></i></button>
                                        </div>
                                    @endif
                                </div>
                                <div class="monitor-comment-slides" aria-live="polite">
                                    @foreach($latestComments as $commentIndex => $latestComment)
                                        <div class="monitor-comment-slide {{ $commentIndex === 0 ? 'is-active' : '' }}" data-comment-slide aria-hidden="{{ $commentIndex === 0 ? 'false' : 'true' }}">
                                            <p>{{ Str::limit($latestComment->comment, 100) }}</p>
                                            <small>{{ $latestComment->user->name ?? 'Unknown' }} · {{ optional($latestComment->created_at)->format('d M Y H:i') }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <span class="monitor-comment-empty"><i class="far fa-comment-dots"></i> Belum ada komentar</span>
                        @endif
                        <button type="button" class="btn monitor-comment-button" data-toggle="modal" data-target="#projectReportCommentsModal{{ $projectId }}">
                            <i class="far fa-comments"></i> Komentar ({{ $project->comments->count() }})
                        </button>
                    </div>
                </header>

                <div class="monitor-record-head report-grid">
                    <span>Report</span><span>Periode mulai</span><span>Periode selesai</span><span>Status</span><span>Aksi</span>
                </div>
                <div class="monitor-record-list">
                    @foreach($projectReports as $report)
                        @php
                            $periodEnd = $report->period_end ? \Carbon\Carbon::parse($report->period_end)->startOfDay() : null;
                            $daysToPeriodEnd = $periodEnd ? now()->startOfDay()->diffInDays($periodEnd, false) : null;
                            $teamSlotCount = $report->teamFiles->count();
                            $expectedTeamCount = $teamSlotCount > 0 ? $teamSlotCount : $report->project->teamSupports->count();
                            $uploadedTeamCount = $report->teamFiles->whereNotNull('uploaded_file')->count();
                        @endphp
                        <div class="monitor-record report-grid {{ $activeTab === 'action' && $daysToPeriodEnd !== null && $daysToPeriodEnd < 0 ? 'is-report-overdue' : '' }}">
                            <div data-label="Report"><span class="monitor-record-index">#{{ $report->report_ke }}</span></div>
                            <div data-label="Periode mulai"><i class="far fa-calendar-alt"></i>{{ $report->period_start ? \Carbon\Carbon::parse($report->period_start)->format('d M Y') : '-' }}</div>
                            <div data-label="Periode selesai" class="monitor-period-end">
                                <span><i class="far fa-calendar-check"></i>{{ $report->period_end ? \Carbon\Carbon::parse($report->period_end)->format('d M Y') : '-' }}</span>
                                @if($activeTab === 'action' && $daysToPeriodEnd !== null)
                                    @if($daysToPeriodEnd < 0)
                                        <small class="report-deadline-state is-overdue"><i class="fas fa-exclamation-circle"></i> Lewat {{ abs($daysToPeriodEnd) }} hari</small>
                                    @elseif($daysToPeriodEnd === 0)
                                        <small class="report-deadline-state is-today"><i class="fas fa-bell"></i> Berakhir hari ini</small>
                                    @else
                                        <small class="report-deadline-state is-upcoming"><i class="far fa-clock"></i> {{ $daysToPeriodEnd }} hari lagi</small>
                                    @endif
                                @endif
                                @php($periodReminder = $report->latestPeriodEndNotification())
                                @if($periodReminder && $periodReminder->status === 'sent')
                                    <small class="report-reminder-state is-sent" title="{{ optional($periodReminder->sent_at)->format('d M Y H:i') }}"><i class="fas fa-envelope"></i> Reminder terkirim</small>
                                @elseif($periodReminder && $periodReminder->status === 'failed')
                                    <div class="report-reminder-failed">
                                        <small class="report-reminder-state is-failed" title="{{ $periodReminder->error_message }}"><i class="fas fa-exclamation-circle"></i> Reminder gagal</small>
                                        @canany(['send-maintenance-reminder', 'monitoring-laporan-upload-laporan'])
                                            <form method="POST" action="{{ route('project.report.reminder.resend', $report->id) }}">
                                                @csrf
                                                <button type="submit" class="report-reminder-retry"><i class="fas fa-redo"></i> Kirim ulang</button>
                                            </form>
                                        @endcanany
                                    </div>
                                @elseif($report->period_end && \Carbon\Carbon::parse($report->period_end)->isToday() && $report->status !== 'signoff')
                                    <small class="report-reminder-state is-pending"><i class="far fa-clock"></i> Menunggu scheduler</small>
                                @endif
                            </div>
                            <div data-label="Status">
                                <select class="form-control editable monitor-status-select status-{{ $report->status }}" data-id="{{ $report->id }}" data-field="status" aria-label="Status report {{ $report->report_ke }}">
                                    <option value="open" @selected($report->status === 'open')>Open</option>
                                    <option value="progress" @selected($report->status === 'progress')>Progress</option>
                                    <option value="submitted" @selected($report->status === 'submitted')>Submitted</option>
                                    <option value="signoff" @selected($report->status === 'signoff')>Signoff</option>
                                </select>
                            </div>
                            <div data-label="Aksi" class="monitor-record-actions">
                                @can('monitoring-laporan-upload-laporan')
                                    @if($report->status === 'signoff')
                                        @if($expectedTeamCount > 0)
                                            <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm {{ $uploadedTeamCount === $expectedTeamCount ? 'btn-success' : 'btn-primary' }}"><i class="fas fa-users"></i><span>Kelola {{ $uploadedTeamCount }}/{{ $expectedTeamCount }}</span></a>
                                        @elseif($report->uploaded_file)
                                            <a href="{{ route('project.report.download', $report->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i><span>Lihat file</span></a>
                                            <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm btn-success"><i class="fas fa-sync-alt"></i><span>Ganti</span></a>
                                        @else
                                            <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm btn-success"><i class="fas fa-upload"></i><span>Upload</span></a>
                                        @endif
                                    @else
                                        <span class="monitor-action-hint"><i class="fas fa-lock"></i> Setelah signoff</span>
                                    @endif
                                @else
                                    <span class="monitor-action-hint">Tidak ada aksi</span>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <div class="modal fade monitor-modal" id="projectReportCommentsModal{{ $projectId }}" tabindex="-1" role="dialog" aria-labelledby="projectReportCommentsModalLabel{{ $projectId }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div><span class="monitor-eyebrow">Diskusi project</span><h5 class="modal-title" id="projectReportCommentsModalLabel{{ $projectId }}">{{ Str::limit($project->nama_project ?? '-', 60) }}</h5></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="monitor-modal-project-meta">
                                <div><span>Nama project</span><strong>{{ $project->nama_project ?? '-' }}</strong></div>
                                <div><span>Nomor SPK</span><strong>{{ $project->nomor_spk ?? '-' }}</strong></div>
                            </div>
                            <div class="table-responsive monitor-modal-table">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Report</th><th>Mulai</th><th>Selesai</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach($projectReports as $pendingReport)
                                            <tr><td>#{{ $pendingReport->report_ke }}</td><td>{{ $pendingReport->period_start ? \Carbon\Carbon::parse($pendingReport->period_start)->format('d M Y') : '-' }}</td><td>{{ $pendingReport->period_end ? \Carbon\Carbon::parse($pendingReport->period_end)->format('d M Y') : '-' }}</td><td><span class="monitor-status-badge status-{{ $pendingReport->status }}">{{ ucfirst($pendingReport->status) }}</span></td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="monitor-comment-history">
                                <h6>Riwayat komentar</h6>
                                @forelse($project->comments as $comment)
                                    <div class="monitor-comment-item">
                                        <div class="monitor-avatar">{{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}</div>
                                        <div><div class="monitor-comment-author"><strong>{{ $comment->user->name ?? 'Unknown' }}</strong><time>{{ optional($comment->created_at)->format('d M Y H:i') }}</time></div><p>{!! nl2br(e($comment->comment)) !!}</p></div>
                                    </div>
                                @empty
                                    <div class="monitor-empty"><i class="far fa-comments"></i><span>Belum ada komentar untuk project ini.</span></div>
                                @endforelse
                            </div>
                            <form action="{{ route('project.comments.store', $projectId) }}" method="POST" class="monitor-comment-form">
                                @csrf
                                <label for="comment_project_{{ $projectId }}">Tambahkan komentar</label>
                                <textarea id="comment_project_{{ $projectId }}" name="comment" class="form-control" rows="3" placeholder="Tulis komentar atau alasan perubahan status..." required></textarea>
                                <div class="text-right mt-3"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan komentar</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="monitor-empty-state">
                @if($activeTab === 'action' && $selectedCustomer === null && $selectedProjectId === null)
                    <i class="fas fa-check-circle is-success"></i>
                    <h3>Semua laporan sudah tertangani</h3>
                    <p>Tidak ada laporan yang sekaligus menunggu signoff dan upload.</p>
                @elseif($selectedCustomer !== null || $selectedProjectId !== null)
                    <i class="far fa-folder-open"></i>
                    <h3>Laporan tidak ditemukan</h3>
                    <p>Tidak ada laporan pada tab ini untuk customer atau project yang dipilih.</p>
                @else
                    <i class="far fa-folder-open"></i>
                    <h3>Belum ada laporan</h3>
                    <p>Data laporan project belum tersedia.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div class="app-pagination monitor-pagination"><span class="app-pagination-summary">Menampilkan {{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }} dari {{ $reports->total() }} laporan</span>{{ $reports->withQueryString()->links() }}</div>
</div>

<div id="save-notification" class="save-toast"><i class="fas fa-check-circle mr-2"></i> Data berhasil disimpan</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('.monitor-filter-select').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 0
    });

    var filterSubmitting = false;
    var filterLoadingMessage = 'Sedang memuat laporan...';
    var filterForm = $('#report-monitoring-filters');

    function showFilterLoading(message) {
        filterSubmitting = true;
        $('#report-monitoring-apply')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');

        Swal.fire({
            title: 'Memuat data',
            text: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onBeforeOpen: function () {
                Swal.showLoading();
            }
        });
    }

    filterForm.on('submit', function (event) {
        if (filterSubmitting) {
            event.preventDefault();
            return;
        }

        showFilterLoading(filterLoadingMessage);
    });

    $('#report-monitoring-customer').on('change', function () {
        $('#report-monitoring-project').val('').trigger('change.select2');
        filterLoadingMessage = 'Memuat project untuk customer yang dipilih...';
        if (typeof this.form.requestSubmit === 'function') {
            this.form.requestSubmit();
        } else {
            filterForm.trigger('submit');
        }
    });

    $('#report-monitoring-project').on('change', function () {
        filterLoadingMessage = 'Memuat laporan untuk project yang dipilih...';
        if (typeof this.form.requestSubmit === 'function') {
            this.form.requestSubmit();
        } else {
            filterForm.trigger('submit');
        }
    });

    $('#report-monitoring-reset').on('click', function (event) {
        if (filterSubmitting) {
            event.preventDefault();
            return;
        }

        showFilterLoading('Menampilkan kembali semua laporan...');
    });

    // Bootstrap menambahkan backdrop langsung ke <body>. Modal yang tetap berada
    // di dalam .app-content akan terjebak stacking context dan tertutup backdrop.
    $('.monitor-modal').appendTo(document.body);

    $('.monitor-comment-nav').on('click', function () {
        var button = $(this);
        var carousel = button.closest('[data-comment-carousel]');
        var slides = carousel.find('[data-comment-slide]');
        var currentIndex = slides.index(slides.filter('.is-active'));
        var isPrevious = button.data('direction') === 'prev';
        var nextIndex = isPrevious
            ? (currentIndex - 1 + slides.length) % slides.length
            : (currentIndex + 1) % slides.length;

        carousel.toggleClass('is-reverse', isPrevious);
        slides.removeClass('is-active').attr('aria-hidden', 'true');
        slides.eq(nextIndex).addClass('is-active').attr('aria-hidden', 'false');
        carousel.find('[data-comment-current]').text(nextIndex + 1);
    });

    function showNotification() {
        var toast = $('#save-notification');
        toast.addClass('show');
        setTimeout(function () { toast.removeClass('show'); }, 3000);
    }

    $('.editable').on('change', function () {
        var element = $(this);
        element.addClass('is-loading');
        $.ajax({
            url: "{{ route('project.report.monitoring.update') }}",
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", id: element.data('id'), field: element.data('field'), value: element.val() },
            success: function (response) { if (response.success) { showNotification(); if (response.reload) location.reload(); } },
            error: function (xhr) { alert('Error updating data'); console.error(xhr); },
            complete: function () { element.removeClass('is-loading'); }
        });
    });
});
</script>
@endpush

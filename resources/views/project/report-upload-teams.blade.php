@extends('layouts.app')

@section('content')
@php
    $uploadedCount = $teamFiles->whereNotNull('uploaded_file')->count();
    $totalTeams = $teamFiles->count();
    $completion = $totalTeams > 0 ? round(($uploadedCount / $totalTeams) * 100) : 0;
    $nextcloudRoot = trim(config('filesystems.project_report_nextcloud_root', ''), '/');
    $nextcloudSubdirectory = trim(config('filesystems.project_report_nextcloud_subdirectory', '30_Preventive_Maintenance'), '/');
    $nextcloudDirectory = collect([$nextcloudRoot, $report->project->link_dokumen ?? '', $nextcloudSubdirectory])
        ->filter(function ($segment) { return $segment !== null && $segment !== ''; })
        ->implode('/');
    $activeErrorTeam = (int) old('team_file_id', session('team_file_error', 0));
@endphp

<div class="team-report-workspace">
    <section class="team-report-header">
        <div>
            <span class="monitor-eyebrow"><i class="fas fa-users"></i> Dokumen laporan per team</span>
            <h2>{{ $report->project->nama_project ?? '-' }}</h2>
            <p>Laporan #{{ $report->report_ke }} · {{ optional($report->period_start)->format('d M Y') }}–{{ optional($report->period_end)->format('d M Y') }}</p>
        </div>
        <a href="{{ route('project.report.monitoring') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </section>

    <section class="team-report-progress-card">
        <div class="team-report-progress-copy">
            <span>Kelengkapan dokumen</span>
            <strong>{{ $uploadedCount }} dari {{ $totalTeams }} laporan tersedia</strong>
        </div>
        <div class="team-report-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $completion }}">
            <span style="width: {{ $completion }}%"></span>
        </div>
        <b>{{ $completion }}%</b>
    </section>

    @if($report->uploaded_file)
        <section class="team-report-legacy">
            <div><i class="fas fa-info-circle"></i><span><strong>Ada satu file existing yang belum dipetakan</strong><small>{{ $report->uploaded_original_name ?: basename($report->uploaded_file) }}</small></span></div>
            <div class="team-report-legacy-actions">
                <a href="{{ route('project.report.download', $report->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye mr-1"></i> Lihat file</a>
                @foreach($teamFiles->whereNull('uploaded_file') as $availableTeamFile)
                    <form method="POST" action="{{ route('project.report.team-file.assign-legacy', [$report->id, $availableTeamFile->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light">Tetapkan ke {{ $availableTeamFile->teamSupport->name ?? 'Team' }}</button>
                    </form>
                @endforeach
            </div>
        </section>
    @endif

    <div class="team-report-grid">
        @foreach($teamFiles as $teamFile)
            @php
                $hasFile = filled($teamFile->uploaded_file);
                $teamName = $teamFile->teamSupport->name ?? 'Team tidak tersedia';
                $expectedFileName = $teamFile->uploadFilename();
            @endphp
            <article class="team-report-card {{ $hasFile ? 'is-complete' : 'is-empty' }}">
                <header>
                    <div class="team-report-icon"><i class="fas {{ $hasFile ? 'fa-file-pdf' : 'fa-users' }}"></i></div>
                    <div><span>Team Support</span><h3>{{ $teamName }}</h3></div>
                    <em><i class="fas {{ $hasFile ? 'fa-check-circle' : 'fa-clock' }}"></i>{{ $hasFile ? 'Tersedia' : 'Belum ada' }}</em>
                </header>

                @if($hasFile)
                    <div class="team-report-file-info">
                        <strong title="{{ $teamFile->uploaded_original_name }}">{{ Str::limit($teamFile->uploaded_original_name ?: basename($teamFile->uploaded_file), 60) }}</strong>
                        <span>
                            @if($teamFile->file_size){{ number_format($teamFile->file_size / 1024 / 1024, 2) }} MB · @endif
                            {{ optional($teamFile->uploaded_at)->format('d M Y H:i') ?: 'Waktu upload belum tersedia' }}
                        </span>
                    </div>
                @else
                    <div class="team-report-empty-copy"><strong>PDF belum tersedia</strong><span>Upload file atau hubungkan file yang sudah ada di Nextcloud.</span></div>
                @endif

                <footer>
                    @if($hasFile)
                        <a href="{{ route('project.report.team-file.download', [$report->id, $teamFile->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Lihat file</a>
                    @endif
                    <button type="button" class="btn btn-sm {{ $hasFile ? 'btn-light' : 'btn-primary' }}" data-toggle="modal" data-target="#teamReportModal{{ $teamFile->id }}">
                        <i class="fas {{ $hasFile ? 'fa-sync-alt' : 'fa-upload' }}"></i> {{ $hasFile ? 'Ganti' : 'Kelola file' }}
                    </button>
                </footer>
            </article>

            <div class="modal fade monitor-modal team-report-modal" id="teamReportModal{{ $teamFile->id }}" tabindex="-1" role="dialog" aria-labelledby="teamReportModalLabel{{ $teamFile->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div><span class="monitor-eyebrow">Laporan team</span><h5 class="modal-title" id="teamReportModalLabel{{ $teamFile->id }}">{{ $teamName }}</h5></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="report-upload-methods" role="tablist">
                                <button type="button" class="report-upload-method {{ $activeErrorTeam === $teamFile->id && $errors->has('existing_file_name') ? '' : 'is-active' }}" data-team-method="computer"><i class="fas fa-upload"></i><span><strong>Upload komputer</strong><small>Gunakan file PDF dari perangkat</small></span></button>
                                <button type="button" class="report-upload-method {{ $activeErrorTeam === $teamFile->id && $errors->has('existing_file_name') ? 'is-active' : '' }}" data-team-method="nextcloud"><i class="fas fa-cloud"></i><span><strong>File dari Nextcloud</strong><small>Verifikasi tanpa upload ulang</small></span></button>
                            </div>

                            <section class="report-upload-panel {{ $activeErrorTeam === $teamFile->id && $errors->has('existing_file_name') ? '' : 'is-active' }}" data-team-panel="computer">
                                <div class="upload-destination-alert mb-3"><div class="upload-destination-title"><i class="fas fa-file-signature mr-1"></i> Nama file otomatis</div><code class="upload-destination-path">{{ $expectedFileName }}</code></div>
                                <form class="team-file-upload-form" action="{{ route('project.report.team-file.upload', [$report->id, $teamFile->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="team_file_id" value="{{ $teamFile->id }}">
                                    <div class="form-group">
                                        <label for="team_upload_file_{{ $teamFile->id }}">File PDF</label>
                                        <div class="custom-file"><input type="file" class="custom-file-input" id="team_upload_file_{{ $teamFile->id }}" name="upload_file" accept="application/pdf" required><label class="custom-file-label" for="team_upload_file_{{ $teamFile->id }}">Pilih file PDF...</label></div>
                                        @if($activeErrorTeam === $teamFile->id) @error('upload_file')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror @endif
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload mr-1"></i> Upload laporan team</button>
                                </form>
                            </section>

                            <section class="report-upload-panel {{ $activeErrorTeam === $teamFile->id && $errors->has('existing_file_name') ? 'is-active' : '' }}" data-team-panel="nextcloud">
                                <div class="report-nextcloud-note"><i class="fas fa-shield-alt"></i><div><strong>File tetap berada di Nextcloud.</strong><span>Sistem hanya memeriksa dan menghubungkan file ke slot {{ $teamName }}.</span></div></div>
                                <form class="team-nextcloud-register-form" action="{{ route('project.report.team-file.register-nextcloud', [$report->id, $teamFile->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="team_file_id" value="{{ $teamFile->id }}">
                                    <div class="form-group">
                                        <label for="existing_file_name_{{ $teamFile->id }}">Nama file PDF di Nextcloud</label>
                                        <input type="text" class="form-control" id="existing_file_name_{{ $teamFile->id }}" name="existing_file_name" value="{{ $activeErrorTeam === $teamFile->id ? old('existing_file_name') : '' }}" placeholder="Contoh: Laporan_{{ Str::slug($teamName, '_') }}.pdf" maxlength="255" required>
                                        @if($activeErrorTeam === $teamFile->id) @error('existing_file_name')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror @endif
                                    </div>
                                    <div class="report-nextcloud-preview"><span>Lokasi yang diperiksa</span><code>{{ $nextcloudDirectory }}/<strong data-team-nextcloud-file>nama-file.pdf</strong></code></div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-link mr-1"></i> Periksa &amp; Hubungkan</button>
                                </form>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('.team-report-modal').appendTo(document.body);

    $('.team-report-modal').each(function () {
        var modal = $(this);
        modal.find('[data-team-method]').on('click', function () {
            var method = $(this).data('team-method');
            modal.find('[data-team-method]').removeClass('is-active');
            $(this).addClass('is-active');
            modal.find('[data-team-panel]').removeClass('is-active');
            modal.find('[data-team-panel="' + method + '"]').addClass('is-active');
        });
        modal.find('[name="existing_file_name"]').on('input', function () {
            modal.find('[data-team-nextcloud-file]').text($.trim($(this).val()) || 'nama-file.pdf');
        }).trigger('input');
    });

    $('.custom-file-input').on('change', function () {
        var fileName = this.files && this.files.length ? this.files[0].name : 'Pilih file PDF...';
        $(this).next('.custom-file-label').text(fileName);
    });

    $('.team-file-upload-form, .team-nextcloud-register-form').on('submit', function () {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        Swal.fire({title: 'Memproses laporan team', text: 'Mohon tunggu sampai proses selesai.', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, onBeforeOpen: function () { Swal.showLoading(); }});
    });

    @if($activeErrorTeam)
        $('#teamReportModal{{ $activeErrorTeam }}').modal('show');
    @endif
});
</script>
@endpush

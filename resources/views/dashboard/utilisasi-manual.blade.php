@extends('layouts.app')

@section('hide_page_header', '1')

@section('content')
<div class="util-page util-manual-page">
    <section class="util-hero">
        <div class="util-hero-main">
            <div>
                <span class="util-eyebrow">People Analytics · Manual Entry</span>
                <h1>Tambah Data Utilisasi</h1>
                <p>Rancangan input aktivitas harian yang langsung mengikuti struktur Dashboard Utilisasi.</p>
            </div>
            <a href="{{ route('dashboard.utilisasi') }}" class="util-action util-action-glass"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </section>

    <div class="util-prototype-note" role="status">
        <span class="util-prototype-icon"><i class="fas fa-pencil-ruler"></i></span>
        <div><strong>Mode rancangan UI</strong><span>Form ini dapat dihitung dan dipreview, tetapi belum menyimpan data ke database.</span></div>
        <span class="util-prototype-badge">Prototype</span>
    </div>

    <form id="manualUtilizationForm" action="#" method="POST" novalidate>
        <div class="util-manual-layout">
            <div>
                <section class="util-form-card">
                    <div class="util-form-card-header">
                        <span class="util-section-icon"><i class="fas fa-user-clock"></i></span>
                        <div><h2>Informasi laporan</h2><p>Identitas karyawan dan parameter dasar perhitungan.</p></div>
                    </div>
                    <div class="util-form-card-body util-form-grid">
                        <div class="util-field util-span-6">
                            <label for="manual_employee">Nama karyawan <span>*</span></label>
                            <div class="util-readonly-field"><i class="fas fa-user-circle"></i><strong>{{ $employeeName }}</strong><span>mengikuti akun login</span></div>
                            <input type="hidden" id="manual_employee" value="{{ $employeeName }}">
                            <small>Identitas tidak dapat diganti dari form ini.</small>
                        </div>
                        <div class="util-field util-span-3">
                            <label for="manual_report_date">Tanggal laporan <span>*</span></label>
                            <input type="date" id="manual_report_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="util-field util-span-3">
                            <label>Jam kerja efektif</label>
                            <div class="util-readonly-field util-readonly-field--compact"><i class="fas fa-clock"></i><strong>{{ $effectiveWorkHours }} jam</strong></div>
                            <input type="hidden" id="manual_effective_hours" value="{{ $effectiveWorkHours }}">
                            <small>Diambil dari menu Pengaturan.</small>
                        </div>
                        <div class="util-field util-span-12">
                            <label>Metode input durasi <span>*</span></label>
                            <div class="util-method-options">
                                <label class="util-method-option">
                                    <input type="radio" name="input_method" value="time_range" checked>
                                    <span class="util-method-icon"><i class="fas fa-business-time"></i></span>
                                    <span><strong>Waktu mulai &amp; selesai</strong><small>Durasi dihitung otomatis dari rentang waktu aktivitas.</small></span>
                                    <i class="fas fa-check-circle util-method-check"></i>
                                </label>
                                <label class="util-method-option">
                                    <input type="radio" name="input_method" value="duration">
                                    <span class="util-method-icon"><i class="fas fa-stopwatch"></i></span>
                                    <span><strong>Durasi langsung</strong><small>Masukkan total menit tanpa menentukan jam mulai dan selesai.</small></span>
                                    <i class="fas fa-check-circle util-method-check"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="util-form-card">
                    <div class="util-form-card-header util-form-card-header--action">
                        <div class="d-flex align-items-center">
                            <span class="util-section-icon mr-3"><i class="fas fa-list-alt"></i></span>
                            <div><h2>Daftar aktivitas</h2><p id="activityMethodHelp">Durasi dihitung otomatis dari waktu mulai dan selesai.</p></div>
                        </div>
                        <button type="button" class="util-button util-button-muted" id="addActivity"><i class="fas fa-plus"></i> Tambah Aktivitas</button>
                    </div>
                    <div class="util-form-card-body">
                        <div id="activityRows"></div>
                        <button type="button" class="util-add-mobile" id="addActivityMobile"><i class="fas fa-plus"></i> Tambah aktivitas lainnya</button>
                    </div>
                </section>

                <section class="util-form-card">
                    <div class="util-form-card-header">
                        <span class="util-section-icon"><i class="fas fa-comment-alt"></i></span>
                        <div><h2>Catatan laporan</h2><p>Informasi tambahan untuk membantu pembaca memahami aktivitas hari tersebut.</p></div>
                    </div>
                    <div class="util-form-card-body">
                        <div class="util-field"><label for="manual_notes">Catatan opsional</label><textarea id="manual_notes" class="form-control" rows="3" placeholder="Contoh: aktivitas dilakukan paralel dengan koordinasi customer…"></textarea></div>
                    </div>
                </section>
            </div>

            <aside class="util-preview-card">
                <div class="util-preview-heading"><span class="util-section-icon"><i class="fas fa-calculator"></i></span><div><h2>Preview Ringkasan</h2><p>Diperbarui otomatis</p></div></div>
                <div class="util-preview-person"><span class="util-preview-avatar"><i class="fas fa-user"></i></span><div><strong id="previewEmployee">Nama karyawan</strong><span id="previewDate">-</span></div></div>
                <div class="util-gauge" id="utilizationGauge" style="--gauge-value:0">
                    <div><strong id="previewUtilization">0%</strong><span>Utilisasi kerja</span></div>
                </div>
                <div class="util-preview-stats">
                    <div><span>Waktu produktif</span><strong id="previewProductive">00:00</strong></div>
                    <div><span>Total aktivitas</span><strong id="previewActivities">0</strong></div>
                    <div><span>Output dokumen</span><strong id="previewDocuments">0</strong></div>
                    <div><span>Jam efektif</span><strong id="previewEffective">{{ $effectiveWorkHours }} jam</strong></div>
                </div>
                <div class="util-formula"><i class="fas fa-info-circle"></i><span>Utilisasi = total durasi aktivitas ÷ jam kerja efektif × 100%.</span></div>
                <div class="util-preview-actions">
                    <button type="button" class="util-button util-button-muted" id="resetManualForm">Reset</button>
                    <button type="button" class="util-button util-button-primary" disabled title="Penyimpanan akan dibuat setelah desain disetujui"><i class="fas fa-save"></i> Simpan Data</button>
                </div>
                <small class="util-save-note">Tombol simpan dinonaktifkan selama tahap desain.</small>
            </aside>
        </div>
    </form>
</div>

<template id="activityRowTemplate">
    <div class="util-activity-row">
        <div class="activity-time-range">
            <div class="util-field"><label>Mulai</label><input type="time" class="form-control activity-start" value="08:00"></div>
            <div class="util-field"><label>Selesai</label><input type="time" class="form-control activity-end" value="09:00"></div>
        </div>
        <div class="activity-duration-direct util-field">
            <label>Durasi aktivitas</label>
            <div class="util-input-suffix"><input type="number" class="form-control activity-duration-input" value="60" min="1" step="5"><span>menit</span></div>
        </div>
        <div class="util-field"><label>Deskripsi aktivitas</label><input type="text" class="form-control activity-description" placeholder="Jelaskan aktivitas yang dilakukan"></div>
        <div class="util-field">
            <label>Output dokumen</label>
            <select class="form-control activity-output">
                <option value="">Tidak ada</option>
                <option value="MOM">MOM</option>
                <option value="Document Implementasi">Document Implementasi</option>
                <option value="MOP">MOP</option>
                <option value="RCA">RCA</option>
                <option value="Document UAT">Document UAT</option>
                <option value="Document Design">Document Design</option>
                <option value="Document SIT">Document SIT</option>
                <option value="Document PAT">Document PAT</option>
                <option value="Berita Acara">Berita Acara</option>
                <option value="Delivery Order">Delivery Order</option>
                <option value="Lainnya">Lainnya…</option>
            </select>
        </div>
        <button type="button" class="util-remove-activity" title="Hapus aktivitas" aria-label="Hapus aktivitas"><i class="fas fa-trash-alt"></i></button>
        <div class="util-field activity-output-other"><label>Nama output lainnya</label><input type="text" class="form-control activity-output-custom" placeholder="Tuliskan jenis output dokumen"></div>
        <div class="util-duration"><span class="activity-duration">60</span><small>menit</small></div>
    </div>
</template>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/utilization-workspace.css') }}?v={{ @filemtime(public_path('assets/css/utilization-workspace.css')) ?: '1' }}">
@endpush

@push('scripts')
<script>
$(function () {
    var rowTemplate = document.getElementById('activityRowTemplate');
    var rowsContainer = document.getElementById('activityRows');

    function minutesBetween(start, end) {
        if (!start || !end) return 0;
        var startParts = start.split(':').map(Number);
        var endParts = end.split(':').map(Number);
        return Math.max(0, (endParts[0] * 60 + endParts[1]) - (startParts[0] * 60 + startParts[1]));
    }

    function formatMinutes(totalMinutes) {
        return String(Math.floor(totalMinutes / 60)).padStart(2, '0') + ':' + String(totalMinutes % 60).padStart(2, '0');
    }

    function updatePreview() {
        var totalMinutes = 0;
        var filledActivities = 0;
        var documentOutputs = 0;
        var inputMethod = $('input[name="input_method"]:checked').val();
        $('.util-activity-row').each(function () {
            var duration = inputMethod === 'duration'
                ? Math.max(0, parseInt($(this).find('.activity-duration-input').val(), 10) || 0)
                : minutesBetween($(this).find('.activity-start').val(), $(this).find('.activity-end').val());
            $(this).find('.activity-duration').text(duration);
            totalMinutes += duration;
            if ($(this).find('.activity-description').val().trim()) filledActivities++;
            if ($(this).find('.activity-output').val()) documentOutputs++;
        });
        var effectiveHours = parseFloat($('#manual_effective_hours').val()) || 0;
        var utilization = effectiveHours > 0 ? Math.round((totalMinutes / 60) / effectiveHours * 1000) / 10 : 0;
        var reportDate = $('#manual_report_date').val();
        $('#previewEmployee').text($('#manual_employee').val().trim() || 'Nama karyawan');
        $('#previewDate').text(reportDate ? new Date(reportDate + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }) : '-');
        $('#previewProductive').text(formatMinutes(totalMinutes));
        $('#previewActivities').text(filledActivities);
        $('#previewDocuments').text(documentOutputs);
        $('#previewEffective').text((effectiveHours || 0) + ' jam');
        $('#previewUtilization').text(utilization + '%');
        $('#utilizationGauge').css('--gauge-value', Math.max(0, Math.min(100, utilization)));
    }

    function updateInputMethod() {
        var inputMethod = $('input[name="input_method"]:checked').val();
        $('#activityRows').toggleClass('is-duration-mode', inputMethod === 'duration');
        $('#activityMethodHelp').text(inputMethod === 'duration'
            ? 'Durasi diisi langsung dalam menit untuk setiap aktivitas.'
            : 'Durasi dihitung otomatis dari waktu mulai dan selesai.');
        updatePreview();
    }

    function addActivity(start, end, description) {
        var fragment = rowTemplate.content.cloneNode(true);
        var row = $(fragment).find('.util-activity-row');
        if (start) row.find('.activity-start').val(start);
        if (end) row.find('.activity-end').val(end);
        if (description) row.find('.activity-description').val(description);
        $('#activityRows').append(row);
        updatePreview();
    }

    $('#addActivity, #addActivityMobile').on('click', function () { addActivity('09:00', '10:00', ''); });
    $(document).on('input change', '#manualUtilizationForm input, #manualUtilizationForm textarea', updatePreview);
    $(document).on('change', 'input[name="input_method"]', updateInputMethod);
    $(document).on('change', '.activity-output', function () {
        $(this).closest('.util-activity-row').toggleClass('has-custom-output', $(this).val() === 'Lainnya');
        updatePreview();
    });
    $(document).on('click', '.util-remove-activity', function () {
        if ($('.util-activity-row').length > 1) $(this).closest('.util-activity-row').remove();
        updatePreview();
    });
    $('#resetManualForm').on('click', function () {
        document.getElementById('manualUtilizationForm').reset();
        rowsContainer.innerHTML = '';
        addActivity('08:00', '09:00', '');
        addActivity('09:00', '10:30', '');
        updateInputMethod();
    });

    addActivity('08:00', '09:00', '');
    addActivity('09:00', '10:30', '');
    updateInputMethod();
});
</script>
@endpush

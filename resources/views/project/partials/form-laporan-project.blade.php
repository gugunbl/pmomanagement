@php
    $totalReports = (int) old('project_report_total', $project->project_report_total ?? 0);
    $selectedPeriodType = old(
        'project_report_period_type',
        $project->project_report_period_type ?: \App\Services\ProjectReportPeriodGenerator::MANUAL
    );
    $periodTypes = [
        'manual' => 'Manual',
        'calendar_month' => 'Kalender bulanan (tanggal 1–akhir bulan)',
        'maintenance_monthly' => 'Bulanan dari tanggal awal maintenance',
        'maintenance_3_months' => 'Setiap 3 bulan',
        'maintenance_4_months' => 'Setiap 4 bulan',
        'maintenance_6_months' => 'Setiap 6 bulan',
    ];
@endphp

<div class="card border-0 report-period-card">
    <div class="card-body">
        <div class="report-period-heading">
            <div>
                <h6 class="heading-small text-muted mb-2">Laporan Preventive Maintenance / Bulanan</h6>
                <p class="mb-0">Buat periode laporan otomatis dari tanggal awal maintenance, lalu koreksi manual bila diperlukan.</p>
            </div>
            <span class="report-period-chip"><i class="far fa-calendar-alt"></i> Maksimal 60 laporan</span>
        </div>

        <div class="report-period-planner">
            <div class="form-group mb-0">
                <label for="project_report_total">Total Laporan</label>
                <input type="number" id="project_report_total" name="project_report_total" class="form-control" min="0" max="60" value="{{ $totalReports }}">
            </div>
            <div class="form-group mb-0">
                <label for="project_report_period_type">Pola Periode</label>
                <select id="project_report_period_type" name="project_report_period_type" class="form-control">
                    @foreach($periodTypes as $value => $label)
                        <option value="{{ $value }}" {{ $selectedPeriodType === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-period-anchor" aria-live="polite">
                <span>Tanggal acuan</span>
                <strong id="report-period-anchor-label">Mengikuti tanggal awal maintenance</strong>
            </div>
            <button type="button" id="generate-report-periods" class="btn btn-primary report-period-generate">
                <i class="fas fa-magic"></i> Generate Periode
            </button>
        </div>

        <input type="hidden" id="project_report_generate" name="project_report_generate" value="{{ old('project_report_generate', 0) }}">
        <input type="hidden" id="project_report_dates_edited" name="project_report_dates_edited" value="{{ old('project_report_dates_edited', 0) }}">

        <div id="report-period-feedback" class="report-period-feedback" role="status" aria-live="polite">
            <i class="fas fa-info-circle"></i>
            <span>Pilih pola selain Manual, lalu klik Generate Periode.</span>
        </div>

        <div class="table-responsive report-period-table-wrap">
            <table class="table table-sm table-bordered mt-3 report-period-table">
                <thead class="thead-light">
                    <tr><th>Laporan Ke</th><th>Periode Start</th><th>Periode End</th><th>Tanggal Submit</th><th>Status</th><th>Keterangan</th></tr>
                </thead>
                <tbody id="project-report-rows">
                @for($i = 1; $i <= $totalReports; $i++)
                    @php
                        $report = $project->reports->firstWhere('report_ke', $i);
                        $periodStart = optional(optional($report)->period_start)->format('Y-m-d');
                        $periodEnd = optional(optional($report)->period_end)->format('Y-m-d');
                        $submissionDate = optional(optional($report)->submission_date)->format('Y-m-d');
                        $status = old("project_reports.$i.status", optional($report)->status ?: 'open');
                    @endphp
                    <tr data-report-row="{{ $i }}">
                        <td class="report-period-number"><span>{{ $i }}</span></td>
                        <td><input type="date" name="project_reports[{{ $i }}][start]" value="{{ old("project_reports.$i.start", $periodStart) }}" class="form-control report-period-date"></td>
                        <td><input type="date" name="project_reports[{{ $i }}][end]" value="{{ old("project_reports.$i.end", $periodEnd) }}" class="form-control report-period-date"></td>
                        <td><input type="date" name="project_reports[{{ $i }}][submission_date]" value="{{ old("project_reports.$i.submission_date", $submissionDate) }}" class="form-control"></td>
                        <td><select name="project_reports[{{ $i }}][status]" class="form-control">
                            @foreach(['open' => 'Open', 'progress' => 'Progress', 'submitted' => 'Submitted', 'signoff' => 'Signoff'] as $value => $label)
                                <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select></td>
                        <td><textarea name="project_reports[{{ $i }}][keterangan]" class="form-control" rows="1">{{ old("project_reports.$i.keterangan", optional($report)->keterangan) }}</textarea></td>
                    </tr>
                @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var $total = $('#project_report_total');
    var $pattern = $('#project_report_period_type');
    var $rows = $('#project-report-rows');
    var $feedback = $('#report-period-feedback');
    var rowCache = {};

    function escapeHtml(value) { return $('<div>').text(value == null ? '' : value).html(); }
    function collectRows() {
        var values = {};
        $rows.find('[data-report-row]').each(function() {
            var index = parseInt($(this).data('report-row'), 10);
            values[index] = {
                start: $(this).find('[name$="[start]"]').val() || '',
                end: $(this).find('[name$="[end]"]').val() || '',
                submission_date: $(this).find('[name$="[submission_date]"]').val() || '',
                status: $(this).find('[name$="[status]"]').val() || 'open',
                keterangan: $(this).find('[name$="[keterangan]"]').val() || ''
            };
        });
        return values;
    }
    function statusOptions(selected) {
        var labels = {open: 'Open', progress: 'Progress', submitted: 'Submitted', signoff: 'Signoff'};
        return Object.keys(labels).map(function(value) {
            return '<option value="' + value + '"' + (selected === value ? ' selected' : '') + '>' + labels[value] + '</option>';
        }).join('');
    }
    function rowHtml(index, data) {
        data = data || {};
        return '<tr data-report-row="' + index + '">' +
            '<td class="report-period-number"><span>' + index + '</span></td>' +
            '<td><input type="date" name="project_reports[' + index + '][start]" value="' + escapeHtml(data.start || '') + '" class="form-control report-period-date"></td>' +
            '<td><input type="date" name="project_reports[' + index + '][end]" value="' + escapeHtml(data.end || '') + '" class="form-control report-period-date"></td>' +
            '<td><input type="date" name="project_reports[' + index + '][submission_date]" value="' + escapeHtml(data.submission_date || '') + '" class="form-control"></td>' +
            '<td><select name="project_reports[' + index + '][status]" class="form-control">' + statusOptions(data.status || 'open') + '</select></td>' +
            '<td><textarea name="project_reports[' + index + '][keterangan]" class="form-control" rows="1">' + escapeHtml(data.keterangan || '') + '</textarea></td></tr>';
    }
    function renderRows() {
        rowCache = $.extend({}, rowCache, collectRows());
        var count = Math.max(0, Math.min(60, parseInt($total.val(), 10) || 0));
        var html = '';
        for (var i = 1; i <= count; i++) html += rowHtml(i, rowCache[i]);
        $rows.html(html);
    }
    function parseIso(value) {
        var parts = (value || '').split('-');
        if (parts.length !== 3) return null;
        return new Date(Date.UTC(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10)));
    }
    function formatIso(date) {
        return date.getUTCFullYear() + '-' + String(date.getUTCMonth() + 1).padStart(2, '0') + '-' + String(date.getUTCDate()).padStart(2, '0');
    }
    function daysInMonth(year, monthIndex) { return new Date(Date.UTC(year, monthIndex + 1, 0)).getUTCDate(); }
    function addMonthsNoOverflow(anchor, months) {
        var targetMonth = anchor.getUTCMonth() + months;
        var year = anchor.getUTCFullYear() + Math.floor(targetMonth / 12);
        var month = ((targetMonth % 12) + 12) % 12;
        return new Date(Date.UTC(year, month, Math.min(anchor.getUTCDate(), daysInMonth(year, month))));
    }
    function addDays(date, days) {
        var result = new Date(date.getTime());
        result.setUTCDate(result.getUTCDate() + days);
        return result;
    }
    function showFeedback(message, type) {
        $feedback.removeClass('is-success is-warning is-danger').addClass(type ? 'is-' + type : '');
        $feedback.find('span').text(message);
    }
    function updateAnchorLabel() {
        var start = $('[name="tanggal_mulai_maintenance"]').val();
        $('#report-period-anchor-label').text(start ? start.split('-').reverse().join('/') : 'Tanggal awal maintenance belum diisi');
    }
    function generatePeriods(requireConfirmation) {
        var pattern = $pattern.val();
        var count = Math.max(0, Math.min(60, parseInt($total.val(), 10) || 0));
        var anchor = parseIso($('[name="tanggal_mulai_maintenance"]').val());
        if (pattern === 'manual') {
            $('#project_report_generate').val('0');
            showFeedback('Mode Manual aktif. Isi atau koreksi tanggal langsung pada tabel.', 'warning');
            return;
        }
        if (!anchor) { showFeedback('Isi Tanggal Awal Maintenance pada tab Informasi terlebih dahulu.', 'danger'); return; }
        if (count < 1) { showFeedback('Isi Total Laporan minimal 1 untuk membuat periode.', 'danger'); return; }

        var currentRows = collectRows();
        var hasDates = Object.keys(currentRows).some(function(key) { return currentRows[key].start || currentRows[key].end; });
        if (requireConfirmation && hasDates && !window.confirm('Generate ulang akan mengganti seluruh tanggal periode mulai dan selesai. Status, tanggal submit, dan keterangan tetap dipertahankan. Lanjutkan?')) return;

        renderRows();
        var monthMap = {maintenance_monthly: 1, maintenance_3_months: 3, maintenance_4_months: 4, maintenance_6_months: 6};
        var finalEnd = null;
        for (var i = 1; i <= count; i++) {
            var start, end;
            if (pattern === 'calendar_month') {
                start = new Date(Date.UTC(anchor.getUTCFullYear(), anchor.getUTCMonth() + i - 1, 1));
                end = new Date(Date.UTC(start.getUTCFullYear(), start.getUTCMonth() + 1, 0));
            } else {
                var months = monthMap[pattern];
                start = addMonthsNoOverflow(anchor, (i - 1) * months);
                end = addDays(addMonthsNoOverflow(anchor, i * months), -1);
            }
            var $row = $rows.find('[data-report-row="' + i + '"]');
            $row.find('[name$="[start]"]').val(formatIso(start));
            $row.find('[name$="[end]"]').val(formatIso(end));
            finalEnd = end;
        }
        rowCache = $.extend({}, rowCache, collectRows());
        $('#project_report_generate').val('1');
        $('#project_report_dates_edited').val('0');
        var maintenanceEnd = parseIso($('[name="tanggal_akhir_maintenance"]').val());
        if (maintenanceEnd && finalEnd && finalEnd > maintenanceEnd) {
            showFeedback('Periode berhasil dibuat, tetapi periode terakhir melewati Tanggal Akhir Maintenance.', 'warning');
        } else {
            showFeedback(count + ' periode berhasil dibuat. Periksa hasilnya sebelum menyimpan.', 'success');
        }
    }

    rowCache = collectRows();
    updateAnchorLabel();
    $('#generate-report-periods').on('click', function() { generatePeriods(true); });
    $pattern.on('change', function() {
        if ($(this).val() === 'manual') {
            $('#project_report_generate').val('0');
            showFeedback('Mode Manual aktif. Tanggal yang sudah ada tidak diubah.', 'warning');
        } else generatePeriods(true);
    });
    $total.on('change', function() {
        renderRows();
        if ($pattern.val() !== 'manual') generatePeriods(true);
    });
    $('[name="tanggal_mulai_maintenance"], [name="tanggal_akhir_maintenance"]').on('change', function() {
        updateAnchorLabel();
        if ($pattern.val() !== 'manual') {
            $('#project_report_generate').val('0');
            showFeedback('Tanggal maintenance berubah. Klik Generate Periode untuk memperbarui jadwal laporan.', 'warning');
        }
    });
    $rows.on('change', '.report-period-date', function() {
        $('#project_report_generate').val('0');
        $('#project_report_dates_edited').val('1');
        rowCache = $.extend({}, rowCache, collectRows());
        showFeedback('Tanggal disesuaikan manual. Nilai ini yang akan disimpan.', 'warning');
    });
});
</script>
@endpush

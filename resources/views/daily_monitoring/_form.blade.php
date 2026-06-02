@csrf

<h6 class="heading-small text-muted mb-4">Informasi Report</h6>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-control-label">Project Name</label>
            <select name="id_project" id="project_select" class="form-control select2" required>
                <option value="">Pilih project...</option>
                @foreach($projects as $id => $name)
                    <option value="{{ $id }}" {{ old('id_project', $monitoring->id_project) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('id_project')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-control-label">Site / Environment Name</label>
            <input type="text" name="site_environment_name" class="form-control" value="{{ old('site_environment_name', $monitoring->site_environment_name) }}" placeholder="Nama cluster/site/environment">
            @error('site_environment_name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-control-label">Report Name</label>
            <input type="text" name="report_name" class="form-control" value="{{ old('report_name', $monitoring->report_name ?? 'Report Daily Monitoring') }}" required>
            @error('report_name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-control-label">Report Tanggal dan Time</label>
            <input type="datetime-local" name="report_datetime" class="form-control" value="{{ old('report_datetime', $monitoring->report_datetime ? $monitoring->report_datetime->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
            @error('report_datetime')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<hr class="my-4">
<h6 class="heading-small text-muted mb-4">Monitoring</h6>
<div class="row">
    @foreach([
        'total_server' => 'Total Server',
        'server_on' => 'Server ON',
        'offline' => 'Offline',
        'total_server_off' => 'Total Server OFF',
    ] as $field => $label)
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-control-label">{{ $label }}</label>
                <input type="number" min="0" name="{{ $field }}" class="form-control" value="{{ old($field, $monitoring->{$field} ?? 0) }}" required>
                @error($field)<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-control-label">Health Indicator</label>
            <select name="health_indicator" class="form-control" required>
                <option value="normal" {{ old('health_indicator', $monitoring->health_indicator) == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="warning" {{ old('health_indicator', $monitoring->health_indicator) == 'warning' ? 'selected' : '' }}>Warning</option>
            </select>
            @error('health_indicator')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-control-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="healthy" {{ old('status', $monitoring->status) == 'healthy' ? 'selected' : '' }}>Healthy</option>
                <option value="not_healthy" {{ old('status', $monitoring->status) == 'not_healthy' ? 'selected' : '' }}>Not Healthy</option>
                <option value="warning" {{ old('status', $monitoring->status) == 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="ok" {{ old('status', $monitoring->status) == 'ok' ? 'selected' : '' }}>OK</option>
            </select>
            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<input type="hidden" name="server_warning" id="server_warning" value="{{ old('server_warning', $monitoring->server_warning ?? 0) }}">
<input type="hidden" name="farm" value="{{ old('farm', $monitoring->farm ?? 0) }}">
<input type="hidden" name="total_machine_farm" value="{{ old('total_machine_farm', $monitoring->total_machine_farm ?? 0) }}">
<input type="hidden" name="status_machine" value="{{ old('status_machine', $monitoring->status_machine) }}">
<input type="hidden" name="alert_status" value="{{ old('alert_status', $monitoring->alert_status) }}">

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="heading-small text-muted mb-0">Tiket Project</h6>
        <div class="server-warning-counter text-right">
            Server Warning:
            <span id="server-warning-display" class="badge badge-warning">{{ old('server_warning', $monitoring->server_warning ?? 0) }}</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>Type</th>
                    <th>Tgl Open Case</th>
                    <th>Subject Case</th>
                    <th>Lokasi Perangkat</th>
                    <th>Jam & Tgl Closed</th>
                    <th>Severity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="project-cases-body">
                <tr>
                    <td colspan="8" class="text-center text-muted">Pilih project untuk melihat tiket.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="form-group">
    <label class="form-control-label">Upload Image</label>
    <input type="file" name="images[]" id="images" class="form-control daily-monitoring-file-input" accept="image/*" multiple>
    <div id="images-label" class="daily-monitoring-file-help">Belum ada gambar dipilih.</div>
    <small class="form-text text-muted">Maksimal 5 gambar, masing-masing maksimal 5MB. Upload baru saat edit akan mengganti gambar lama.</small>
    @error('images')<small class="text-danger d-block">{{ $message }}</small>@enderror
    @error('images.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
</div>

@if(!empty($monitoring->images))
    <div class="mb-4">
        <label class="form-control-label d-block">Image Tersimpan</label>
        <div class="row">
            @foreach($monitoring->images as $imageIndex => $image)
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('daily-monitoring.image', [$monitoring->id, $imageIndex]) }}" target="_blank">
                        <img src="{{ route('daily-monitoring.image', [$monitoring->id, $imageIndex]) }}" class="img-fluid rounded border shadow-sm daily-monitoring-thumb" alt="Daily monitoring image">
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="text-right">
    <a href="{{ route('daily-monitoring.index') }}" class="btn btn-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary btn-save-monitoring">
        <span class="btn-label">Simpan</span>
        <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...</span>
    </button>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: false
        });

        function escapeHtml(value) {
            return $('<div>').text(value || '-').html();
        }

        function loadProjectCases(projectId) {
            var body = $('#project-cases-body');
            var warningInput = $('#server_warning');
            var warningDisplay = $('#server-warning-display');

            if (!projectId) {
                warningInput.val(0);
                warningDisplay.text(0);
                body.html('<tr><td colspan="8" class="text-center text-muted">Pilih project untuk melihat tiket.</td></tr>');
                return;
            }

            body.html('<tr><td colspan="8" class="text-center text-muted">Memuat tiket...</td></tr>');

            var url = "{{ route('daily-monitoring.project-cases', ':project') }}".replace(':project', projectId);
            $.get(url, function(response) {
                warningInput.val(response.total_warning || 0);
                warningDisplay.text(response.total_warning || 0);

                if (!response.cases || response.cases.length === 0) {
                    body.html('<tr><td colspan="8" class="text-center text-muted">Tidak ada tiket untuk project ini.</td></tr>');
                    return;
                }

                var rows = '';
                $.each(response.cases, function(index, item) {
                    rows += '<tr>'
                        + '<td>' + (index + 1) + '</td>'
                        + '<td>' + escapeHtml(item.type) + '</td>'
                        + '<td>' + escapeHtml(item.open_date) + '</td>'
                        + '<td>' + escapeHtml(item.subject) + '</td>'
                        + '<td>' + escapeHtml(item.lokasi_perangkat) + '</td>'
                        + '<td>' + escapeHtml(item.closed_date) + '</td>'
                        + '<td>' + escapeHtml(item.severity) + '</td>'
                        + '<td>' + escapeHtml(item.status) + '</td>'
                        + '</tr>';
                });
                body.html(rows);
            }).fail(function() {
                warningInput.val(0);
                warningDisplay.text(0);
                body.html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat tiket project.</td></tr>');
            });
        }

        $('#project_select').on('change', function() {
            loadProjectCases($(this).val());
        });

        if ($('#project_select').val()) {
            loadProjectCases($('#project_select').val());
        }

        $('.daily-monitoring-form').on('submit', function() {
            var button = $(this).find('.btn-save-monitoring');
            button.prop('disabled', true);
            button.find('.btn-label').addClass('d-none');
            button.find('.btn-loading').removeClass('d-none');
        });

        $('#images').on('change', function() {
            var files = this.files || [];
            if (files.length > 5) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Peringatan', 'Maksimal upload 5 gambar.', 'warning');
                } else {
                    alert('Maksimal upload 5 gambar.');
                }
                $(this).val('');
                $('#images-label').text('Belum ada gambar dipilih.');
                return;
            }

            var label = files.length ? files.length + ' gambar dipilih' : 'Belum ada gambar dipilih.';
            $('#images-label').text(label);
        });
    });
</script>
@endpush

@push('styles')
<style>
    .select2-container--bootstrap4 .select2-selection {
        min-height: 44px;
        border: 1px solid #cad1d7;
        border-radius: .375rem;
        padding: .375rem .75rem;
        background-color: #fff;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection__rendered {
        padding-left: 0;
        line-height: normal;
        color: #8898aa;
    }

    .select2-container--bootstrap4 .select2-selection__arrow {
        height: 100%;
        top: 0;
        right: 8px;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection,
    .select2-container--bootstrap4.select2-container--open .select2-selection {
        border-color: #5e72e4;
        box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, .25);
    }

    .select2-search--dropdown .select2-search__field {
        height: 40px;
        padding: .375rem .75rem;
        border-radius: .375rem;
    }

    .daily-monitoring-thumb {
        height: 120px;
        width: 100%;
        object-fit: cover;
    }

    .daily-monitoring-file-input {
        height: auto;
        padding: .55rem .75rem;
        line-height: 1.5;
        color: #32325d;
        font-weight: 600;
    }

    .daily-monitoring-file-help {
        margin-top: .45rem;
        color: #32325d;
        font-weight: 600;
        font-size: .875rem;
    }

    .server-warning-counter #server-warning-display {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 54px;
        min-height: 44px;
        font-size: 2.6rem;
        line-height: 1;
        margin-left: .5rem;
        padding: .35rem .6rem;
        vertical-align: middle;
    }
</style>
@endpush

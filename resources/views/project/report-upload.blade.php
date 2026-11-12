@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">Upload Laporan</h3>
                    <small class="text-muted">Project: {{ $report->project->nama_project ?? '-' }} | Laporan ke-{{ $report->report_ke }}</small>
                </div>
                <a href="{{ route('project.report.monitoring') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <div class="form-group mb-4">
                    <label>Status saat ini</label>
                    <div class="badge badge-{{ $report->status == 'signoff' ? 'success' : 'secondary' }}">{{ ucfirst($report->status) }}</div>
                </div>

                @if(config('filesystems.project_report_disk') === 'nextcloud')
                    @php
                        $nextcloudRoot = trim(config('filesystems.project_report_nextcloud_root', ''), '/');
                        $nextcloudSubdirectory = trim(config('filesystems.project_report_nextcloud_subdirectory', '30_Preventive_Maintenance'), '/');
                        $nextcloudProjectFolder = $report->project->link_dokumen ?? '';
                        $nextcloudDestination = collect([
                            $nextcloudRoot,
                            $nextcloudProjectFolder,
                            $nextcloudSubdirectory,
                            $uploadFileName,
                        ])->filter(function ($segment) {
                            return $segment !== null && $segment !== '';
                        })->implode('/');
                    @endphp
                    <div class="alert upload-destination-alert mb-4" role="status">
                        <div class="upload-destination-title mb-2"><i class="fas fa-cloud-upload-alt mr-1"></i> Tujuan upload Nextcloud</div>
                        <code class="upload-destination-path">{{ $nextcloudDestination }}</code>
                        <div class="upload-destination-note small mt-2">Folder project mengikuti field <strong>Folder Project di Nextcloud</strong> pada informasi project.</div>
                    </div>
                @endif

                @if(!$uploadFileName)
                    <div class="alert alert-warning" role="alert">
                        Periode mulai dan periode selesai harus diisi sebelum laporan dapat diupload.
                    </div>
                @endif

                @if($report->uploaded_file)
                    <div class="form-group">
                        <label>File yang sudah diupload</label>
                        <div>
                            <a href="{{ route('project.report.download', $report->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-upload mr-1"></i> Lihat File
                            </a>
                            <div class="text-muted small mt-2">
                                Storage: {{ $report->uploaded_disk ?: 'public' }}
                                @if($report->uploaded_original_name)
                                    | {{ $report->uploaded_original_name }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <form id="report-upload-form" action="{{ route('project.report.upload', $report->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="upload_file">Upload File Laporan (PDF, max {{ number_format(intval(config('filesystems.project_report_max_upload_kb', 51200)) / 1024) }}MB)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="upload_file" name="upload_file" accept="application/pdf" required>
                            <label class="custom-file-label" for="upload_file">Pilih file PDF...</label>
                        </div>
                        @error('upload_file')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small id="upload-file-client-error" class="text-danger d-none"></small>
                    </div>
                    <button type="submit" id="upload-submit" class="btn btn-primary" {{ $uploadFileName ? '' : 'disabled' }}>Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var maxUploadBytes = {{ intval(config('filesystems.project_report_max_upload_kb', 51200)) * 1024 }};

        $('.custom-file-input').on('change', function() {
            var input = this;
            var file = input.files && input.files.length ? input.files[0] : null;
            var fileName = $(input).val().split('\\').pop();
            var error = $('#upload-file-client-error');

            $(input).next('.custom-file-label').html(fileName || 'Pilih file PDF...');
            input.setCustomValidity('');
            error.addClass('d-none').text('');
            $('#upload-submit').prop('disabled', false);

            if (file && file.size > maxUploadBytes) {
                var maxMegabytes = Math.round(maxUploadBytes / 1024 / 1024);
                var message = 'Ukuran file ' + (file.size / 1024 / 1024).toFixed(1) + ' MB melebihi batas ' + maxMegabytes + ' MB.';
                input.setCustomValidity(message);
                error.removeClass('d-none').text(message);
                $('#upload-submit').prop('disabled', true);
            }
        });

        var uploadInProgress = false;
        $('#report-upload-form').on('submit', function(event) {
            if (uploadInProgress) {
                event.preventDefault();
                return;
            }

            uploadInProgress = true;
            $('#upload-submit')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');

            Swal.fire({
                title: 'Mengunggah laporan',
                text: 'Mohon tunggu, file laporan sedang dikirim dan disimpan.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                onBeforeOpen: function() {
                    Swal.showLoading();
                }
            });
        });
    });
</script>
@endpush

@extends('layouts.app')

@section('content')
@php
    $nextcloudRoot = trim(config('filesystems.project_report_nextcloud_root', ''), '/');
    $nextcloudSubdirectory = trim(config('filesystems.project_report_nextcloud_document_subdirectory', '31_Document_Maintenance'), '/');
    $nextcloudProjectFolder = $ba->project->link_dokumen ?? '';
    $nextcloudDestination = collect([$nextcloudRoot, $nextcloudProjectFolder, $nextcloudSubdirectory, $uploadFileName])
        ->filter(function ($segment) { return $segment !== null && $segment !== ''; })
        ->implode('/');
@endphp

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-start">
                <div>
                    <span class="monitor-eyebrow"><i class="fas fa-file-signature"></i> Arsip Berita Acara</span>
                    <h3 class="mb-1 mt-2">Upload Dokumen BA</h3>
                    <small class="text-muted">TOP {{ $ba->top_ke }} · {{ $ba->project->nama_project ?? '-' }}</small>
                </div>
                <a href="{{ route('project.ba.monitoring', ['tab' => 'paid']) }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-4">
                    <div><small class="text-muted d-block">Status BA</small><span class="badge badge-success px-3 py-2">Paid</span></div>
                    @if($ba->uploaded_at)
                        <div class="text-md-right mt-2 mt-md-0"><small class="text-muted d-block">Upload terakhir</small><strong>{{ $ba->uploaded_at->format('d M Y H:i') }}</strong></div>
                    @endif
                </div>

                <div class="alert upload-destination-alert mb-4" role="status">
                    <div class="upload-destination-title mb-2"><i class="fas fa-cloud-upload-alt mr-1"></i> Tujuan upload Nextcloud</div>
                    <code class="upload-destination-path">{{ $nextcloudDestination }}</code>
                    <div class="upload-destination-note small mt-2">Nama project otomatis dibatasi agar aman untuk path Nextcloud. File disimpan ke folder <strong>31_Document_Maintenance</strong>.</div>
                </div>

                @if($ba->uploaded_file)
                    <div class="alert alert-light border d-flex justify-content-between align-items-center flex-wrap">
                        <div><strong>Dokumen BA sudah tersedia</strong><div class="text-muted small mt-1">{{ $ba->uploaded_original_name ?: basename($ba->uploaded_file) }}</div></div>
                        <a href="{{ route('project.ba.download', $ba->id) }}" class="btn btn-sm btn-outline-primary mt-2 mt-sm-0"><i class="fas fa-eye mr-1"></i> Lihat File</a>
                    </div>
                @endif

                <form id="ba-upload-form" action="{{ route('project.ba.upload', $ba->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="upload_file">{{ $ba->uploaded_file ? 'Ganti dokumen BA' : 'Pilih dokumen BA' }} <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="upload_file" name="upload_file" accept="application/pdf" required>
                            <label class="custom-file-label" for="upload_file">Pilih file PDF...</label>
                        </div>
                        @error('upload_file')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        <small id="ba-upload-client-error" class="text-danger d-none mt-1"></small>
                        <small class="form-text text-muted">PDF, maksimal {{ number_format(intval(config('filesystems.project_report_max_upload_kb', 51200)) / 1024) }} MB. Nama file akan disesuaikan otomatis.</small>
                    </div>
                    <button type="submit" id="ba-upload-submit" class="btn btn-primary"><i class="fas fa-cloud-upload-alt mr-1"></i> {{ $ba->uploaded_file ? 'Upload Pengganti' : 'Upload ke Nextcloud' }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var maxUploadBytes = {{ intval(config('filesystems.project_report_max_upload_kb', 51200)) * 1024 }};
    var uploadInProgress = false;

    $('#upload_file').on('change', function () {
        var file = this.files && this.files.length ? this.files[0] : null;
        var error = $('#ba-upload-client-error');
        $(this).next('.custom-file-label').text(file ? file.name : 'Pilih file PDF...');
        this.setCustomValidity('');
        error.addClass('d-none').text('');
        $('#ba-upload-submit').prop('disabled', false);

        if (file && file.size > maxUploadBytes) {
            var message = 'Ukuran file melebihi batas ' + Math.round(maxUploadBytes / 1024 / 1024) + ' MB.';
            this.setCustomValidity(message);
            error.removeClass('d-none').text(message);
            $('#ba-upload-submit').prop('disabled', true);
        }
    });

    $('#ba-upload-form').on('submit', function (event) {
        if (uploadInProgress) { event.preventDefault(); return; }
        uploadInProgress = true;
        $('#ba-upload-submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');
        Swal.fire({
            title: 'Mengunggah dokumen BA',
            text: 'File sedang dikirim ke Nextcloud. Mohon jangan menutup halaman.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onBeforeOpen: function () { Swal.showLoading(); }
        });
    });
});
</script>
@endpush

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

                <form action="{{ route('project.report.upload', $report->id) }}" method="POST" enctype="multipart/form-data">
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
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    });
</script>
@endpush

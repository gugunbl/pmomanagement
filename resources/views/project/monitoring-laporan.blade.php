@extends('layouts.app')

@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Monitoring Laporan Project</h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col" style="min-width: 140px;">Nama Project</th>
                                <th scope="col">Nomor SPK</th>
                                <th scope="col">Report Ke</th>
                                <th scope="col">Periode Start</th>
                                <th scope="col">Periode End</th>
                                <th scope="col" style="width: 150px;">Status</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @php
                                $projectCounts = $reports->getCollection()->groupBy('project_id')->map->count();
                                $projectCounter = $startProjectCounter ?? 0;
                                $prevProject = null;
                                $isSilver = false;
                            @endphp
                            @forelse($reports as $report)
                                @php
                                    $isFirst = false;
                                    if ($report->project_id !== $prevProject) {
                                        $isSilver = !$isSilver;
                                        $prevProject = $report->project_id;
                                        $isFirst = true;
                                        $projectCounter++;
                                    }

                                    $rowspan = $projectCounts[$report->project_id] ?? 1;
                                @endphp
                                <tr class="{{ $isSilver ? 'bg-silver' : '' }}">
                                    @if($isFirst)
                                        <td rowspan="{{ $rowspan }}">{{ $projectCounter }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-wrap font-weight-bold" style="min-width: 150px; font-size: 1.05rem;">
                                            <a href="{{ route('project.edit', $report->project_id) }}">
                                                {{ $report->project->nama_project ?? '-' }}
                                            </a>
                                        </td>
                                        <td rowspan="{{ $rowspan }}">{{ $report->project->nomor_spk ?? '-' }}</td>
                                    @endif
                                    <td class="text-center">{{ $report->report_ke }}</td>
                                    <td>{{ $report->period_start ? \Carbon\Carbon::parse($report->period_start)->format('d M Y') : '-' }}</td>
                                    <td>{{ $report->period_end ? \Carbon\Carbon::parse($report->period_end)->format('d M Y') : '-' }}</td>
                                    <td>
                                        <select class="form-control form-control-sm editable"
                                                data-id="{{ $report->id }}"
                                                data-field="status">
                                            <option value="open" {{ $report->status == 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="progress" {{ $report->status == 'progress' ? 'selected' : '' }}>Progress</option>
                                            <option value="submitted" {{ $report->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                            <option value="signoff" {{ $report->status == 'signoff' ? 'selected' : '' }}>Signoff</option>
                                        </select>
                                    </td>
                                    @if($isFirst)
                                        @php
                                            $latestComments = $report->project->comments->take(3);
                                        @endphp
                                        <td rowspan="{{ $rowspan }}" style="min-width: 320px; max-width: 420px;">
                                            @if($latestComments->isEmpty())
                                                <div class="text-muted small">Belum ada komentar untuk project ini.</div>
                                            @else
                                                <div class="comment-activity">
                                                    @foreach($latestComments as $comment)
                                                        <div class="media py-3 border-bottom" style="min-height: 82px;">
                                                            <div class="avatar rounded-circle bg-primary text-white mr-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-weight:600; font-size:0.95rem;">
                                                                {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                                            </div>
                                                            <div class="media-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                    <strong class="text-dark">{{ $comment->user->name ?? 'Unknown' }}</strong>
                                                                    <small class="text-muted">{{ $comment->created_at ? $comment->created_at->format('d M Y H:i') : '' }}</small>
                                                                </div>
                                                                <div class="text-sm" style="white-space: pre-wrap;">{!! nl2br(e($comment->comment)) !!}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if($report->project->comments->count() > 3)
                                                        <div class="text-right mt-2">
                                                            <small class="text-muted">Menampilkan 3 komentar terakhir dari {{ $report->project->comments->count() }}.</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-toggle="modal" data-target="#projectReportCommentsModal{{ $report->project_id }}">
                                                Komentar ({{ $report->project->comments->count() }})
                                            </button>
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        @can('monitoring-laporan-upload-laporan')
                                            @if($report->status == 'signoff')
                                                @if($report->uploaded_file)
                                                    <a href="{{ route('project.report.download', $report->id) }}" class="btn btn-sm btn-outline-primary btn-block">
                                                        Lihat File
                                                    </a>
                                                    <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm btn-success btn-block mt-2">
                                                        Ganti File
                                                    </a>
                                                @else
                                                    <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm btn-success btn-block">
                                                        Upload
                                                    </a>
                                                @endif
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data laporan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach($reports->getCollection()->groupBy('project_id') as $projectId => $projectReports)
                    @php
                        $modalReport = $projectReports->first();
                    @endphp
                    @if($modalReport)
                        <div class="modal fade" id="projectReportCommentsModal{{ $projectId }}" tabindex="-1" role="dialog" aria-labelledby="projectReportCommentsModalLabel{{ $projectId }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="projectReportCommentsModalLabel{{ $projectId }}">
                                            Komentar Project - {{ Str::limit($modalReport->project->nama_project ?? '-', 35) }}
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-4">
                                            <h6>Detail Laporan</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold mb-1">Nama Project</label>
                                                        <div>{{ $modalReport->project->nama_project ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold mb-1">Nomor SPK</label>
                                                        <div>{{ $modalReport->project->nomor_spk ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Report Ke</th>
                                                            <th>Periode Start</th>
                                                            <th>Periode End</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($projectReports as $pendingReport)
                                                            <tr>
                                                                <td>{{ $pendingReport->report_ke }}</td>
                                                                <td>{{ $pendingReport->period_start ? \Carbon\Carbon::parse($pendingReport->period_start)->format('d M Y') : '-' }}</td>
                                                                <td>{{ $pendingReport->period_end ? \Carbon\Carbon::parse($pendingReport->period_end)->format('d M Y') : '-' }}</td>
                                                                <td>{{ ucfirst($pendingReport->status) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="mb-4 project-comment-history">
                                            <h6>Riwayat Komentar</h6>
                                            @forelse($modalReport->project->comments as $comment)
                                                <div class="media mb-3">
                                                    <div class="avatar rounded-circle bg-primary text-white mr-3" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:600;">
                                                        {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong>{{ $comment->user->name ?? 'Unknown' }}</strong>
                                                                <div class="text-muted small">{{ $comment->created_at->format('d M Y H:i') }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="mt-1">{!! nl2br(e($comment->comment)) !!}</div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-comments fa-2x mb-2"></i>
                                                    <p class="mb-0">Belum ada komentar untuk project ini.</p>
                                                </div>
                                            @endforelse
                                        </div>

                                        <form action="{{ route('project.comments.store', $projectId) }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label for="comment_project_{{ $projectId }}">Komentar / Keterangan</label>
                                                <textarea id="comment_project_{{ $projectId }}" name="comment" class="form-control" rows="3" placeholder="Masukkan komentar atau alasan perubahan status" required></textarea>
                                            </div>
                                            <div class="text-right">
                                                <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Komentar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <div id="save-notification" class="save-toast">
                    <i class="fas fa-check-circle mr-2"></i> Data berhasil disimpan
                </div>

                <div class="card-footer py-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        white-space: normal !important;
        vertical-align: top;
    }

    .bg-silver {
        background-color: #f4f4f4 !important;
    }

    .project-comment-history {
        max-height: 360px;
        overflow-y: auto;
        padding-right: .5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        function showNotification() {
            var toast = $('#save-notification');
            toast.addClass('show');
            setTimeout(function() {
                toast.removeClass('show');
            }, 3000);
        }

        $('.editable').on('change', function() {
            var id = $(this).data('id');
            var field = $(this).data('field');
            var value = $(this).val();
            var element = $(this);

            element.addClass('is-loading');

            $.ajax({
                url: "{{ route('project.report.monitoring.update') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: field,
                    value: value
                },
                success: function(response) {
                    if (response.success) {
                        showNotification();
                        if (response.reload) {
                            location.reload();
                        }
                    }
                },
                error: function(xhr) {
                    alert('Error updating data');
                    console.error(xhr);
                },
                complete: function() {
                    element.removeClass('is-loading');
                }
            });
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
<style>
    .is-loading {
        opacity: 0.5;
        border-color: #5e72e4;
    }

    .save-toast {
        visibility: hidden;
        min-width: 250px;
        background-color: #2dce89;
        color: #fff;
        text-align: center;
        border-radius: 4px;
        padding: 16px;
        position: fixed;
        z-index: 9999;
        right: 30px;
        bottom: 30px;
        font-size: 14px;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
    }

    .save-toast.show {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

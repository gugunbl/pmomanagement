@extends('layouts.app')

@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            <div class="card">
                <!-- Nav tabs -->
                <div class="card-header border-0 pb-0">
                    <ul class="nav nav-tabs border-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'progress' ? 'active font-weight-bold text-primary border-bottom' : '' }}" 
                               href="{{ route('project.ba.monitoring', array_merge(request()->query(), ['tab' => 'progress'])) }}">
                               Progress BA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'open' ? 'active font-weight-bold text-primary border-bottom' : '' }}" 
                               href="{{ route('project.ba.monitoring', array_merge(request()->query(), ['tab' => 'open'])) }}">
                               BA Open
                            </a>
                        </li>
                        <li class="nav-item ml-auto align-self-center pr-3">
                            <div class="recap-summary d-flex align-items-center">
                                <div class="recap-item mr-4">
                                    <span class="recap-label text-muted">Banyak Project</span>
                                    <span class="recap-value text-primary font-weight-bold ml-1">{{ $totalProjects }}</span>
                                </div>
                                <div class="recap-item">
                                    <span class="recap-label text-muted">Banyak BA</span>
                                    <span class="recap-value text-info font-weight-bold ml-1">{{ $totalBA }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="card-header border-0 mt--2">
                    <form action="{{ route('project.ba.monitoring') }}" method="GET">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">PM Maintenance</label>
                                    <select name="pm_maintenance" class="form-control form-control-sm">
                                        <option value="">Semua PM</option>
                                        @foreach($listPM as $pm)
                                            <option value="{{ $pm->id_pm }}" {{ $pm_maintenance == $pm->id_pm ? 'selected' : '' }}>
                                                {{ $pm->nama_pm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Search Project</label>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama Project..." value="{{ $search }}">
                                </div>
                            </div>
                            <div class="col-md-6 mt-4">
                                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                                <a href="{{ route('project.ba.export.pdf', array_merge(request()->query(), ['tab' => $activeTab])) }}" class="btn btn-danger btn-sm">Export PDF</a>
                                <a href="{{ route('project.ba.export.excel', array_merge(request()->query(), ['tab' => $activeTab])) }}" class="btn btn-success btn-sm">Export Excel</a>
                                <a href="{{ route('project.ba.monitoring', ['tab' => $activeTab]) }}" class="btn btn-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="alert alert-info py-2 mt-2 mb-0" role="alert">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Kolom Signer Dinamis:</strong> Nama user diambil dari field "Nama User Tanda Tangan" di edit project (dipisahkan dengan titik koma). Checklist aktif berwarna hijau, tidak aktif berwarna kuning.
                    </div>
                </div>

                @php
                    $signerColumns = $listBA->getCollection()->pluck('project.top_maintenance_signer_total')
                        ->map(function ($value) {
                            return intval($value);
                        })
                        ->max() ?? 0;
                    $signerColumns = max(0, $signerColumns);
                    $totalColumns = 10 + $signerColumns;
                @endphp

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col" style="min-width: 140px;">Nama Project</th>
                                <th scope="col">Nomor SPK</th>
                                <th scope="col">PM Maintenance</th>
                                <th scope="col">TOP Ke</th>
                                <th scope="col">Start Date BA</th>
                                <th scope="col">End Date BA</th>
                                <th scope="col" style="width: 140px;">Status</th>
                                @for ($i = 1; $i <= $signerColumns; $i++)
                                    <th scope="col">User {{ $i }}</th>
                                @endfor
                                <th scope="col">Keterangan</th>
                                <th scope="col" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @php 
                                $projectCounts = $listBA->getCollection()->groupBy('project_id')->map->count();
                                $projectCounter = $startProjectCounter ?? 0;
                                $prevProject = null;
                                $isSilver = false;
                                $signerColumns = $listBA->getCollection()->pluck('project.top_maintenance_signer_total')->max() ?? 0;
                                $totalColumns = 10 + $signerColumns;
                            @endphp
                            @forelse($listBA as $ba)
                                @php
                                    $isFirst = false;
                                    // Toggle silver background when project changes
                                    if ($ba->project_id !== $prevProject) {
                                        $isSilver = !$isSilver;
                                        $prevProject = $ba->project_id;
                                        $isFirst = true;
                                        $projectCounter++;
                                    }

                                    $highlight = '';
                                    if ($activeTab == 'progress' && $ba->periode_end) {
                                        $endDate = \Carbon\Carbon::parse($ba->periode_end);
                                        $threeMonthsAgo = \Carbon\Carbon::now()->subMonths(3);
                                        $today = \Carbon\Carbon::now();

                                        if ($endDate->lt($threeMonthsAgo)) {
                                            $highlight = 'bg-danger-dark'; 
                                        } elseif ($endDate->lte($today)) {
                                            $highlight = 'bg-warning-custom';
                                        }
                                    }
                                    
                                    $rowspan = $projectCounts[$ba->project_id] ?? 1;
                                    $signers = json_decode($ba->signers ?? '[]', true) ?: [];
                                    $teamLeadDone = in_array(1, $signers);
                                    $deptHeadDone = in_array(2, $signers);
                                    $noteText = $ba->comments->first()->comment ?? ($ba->keterangan ?? '-');
                                @endphp
                                <tr class="{{ $isSilver ? 'bg-silver' : '' }}">
                                    @if($isFirst)
                                        <td rowspan="{{ $rowspan }}">{{ $projectCounter }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-wrap font-weight-bold" style="min-width: 150px; font-size: 1.05rem;">
                                            <a href="{{ route('project.edit', $ba->project_id) }}">
                                                {{ $ba->project->nama_project ?? '-' }}
                                            </a>
                                        </td>
                                        <td rowspan="{{ $rowspan }}">{{ $ba->project->nomor_spk ?? '-' }}</td>
                                        <td rowspan="{{ $rowspan }}" class="font-weight-bold" style="font-size: 1.05rem;">{{ $ba->project->mPMMaintenance->nama_pm ?? '-' }}</td>
                                    @endif
                                    <td class="text-center">{{ $ba->top_ke }}</td>
                                    <td class="{{ $highlight }}">{{ $ba->periode_start ? \Carbon\Carbon::parse($ba->periode_start)->format('d M Y') : '-' }}</td>
                                    <td class="{{ $highlight }}">{{ $ba->periode_end ? \Carbon\Carbon::parse($ba->periode_end)->format('d M Y') : '-' }}</td>
                                    <td style="width: 140px;">
                                        <select class="form-control form-control-sm editable" data-id="{{ $ba->id }}" data-field="status">
                                            <option value="planned" {{ $ba->status == 'planned' ? 'selected' : '' }}>Planned</option>
                                            <option value="submitted" {{ $ba->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                            <option value="revision" {{ $ba->status == 'revision' ? 'selected' : '' }}>Revision</option>
                                            <option value="paid" {{ $ba->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        </select>
                                    </td>
                                    @php
                                        $projectSignerTotal = intval($ba->project->top_maintenance_signer_total ?? 0);
                                        $selectedSigners = json_decode($ba->signers ?? '[]', true) ?: [];
                                        $isPaid = $ba->status === 'paid';
                                        $signerLabels = [];
                                        if (!empty($ba->project->top_maintenance_notes)) {
                                            $signerLabels = array_map('trim', explode(';', $ba->project->top_maintenance_notes));
                                        }
                                    @endphp
                                    @for ($signerIndex = 1; $signerIndex <= $signerColumns; $signerIndex++)
                                        @if ($signerIndex <= $projectSignerTotal)
                                            @php
                                                $checked = in_array($signerIndex, $selectedSigners);
                                                $cellClass = $checked ? 'bg-success text-white' : 'bg-warning text-dark';
                                            @endphp
                                            <td class="text-center {{ $cellClass }}">
                                                <div class="custom-control custom-checkbox d-inline-block" style="vertical-align: middle;">
                                                    <input type="checkbox"
                                                           class="custom-control-input project-signer-checkbox"
                                                           id="signer_checkbox_{{ $ba->id }}_{{ $signerIndex }}"
                                                           data-ba-id="{{ $ba->id }}"
                                                           data-signer-index="{{ $signerIndex }}"
                                                           {{ $checked ? 'checked' : '' }}
                                                           {{ $isPaid ? 'disabled' : '' }}
                                                           value="1">
                                                    <label class="custom-control-label" for="signer_checkbox_{{ $ba->id }}_{{ $signerIndex }}"></label>
                                                </div>
                                                <div style="font-size: 0.8rem; margin-top: 0.2rem;">
                                                    {{ $signerLabels[$signerIndex - 1] ?? 'User ' . $signerIndex }}
                                                </div>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endfor
                                    @if($isFirst)
                                        @php
                                            $latestComments = $ba->project->comments->take(3);
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
                                                    @if($ba->project->comments->count() > 3)
                                                        <div class="text-right mt-2">
                                                            <small class="text-muted">Menampilkan 3 komentar terakhir dari {{ $ba->project->comments->count() }}.</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-toggle="modal" data-target="#projectCommentsModal{{ $ba->project_id }}">
                                                Komentar ({{ $ba->project->comments->count() }})
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $totalColumns }}" class="text-center">Tidak ada data BA di tab ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach($listBA->getCollection()->groupBy('project_id') as $projectId => $projectPendingBAs)
                    @php
                        $modalBA = $projectPendingBAs->first();
                    @endphp
                    @if($modalBA)
                        <div class="modal fade" id="projectCommentsModal{{ $projectId }}" tabindex="-1" role="dialog" aria-labelledby="projectCommentsModalLabel{{ $projectId }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="projectCommentsModalLabel{{ $projectId }}">
                                            Komentar Project - {{ Str::limit($modalBA->project->nama_project ?? '-', 35) }}
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-4">
                                            <h6>Detail TOP</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold mb-1">Nama Project</label>
                                                        <div>{{ $modalBA->project->nama_project ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold mb-1">Nomor SPK</label>
                                                        <div>{{ $modalBA->project->nomor_spk ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="font-weight-bold mb-1">PM Maintenance</label>
                                                        <div>{{ $modalBA->project->mPMMaintenance->nama_pm ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>TOP Ke</th>
                                                            <th>Start Date BA</th>
                                                            <th>End Date BA</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($projectPendingBAs as $pendingBA)
                                                            <tr>
                                                                <td>{{ $pendingBA->top_ke }}</td>
                                                                <td>{{ $pendingBA->periode_start ? \Carbon\Carbon::parse($pendingBA->periode_start)->format('d M Y') : '-' }}</td>
                                                                <td>{{ $pendingBA->periode_end ? \Carbon\Carbon::parse($pendingBA->periode_end)->format('d M Y') : '-' }}</td>
                                                                <td>{{ ucfirst($pendingBA->status) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="mb-4 project-comment-history">
                                            <h6>Riwayat Komentar</h6>
                                            @forelse($modalBA->project->comments as $comment)
                                                <div class="media mb-3">
                                                    <div class="avatar rounded-circle bg-primary text-white mr-3" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:600;">
                                                        {{ strtoupper(substr($comment->user->name ?? 'U',0,1)) }}
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
                                                <textarea id="comment_project_{{ $projectId }}" name="comment" class="form-control" rows="3" placeholder="Masukkan komentar atau alasan perubahan status"></textarea>
                                            </div>
                                            <input type="hidden" name="tab" value="{{ $activeTab }}">
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
                    {{ $listBA->links() }}
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
    
    .nav-tabs .nav-link {
        border: none;
        color: #8898aa;
    }
    
    .nav-tabs .nav-link.active {
        background-color: transparent;
        color: #5e72e4;
        border-bottom: 3px solid #5e72e4 !important;
    }

    /* Merah Gelap untuk > 3 bulan overdue */
    .bg-danger-dark {
        background-color: #8B0000 !important;
        color: white !important;
    }
    
    /* Kuning untuk <= 3 bulan overdue */
    .bg-warning-custom {
        background-color: #FFFF00 !important;
        color: black !important;
    }

    .bg-silver {
        background-color: #f4f4f4 !important; /* Silver/Light Gray */
    }

    .recap-label {
        font-size: 0.9rem; /* Same as progress BA / standard nav-link */
        text-transform: uppercase;
        letter-spacing: .025em;
    }

    .recap-value {
        font-size: 1.8rem; /* 2x the label (approx) */
        line-height: 1;
        vertical-align: middle;
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
                url: '/project/monitoring-ba/' + id + '/update',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: value,
                    field: field
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

        $(document).on('click', '.project-comment-open', function() {
            var button = $(this);
            var projectId = button.data('project-id');
            var projectName = button.data('project-name');
            var spk = button.data('spk');
            var pm = button.data('pm');
            var topKe = button.data('top-ke');
            var startDate = button.data('start-date');
            var endDate = button.data('end-date');
            var status = button.data('status');

            $('#projectCommentProjectName' + projectId).text(projectName);
            $('#projectCommentSpk' + projectId).text(spk);
            $('#projectCommentPm' + projectId).text(pm);
            $('#projectCommentTopKe' + projectId).text(topKe);
            $('#projectCommentStartDate' + projectId).text(startDate);
            $('#projectCommentEndDate' + projectId).text(endDate);
            $('#projectCommentStatus' + projectId).text(status);
        });

        $(document).on('change', '.project-signer-checkbox', function() {
            var checkbox = $(this);
            var baId = checkbox.data('ba-id');
            var row = checkbox.closest('tr');
            var selected = [];

            row.find('.project-signer-checkbox:checked').each(function() {
                selected.push($(this).data('signer-index'));
            });

            $.ajax({
                url: '/project/monitoring-ba/' + baId + '/signers',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    signers: selected
                },
                success: function(response) {
                    if (response.success) {
                        if (response.reload) {
                            location.reload();
                            return;
                        }

                        row.find('.project-signer-checkbox').each(function() {
                            var cell = $(this).closest('td');
                            if ($(this).is(':checked')) {
                                cell.removeClass('bg-warning text-dark').addClass('bg-success text-white');
                            } else {
                                cell.removeClass('bg-success text-white').addClass('bg-warning text-dark');
                            }
                        });
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                }
            });
        });
    });
</script>
<style>
    .is-loading { opacity: 0.5; border-color: #5e72e4; }
    .save-toast { visibility: hidden; min-width: 250px; background-color: #2dce89; color: #fff; text-align: center; border-radius: 4px; padding: 16px; position: fixed; z-index: 9999; right: 30px; bottom: 30px; font-size: 14px; box-shadow: 0 4px 6px rgba(50,50,93,0.11), 0 1px 3px rgba(0,0,0,0.08); transition: all 0.3s ease; opacity: 0; transform: translateY(20px); }
    .save-toast.show { visibility: visible; opacity: 1; transform: translateY(0); }
</style>
@endpush

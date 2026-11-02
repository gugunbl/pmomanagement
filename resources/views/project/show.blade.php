@extends('layouts.app')
@push('pg_btn')
    @if(Auth::check() && Auth::user()->canEditProject($project))
        <a class="btn btn-info btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Edit Project details" href="{{route('project.edit',$project)}}">
            <i class="fa fa-edit" aria-hidden="true"></i> Edit project
        </a>
    @endif
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <h4 class="mb-0">Informasi Detail Project</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Title</div>
                        <div class="col-sm-10"><strong>{{ $project->nama_project }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Tahun Project</div>
                        <div class="col-sm-4"><strong>{{ $project->tahun }}</strong></div>
                        <div class="col-sm-2 text-muted">Tanggal SPK</div>
                        <div class="col-sm-4"><strong>{{ $project->tanggal_spk }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Nomor SPK</div>
                        <div class="col-sm-4"><strong>{{ $project->nomor_spk }}</strong></div>
                        <div class="col-sm-2 text-muted">Customer</div>
                        <div class="col-sm-4"><strong>{{ $project->customer }}</strong></div>
                    </div>
                    @if($project->link_dokumen)
                        <div class="row mb-3">
                            <div class="col-sm-2 text-muted">Link Dokumen</div>
                            <div class="col-sm-10">
                                @if($project->link_dokumen_url)
                                    <a href="{{ $project->link_dokumen_url }}" target="_blank" rel="noopener noreferrer">
                                        {{ $project->link_dokumen }}
                                    </a>
                                @else
                                    <strong>{{ $project->link_dokumen }}</strong>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Category</div>
                        <div class="col-sm-4"><strong>{{ $project->mcategory->category_name }}</strong></div>
                        <div class="col-sm-2 text-muted">Team Support</div>
                        <div class="col-sm-4">
                            @forelse($project->teamSupports as $ts)
                                <span class="badge badge-pill badge-info mr-1">{{ $ts->name }}</span>
                            @empty
                                <strong>-</strong>
                            @endforelse
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Status Implementasi</div>
                        <div class="col-sm-4"><strong>{{ $project->statusImplementation->nama_status }}</strong></div>
                        <div class="col-sm-2 text-muted">PM Implementasi</div>
                        <div class="col-sm-4"><strong>{{ $project->mPMImplementasi->nama_pm }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Status Maintenance</div>
                        <div class="col-sm-4"><strong>{{ $project->statusMaintenance->nama_status }}</strong></div>
                        <div class="col-sm-2 text-muted">PM Maintenance</div>
                        <div class="col-sm-4"><strong>{{ $project->mPMMaintenance->nama_pm }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 text-muted">Tipe Maintenance</div>
                        <div class="col-sm-4"><strong>{{ $project->tipe_maintenance ?? '-' }}</strong></div>
                        @if(Auth::check())
                            @php $userCanEditProject = Auth::user()->canEditProject($project); @endphp
                            <div class="col-sm-2 text-muted">Hak Edit</div>
                            <div class="col-sm-4">
                                <strong class="{{ $userCanEditProject ? 'text-success' : 'text-muted' }}">
                                    {{ $userCanEditProject ? 'Ya, Anda dapat edit project ini' : 'Tidak, hanya view saja' }}
                                </strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Laporan Project</h4>
                </div>
                <div class="card-body">
                    @if($project->reports->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <p>Tidak ada laporan yang tersimpan untuk project ini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Laporan Ke</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th>Keterangan / Komentar</th>
                                        <th style="width: 140px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->reports as $report)
                                        <tr>
                                            <td>{{ $report->report_ke }}</td>
                                            <td>{{ $report->period_start ?? '-' }}<br>{{ $report->period_end ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-pill badge-{{ $report->status == 'signoff' ? 'success' : ($report->status == 'submitted' ? 'info' : ($report->status == 'open' ? 'secondary' : 'warning')) }}">
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($report->comments->isNotEmpty())
                                                    <div class="small text-dark mb-1">{{ Str::limit($report->comments->first()->comment, 120) }}</div>
                                                    <div class="text-muted small">{{ $report->comments->first()->user->name ?? 'Unknown' }} • {{ $report->comments->first()->created_at->format('d M Y H:i') }}</div>
                                                @else
                                                    <span class="text-muted">{{ Str::limit($report->keterangan ?? '-', 120) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary mb-2 btn-block" data-toggle="modal" data-target="#reportDetailsModal{{ $report->id }}">
                                                    Komentar
                                                </button>

                                                @can('monitoring-laporan-upload-laporan')
                                                    @if($report->status === 'signoff')
                                                        @if($report->uploaded_file)
                                                            <a href="{{ route('project.report.download', $report->id) }}" class="btn btn-sm btn-outline-primary btn-block">
                                                                Lihat File
                                                            </a>
                                                            <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm btn-success btn-block">
                                                                Ganti File
                                                            </a>
                                                        @else
                                                            <a href="{{ route('project.report.upload.form', $report->id) }}" class="btn btn-sm btn-success btn-block">
                                                                Upload
                                                            </a>
                                                        @endif
                                                    @else
                                                        <button class="btn btn-sm btn-secondary btn-block" disabled>Tidak tersedia</button>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">No access</span>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @foreach($project->reports as $report)
                            <div class="modal fade" id="reportDetailsModal{{ $report->id }}" tabindex="-1" role="dialog" aria-labelledby="reportDetailsModalLabel{{ $report->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="reportDetailsModalLabel{{ $report->id }}">
                                                Laporan #{{ $report->report_ke }} - {{ Str::limit($project->nama_project, 35) }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Periode:</strong> {{ $report->period_start ?? '-' }} sampai {{ $report->period_end ?? '-' }}
                                            </div>
                                            <div class="mb-3">
                                                <strong>Status:</strong>
                                                <span class="badge badge-{{ $report->status == 'signoff' ? 'success' : ($report->status == 'submitted' ? 'info' : ($report->status == 'open' ? 'secondary' : 'warning')) }}">
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                            </div>
                                            <div class="mb-4">
                                                <strong>Keterangan awal:</strong>
                                                <p class="mb-0">{{ $report->keterangan ?? '-' }}</p>
                                            </div>

                                            <div class="mb-4">
                                                <h6>Riwayat Komentar</h6>
                                                @forelse($report->comments as $comment)
                                                    <div class="card mb-2">
                                                        <div class="card-body py-2">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <div>
                                                                    <strong>{{ $comment->user->name ?? 'Unknown' }}</strong>
                                                                    <span class="text-muted small">• {{ $comment->created_at->format('d M Y H:i') }}</span>
                                                                </div>
                                                            </div>
                                                            <div>{!! nl2br(e($comment->comment)) !!}</div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center text-muted py-3">
                                                        <p class="mb-0">Belum ada komentar.</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div class="mb-4">
                                                <h6>Riwayat Status</h6>
                                                @forelse($report->statusLogs as $log)
                                                    <div class="card mb-2">
                                                        <div class="card-body py-2">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <div>
                                                                    <strong>{{ $log->user->name ?? 'Unknown' }}</strong>
                                                                    <span class="text-muted small">• {{ $log->created_at->format('d M Y H:i') }}</span>
                                                                </div>
                                                                <span class="badge badge-pill badge-info">{{ ucfirst($log->new_status) }}</span>
                                                            </div>
                                                            <div class="small text-muted">Dari: {{ ucfirst($log->old_status ?? '-') }}</div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center text-muted py-3">
                                                        <p class="mb-0">Belum ada perubahan status.</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <form action="{{ route('project.report.comments.store', $report->id) }}" method="POST">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="comment_{{ $report->id }}">Tambah Komentar</label>
                                                    <textarea id="comment_{{ $report->id }}" name="comment" class="form-control" rows="3" required></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Simpan Komentar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
@endsection

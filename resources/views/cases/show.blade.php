@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">{{ $title }}</h3>
                    </div>
                    <div class="col text-right">
                        <a href="{{ route('cases.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                        <a href="{{ route('cases.edit', $case->id_case) }}" class="btn btn-sm btn-warning">Edit</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="heading-small text-muted mb-4">Informasi User & Project</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            <p class="form-control-static">{{ optional($case->project)->nama_project ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">User Input</label>
                            <p class="form-control-static">{{ optional($case->user)->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Open Case</label>
                            <p class="form-control-static">{{ date('d-m-Y H:i', strtotime($case->open_date)) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Closed Case</label>
                            <p class="form-control-static">{{ $case->closed_date ? date('d-m-Y H:i', strtotime($case->closed_date)) : '-' }}</p>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Detail Case</h6>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Subject Case</label>
                            <p class="form-control-static">{{ $case->subject }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Perangkat Terdampak (SN)</label>
                            <p class="form-control-static">{{ $case->affected_sn ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Lokasi Perangkat</label>
                            <p class="form-control-static">{{ $case->location ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Case</label>
                            <div class="p-3 bg-secondary rounded">
                                {!! nl2br(e($case->detail)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Severity</label>
                            <p class="form-control-static">
                                <span class="badge badge-dot mr-4">
                                    @if($case->severity == 'Minor')
                                        <i class="bg-success"></i>
                                    @elseif($case->severity == 'Major')
                                        <i class="bg-warning"></i>
                                    @elseif($case->severity == 'Critical')
                                        <i class="bg-danger"></i>
                                    @else
                                        <i class="bg-info"></i>
                                    @endif
                                    <span class="status">{{ $case->severity }}</span>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Status</label>
                            <p class="form-control-static">
                                <span class="badge badge-dot mr-4">
                                    @if($case->status == 'Open')
                                        <i class="bg-danger"></i>
                                    @elseif($case->status == 'In Progress')
                                        <i class="bg-warning"></i>
                                    @elseif($case->status == 'Closed')
                                        <i class="bg-success"></i>
                                    @endif
                                    <span class="status">{{ $case->status }}</span>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No. Tiket Prinsipal</label>
                            <p class="form-control-static">{{ $case->principal_ticket_no ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Penyelesaian</label>
                            <div class="p-3 bg-secondary rounded">
                                {!! nl2br(e($case->resolution_detail ?? '-')) !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

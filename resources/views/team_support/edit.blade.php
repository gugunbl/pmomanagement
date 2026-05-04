@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <h3 class="mb-0">Edit Team Support</h3>
                </div>
                <div class="card-body">
                    {!! Form::model($teamSupport, ['route' => ['team-support.update', $teamSupport->id], 'method' => 'PUT']) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('name', 'Nama Team') }}
                                    {{ Form::text('name', null, ['class' => 'form-control', 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('status', 'Status') }}
                                    {{ Form::select('status', [1 => 'Aktif', 0 => 'Nonaktif'], null, ['class' => 'form-control']) }}
                                </div>
                            </div>
                        </div>

                        <div class="text-right mb-4">
                            <a href="{{ route('team-support.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    {!! Form::close() !!}

                    <hr class="my-4" />
                    <h6 class="heading-small text-muted mb-4">Project yang Terelasi</h6>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama Project</th>
                                    <th>Tahun</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teamSupport->projects as $project)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $project->nama_project }}</span>
                                    </td>
                                    <td>{{ $project->tahun }}</td>
                                    <td>
                                        @if($project->status_maintenance)
                                            <span class="badge badge-success">Maintenance</span>
                                        @else
                                            <span class="badge badge-info">Implementation</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('project.show', $project->id_project) }}" class="btn btn-sm btn-outline-primary">Detail Project</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <span class="text-muted">Tidak ada project yang terelasi dengan team ini.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

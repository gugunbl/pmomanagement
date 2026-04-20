@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <h3 class="mb-0">Edit Team Support</h3>
                </div>
                <div class="card-body">
                    {!! Form::model($teamSupport, ['route' => ['team-support.update', $teamSupport->id], 'method' => 'PUT']) !!}

                        <div class="form-group">
                            {{ Form::label('name', 'Nama Team') }}
                            {{ Form::text('name', null, ['class' => 'form-control', 'required']) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('status', 'Status') }}
                            {{ Form::select('status', [1 => 'Aktif', 0 => 'Nonaktif'], null, ['class' => 'form-control']) }}
                        </div>

                        <div class="text-right">
                            <a href="{{ route('team-support.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

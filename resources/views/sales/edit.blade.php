@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Edit Sales</h3>
                </div>
                <div class="card-body">
                    {!! Form::model($sales, ['route' => ['sales.update', $sales->id], 'method' => 'PUT']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('name', 'Sales Name') }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Enter sales name', 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('status', 'Status') }}
                                {{ Form::select('status', [1 => 'Active', 0 => 'Disabled'], null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            {{ Form::submit('Update Sales', ['class' => 'btn btn-primary']) }}
                            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Create Customer</h3>
                </div>
                <div class="card-body">
                    {!! Form::open(['route' => 'customer.store']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('name', 'Customer Name') }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Enter customer name', 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('status', 'Status') }}
                                {{ Form::select('status', [1 => 'Active', 0 => 'Disabled'], 1, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            {{ Form::submit('Save Customer', ['class' => 'btn btn-primary']) }}
                            <a href="{{ route('customer.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

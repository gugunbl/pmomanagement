@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card mb-5">
            <div class="card-header bg-transparent">
                <h3 class="mb-0">Tambah Daily Monitoring</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('daily-monitoring.store') }}" method="POST" enctype="multipart/form-data" class="daily-monitoring-form">
                    @include('daily_monitoring._form', ['monitoring' => $monitoring])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

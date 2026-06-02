@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card mb-5">
            <div class="card-header bg-transparent">
                <h3 class="mb-0">Edit Daily Monitoring</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('daily-monitoring.update', $daily_monitoring->id) }}" method="POST" enctype="multipart/form-data" class="daily-monitoring-form">
                    @method('PUT')
                    @include('daily_monitoring._form', ['monitoring' => $daily_monitoring])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

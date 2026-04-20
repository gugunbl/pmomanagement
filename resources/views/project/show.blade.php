@extends('layouts.app')
@push('pg_btn')
    @can('update-project')
        <a class="btn btn-info btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Edit Project details" href="{{route('project.edit',$project)}}">
            <i class="fa fa-edit" aria-hidden="true"></i> Edit project
        </a>
    @endcan
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-1">
                            Title
                        </div>
                        <div class="col-sm-10">
                            <strong>{{ $project->nama_project }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Tahun Project
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->tahun }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Tanggal SPK
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->tanggal_spk }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Nomor SPK
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->nomor_spk }}</strong>
                        </div>
                    </div>
                    <h1></h1>   
                    <div class="row">
                        <div class="col-sm-1">
                            Customer
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->customer }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Category
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->mcategory->category_name }}</strong>
                        </div>
                    </div>
                    <p><p>
                    <div class="row">
                        <div class="col-sm-1">
                            Status Implementasi
                        </div>
                        <div class="col-sm-3">
                            <strong> {{ $project->statusImplementation->nama_status }} </strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            PM Implementasi
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->mPMImplementasi->nama_pm }}</strong>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-1">
                            Status Maintenance
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->statusMaintenance->nama_status }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            PM Maintenance
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->mPMMaintenance->nama_pm }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Tipe Maintenance
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $project->tipe_maintenance ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Team Support
                        </div>
                        <div class="col-sm-9">
                            @forelse($project->teamSupports as $ts)
                                <span class="badge badge-pill badge-info mr-1">{{ $ts->name }}</span>
                            @empty
                                <strong>-</strong>
                            @endforelse
                        </div>
                    </div>
                    

 
                </div>
            </div>
        </div>
    </div>
@endsection

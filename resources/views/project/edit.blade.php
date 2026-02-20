@extends('layouts.app')

@section('content')

<div class="card">

    <div class="card-header p-0">
        <ul class="nav nav-pills nav-fill" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#info">
                    Informasi Project
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#top-implementasi">
                    TOP Implementasi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#top-maintenance">
                    TOP Maintenance
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#laporan-project">
                    Laporan Project
                </a>
            </li>

        </ul>
    </div>

    {!! Form::model($project, ['route' => ['project.update', $project->id_project], 'method' => 'PUT']) !!}

    <div class="card-body">
        <div class="tab-content">

            <div class="tab-pane fade show active" id="info">
                @include('project.partials.form-project-info')
            </div>

            <div class="tab-pane fade" id="top-implementasi">
                @include('project.partials.form-top-implementasi')
            </div>

            <div class="tab-pane fade" id="top-maintenance">
                @include('project.partials.form-top-maintenance')
            </div>

            <div class="tab-pane fade" id="laporan-project">
                @include('project.partials.form-laporan-project')
            </div>

        </div>

        <div class="text-right mt-4">
            <a href="{{ route('project.maintenance') }}" class="btn btn-secondary">
                Kembali ke List Project Maintenance
            </a>
            <button type="submit" class="btn btn-primary">
                Update Project 
            </button>

        </div>

    </div>

    {!! Form::close() !!}
</div>

@endsection

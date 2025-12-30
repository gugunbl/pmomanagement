@extends('layouts.app')

@section('content')

<div class="card">

    <div class="card-header p-0">
        <ul class="nav nav-pills nav-fill" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#info">
                    Informasi Project
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#top-implementasi">
                    TOP Implementasi
                </a>
            </li>
        </ul>
    </div>

    {!! Form::open(['route' => 'project.store']) !!}

    <div class="card-body">
        <div class="tab-content">

            <div class="tab-pane fade show active" id="info">
                @include('project.partials.form-project-info')
            </div>

            <div class="tab-pane fade" id="top-implementasi">
                @include('project.partials.form-top-implementasi')
            </div>

        </div>

        <div class="text-right mt-4">
            <button class="btn btn-primary">
                Simpan Project
            </button>
        </div>
    </div>

    {!! Form::close() !!}
</div>

@endsection

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

    {!! Form::model($project, ['route' => 'project.store']) !!}

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

@push('scripts')
<script>
    $(document).ready(function() {
        function toggleTipeMaintenance() {
            var statusMa = $('#status_maintenance').val();
            if (statusMa == '1') {
                $('#tipe_maintenance').prop('disabled', false);
                $('.ts-checkbox').prop('disabled', false);
                $('#btn-select-all-ts, #btn-deselect-all-ts').removeClass('disabled').css('pointer-events', '');
            } else {
                $('#tipe_maintenance').prop('disabled', true).val('');
                $('.ts-checkbox').prop('disabled', true).prop('checked', false);
                $('#btn-select-all-ts, #btn-deselect-all-ts').addClass('disabled').css('pointer-events', 'none');
            }
        }

        $('#status_maintenance').on('change', function() {
            toggleTipeMaintenance();
        });

        // Initialize on load
        toggleTipeMaintenance();

        // Team Support: Select All / Deselect All
        $('#btn-select-all-ts').on('click', function(e) {
            e.preventDefault();
            $('.ts-checkbox').prop('checked', true);
        });
        $('#btn-deselect-all-ts').on('click', function(e) {
            e.preventDefault();
            $('.ts-checkbox').prop('checked', false);
        });
    });
</script>
@endpush

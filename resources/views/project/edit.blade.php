@extends('layouts.app')

@section('body_class', 'project-workspace-body')

@section('content')
<div class="project-workspace">
    <div class="project-intro-card project-intro-card--edit">
        <div class="project-intro-icon" aria-hidden="true"><i class="fas fa-tools"></i></div>
        <div class="project-intro-copy">
            <span class="project-kicker">Maintenance workspace</span>
            <h2>{{ $project->nama_project }}</h2>
            <p>Perbarui detail project, termin, dan laporan maintenance dalam satu workspace terstruktur.</p>
        </div>
        <div class="project-intro-status"><i class="fas fa-hashtag"></i> {{ $project->nomor_spk ?: 'Tanpa SPK' }}</div>
    </div>

    <div class="card project-form-shell">
        <div class="card-header project-tabs-header">
            <ul class="nav nav-pills project-step-tabs project-step-tabs--four" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info" role="tab" aria-selected="true"><span class="project-step-number">01</span><span><strong>Informasi</strong><small>Data utama project</small></span></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#top-implementasi" role="tab" aria-selected="false"><span class="project-step-number">02</span><span><strong>TOP Implementasi</strong><small>Termin implementasi</small></span></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#top-maintenance" role="tab" aria-selected="false"><span class="project-step-number">03</span><span><strong>TOP Maintenance</strong><small>Termin maintenance</small></span></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#laporan-project" role="tab" aria-selected="false"><span class="project-step-number">04</span><span><strong>Laporan</strong><small>Dokumen bulanan</small></span></a></li>
            </ul>
        </div>

        {!! Form::model($project, ['route' => ['project.update', $project->id_project], 'method' => 'PUT']) !!}
        <div class="card-body project-form-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="info" role="tabpanel">@include('project.partials.form-project-info')</div>
                <div class="tab-pane fade" id="top-implementasi" role="tabpanel">@include('project.partials.form-top-implementasi')</div>
                <div class="tab-pane fade" id="top-maintenance" role="tabpanel">@include('project.partials.form-top-maintenance')</div>
                <div class="tab-pane fade" id="laporan-project" role="tabpanel">@include('project.partials.form-laporan-project')</div>
            </div>

            <div class="project-form-actions project-form-actions--wrap">
                <a href="{{ route('project.maintenance') }}" class="btn project-btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <div class="project-action-group">
                    <a href="{{ route('perangkat.index', ['id_project' => $project->id_project]) }}" class="btn project-btn-info"><i class="fas fa-boxes"></i> {{ $project->perangkats->count() }} Perangkat</a>
                    <button type="submit" class="btn btn-primary project-btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
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

        $('#status_maintenance').on('change', toggleTipeMaintenance);
        toggleTipeMaintenance();
        $('#btn-select-all-ts').on('click', function(e) { e.preventDefault(); $('.ts-checkbox').prop('checked', true); });
        $('#btn-deselect-all-ts').on('click', function(e) { e.preventDefault(); $('.ts-checkbox').prop('checked', false); });
    });
</script>
@endpush

@extends('layouts.app')

@section('body_class', 'project-workspace-body')

@section('content')
<div class="project-workspace">
    <div class="project-intro-card">
        <div class="project-intro-icon" aria-hidden="true"><i class="fas fa-folder-plus"></i></div>
        <div class="project-intro-copy">
            <span class="project-kicker">Project workspace</span>
            <h2>Buat project baru</h2>
            <p>Lengkapi informasi utama dan termin implementasi. Data dapat diperbarui kembali setelah project dibuat.</p>
        </div>
        <div class="project-intro-status"><i class="fas fa-shield-alt"></i> Draft baru</div>
    </div>

    <div class="card project-form-shell">
        <div class="card-header project-tabs-header">
            <ul class="nav nav-pills project-step-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="pill" href="#info" role="tab" aria-controls="info" aria-selected="true">
                        <span class="project-step-number">01</span>
                        <span><strong>Informasi Project</strong><small>Identitas, periode, dan PIC</small></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="pill" href="#top-implementasi" role="tab" aria-controls="top-implementasi" aria-selected="false">
                        <span class="project-step-number">02</span>
                        <span><strong>TOP Implementasi</strong><small>Termin dan status pembayaran</small></span>
                    </a>
                </li>
            </ul>
        </div>

        {!! Form::model($project, ['route' => 'project.store', 'id' => 'project-create-form']) !!}
        <div class="card-body project-form-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    @include('project.partials.form-project-info')
                </div>
                <div class="tab-pane fade" id="top-implementasi" role="tabpanel">
                    @include('project.partials.form-top-implementasi')
                </div>
            </div>

            <div class="project-form-actions">
                <a href="{{ route('project.index') }}" class="btn project-btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
                <button type="submit" id="project-save-button" class="btn btn-primary project-btn-primary">
                    <i class="fas fa-save project-save-icon"></i>
                    <span class="project-save-label">Simpan Project</span>
                </button>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<div id="project-save-loading" class="project-save-loading" role="status" aria-live="assertive" aria-hidden="true">
    <div class="project-save-loading-card">
        <span class="project-save-loading-spinner" aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>
        <div>
            <strong>Menyimpan project…</strong>
            <p>Data project sedang disimpan dan folder Nextcloud sedang disiapkan. Mohon tunggu.</p>
        </div>
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

        var $projectForm = $('#project-create-form');
        var $saveButton = $('#project-save-button');
        var $loadingNotice = $('#project-save-loading');

        $projectForm.on('submit', function(event) {
            if (this.checkValidity && !this.checkValidity()) {
                return;
            }

            if ($projectForm.data('submitting')) {
                event.preventDefault();
                return;
            }

            $projectForm.data('submitting', true);
            $saveButton
                .prop('disabled', true)
                .attr('aria-busy', 'true')
                .find('.project-save-icon')
                .removeClass('fa-save')
                .addClass('fa-spinner fa-spin');
            $saveButton.find('.project-save-label').text('Menyimpan project…');
            $loadingNotice.addClass('is-visible').attr('aria-hidden', 'false');
            $('body').addClass('project-save-in-progress');
        });

        $(window).on('pageshow', function(event) {
            if (!event.originalEvent || !event.originalEvent.persisted) {
                return;
            }

            $projectForm.removeData('submitting');
            $saveButton.prop('disabled', false).removeAttr('aria-busy');
            $saveButton.find('.project-save-icon').removeClass('fa-spinner fa-spin').addClass('fa-save');
            $saveButton.find('.project-save-label').text('Simpan Project');
            $loadingNotice.removeClass('is-visible').attr('aria-hidden', 'true');
            $('body').removeClass('project-save-in-progress');
        });
    });
</script>
@endpush

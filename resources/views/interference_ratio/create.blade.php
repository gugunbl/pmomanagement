@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">{{ $title }}</h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'interference-ratio.store', 'method' => 'POST', 'id' => 'formRatio']) !!}
                
                <h6 class="heading-small text-muted mb-4">Informasi Dasar & Perangkat</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            @if($fixedProject)
                                {{ Form::select('id_project', $projects, null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'project_select']) }}
                                <small class="text-info">Project ini sudah ditentukan untuk akun Anda.</small>
                            @else
                                {{ Form::select('id_project', ['' => 'Pilih Project...'] + $projects->toArray(), null, ['class' => 'form-control select2', 'id' => 'project_select']) }}
                            @endif
                            @error('id_project') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
                            @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Waktu Monitor</label>
                            @php
                                $waktu_list = ['' => 'Pilih Waktu...'];
                                for ($i = 6; $i <= 23; $i++) {
                                    $h = str_pad($i, 2, '0', STR_PAD_LEFT) . '.00 WIB';
                                    $waktu_list[$h] = $h;
                                }
                            @endphp
                            {{ Form::select('waktu', $waktu_list, null, ['class' => 'form-control', 'required']) }}
                            @error('waktu') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label class="form-control-label d-block">AP Name (Pilih Perangkat)</label>
                            {{ Form::select('ap_name', ['' => 'Pilih AP Name...'], null, ['class' => 'form-control select2', 'id' => 'ap_name_select', 'required']) }}
                            @error('ap_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Interference Data</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-secondary shadow-none border">
                            <div class="card-body">
                                <h5 class="card-title text-center">CHANNEL</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-0">
                                            <label class="form-control-label text-xs">Before</label>
                                            <input type="number" name="channel_before" class="form-control" required>
                                            @error('channel_before') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-0">
                                            <label class="form-control-label text-xs">After</label>
                                            <input type="number" name="channel_after" class="form-control" required>
                                            @error('channel_after') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-secondary shadow-none border">
                            <div class="card-body">
                                <h5 class="card-title text-center">UTILISASI INTERFERENCE</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-0">
                                            <label class="form-control-label text-xs">Before (%)</label>
                                            <div class="input-group">
                                                <input type="number" name="utilisasi_before" class="form-control" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('utilisasi_before') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-0">
                                            <label class="form-control-label text-xs">After (%)</label>
                                            <div class="input-group">
                                                <input type="number" name="utilisasi_after" class="form-control" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            @error('utilisasi_after') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Keterangan</label>
                            {{ Form::textarea('keterangan', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Isian keterangan tambahan...']) }}
                            @error('keterangan') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class="col-6 text-right offset-6 d-flex justify-content-end">
                        <a href="{{ route('interference-ratio.index') }}" class="btn btn-secondary px-5 mr-3" style="background-color: #0b5e79; color: white; border: none;">Batal</a>
                        <button type="submit" class="btn btn-primary px-5" style="background-color: #0b4ab1;">Simpan Data</button>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* === SELECT2 ARGON THEME FIX === */
.select2-container--bootstrap4 .select2-selection {
    min-height: 44px;
    border: 1px solid #cad1d7;
    border-radius: .375rem;
    padding: .375rem .75rem;
    background-color: #fff;
}
.select2-container--bootstrap4 .select2-selection--single {
    display: flex;
    align-items: center;
}
.select2-container--bootstrap4 .select2-selection__rendered {
    padding-left: 0;
    line-height: normal;
    color: #8898aa;
}
.select2-container--bootstrap4 .select2-selection__arrow {
    height: 100%;
    top: 0;
    right: 8px;
}
.select2-container--bootstrap4.select2-container--focus .select2-selection,
.select2-container--bootstrap4.select2-container--open .select2-selection {
    border-color: #5e72e4;
    box-shadow: 0 0 0 0.2rem rgba(94,114,228,.25);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: false
    });

    function fetchSNs(projectId) {
        if (!projectId) {
            $('#ap_name_select').html('<option value="">Pilih AP Name...</option>').trigger('change');
            return;
        }

        let url = "{{ route('cases.get-sn', ':id') }}";
        url = url.replace(':id', projectId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih AP Name...</option>';
                // Note: The value and text is the same since ap_name is string. Or we can just store the full text generated by CasesController
                $.each(data, function(key, value) {
                    options += '<option value="' + value + '">' + value + '</option>';
                });
                $('#ap_name_select').html(options).trigger('change');
            }
        });
    }

    $('#project_select').on('change', function() {
        let projectId = $(this).val();
        fetchSNs(projectId);
    });

    let initialProject = $('#project_select').val();
    if (initialProject) {
        fetchSNs(initialProject);
    }
    
    $('#formRatio').on('submit', function() {
        if (!$('#ap_name_select').val()) {
            Swal.fire('Peringatan', 'Silahkan pilih AP Name dari list!', 'warning');
            return false;
        }
        return true;
    });
});
</script>
@endpush

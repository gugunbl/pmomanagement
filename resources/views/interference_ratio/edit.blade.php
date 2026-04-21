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
                {!! Form::model($ratio, ['route' => ['interference-ratio.update', $ratio->id], 'method' => 'PUT', 'id' => 'formRatio']) !!}
                
                <h6 class="heading-small text-muted mb-4">Informasi Dasar & Perangkat</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            {{ Form::select('id_project', $projects, null, ['class' => 'form-control select2', 'id' => 'project_select']) }}
                            @error('id_project') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ \Carbon\Carbon::parse($ratio->tanggal)->format('Y-m-d') }}">
                            @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Waktu Monitor</label>
                            {{ Form::select('waktu', ['' => 'Pilih Waktu...', '07.00 WIB' => '07.00 WIB', '12.00 WIB' => '12.00 WIB', '17.00 WIB' => '17.00 WIB', '22.00 WIB' => '22.00 WIB'], null, ['class' => 'form-control', 'required']) }}
                            @error('waktu') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div id="ap_container">
                    <hr class="my-4" />
                    <h6 class="heading-small text-muted mb-4">Interference Data</h6>
                    @foreach($ratios as $i => $r)
                    <input type="hidden" name="ap_data[{{ $i }}][id]" value="{{ $r->id }}">
                    <div class="p-3 mb-4 rounded border bg-white shadow-sm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-control-label d-block text-primary"><i class="fas fa-wifi"></i> AP Name (Pilih Perangkat)</label>
                                    <select name="ap_data[{{ $i }}][ap_name]" class="form-control select2 ap_name_select" data-selected="{{ $r->ap_name }}" required>
                                        <option value="{{ $r->ap_name }}">{{ $r->ap_name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-secondary shadow-none border mb-0">
                                    <div class="card-body py-3">
                                        <h5 class="card-title text-center text-muted mb-3">CHANNEL</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">Before</label>
                                                    <input type="number" name="ap_data[{{ $i }}][channel_before]" class="form-control" required value="{{ $r->channel_before }}">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">After</label>
                                                    <input type="number" name="ap_data[{{ $i }}][channel_after]" class="form-control" required value="{{ $r->channel_after }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-secondary shadow-none border mb-0">
                                    <div class="card-body py-3">
                                        <h5 class="card-title text-center text-muted mb-3">UTILISASI INTERFERENCE</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">Before (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="ap_data[{{ $i }}][utilisasi_before]" class="form-control" required value="{{ $r->utilisasi_before }}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">After (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="ap_data[{{ $i }}][utilisasi_after]" class="form-control" required value="{{ $r->utilisasi_after }}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
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
                        <button type="submit" class="btn btn-primary px-5" style="background-color: #0b4ab1;">Update Data</button>
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
            $('.ap_name_select').html('<option value="">Pilih AP Name...</option>').trigger('change');
            return;
        }

        let url = "{{ route('cases.get-sn', ':id') }}";
        url = url.replace(':id', projectId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('.ap_name_select').each(function() {
                    let select = $(this);
                    let currentApName = select.data('selected');
                    let options = '<option value="">Pilih AP Name...</option>';
                    let found = false;
                    $.each(data, function(key, value) {
                        options += '<option value="' + value + '">' + value + '</option>';
                        if (value == currentApName) found = true;
                    });
                    
                    if(!found && currentApName) {
                        options += '<option value="' + currentApName + '">' + currentApName + '</option>';
                    }
                    
                    select.html(options);
                    select.val(currentApName).trigger('change');
                });
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
});
</script>
@endpush

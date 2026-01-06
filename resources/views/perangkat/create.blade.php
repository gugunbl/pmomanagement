@extends('layouts.app')
@push('pg_btn')
    <a href="{{route('perangkat.index')}}" class="btn btn-sm btn-neutral">Data Semua Perangkat</a>
<!--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
-->
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    {!! Form::open(['route' => 'perangkat.store','method' => 'POST', 'files' => true]) !!}

    @csrf
                    <h6 class="heading-small text-muted mb-4">Informasi Perangkat</h6>
                        <div class="pl-lg-4">

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('id_project', 'Select Project', ['class' => 'form-control-label']) }}
                                        {{ Form::select('id_project',['' => ''] + $projectes->toArray(),null,['class'=> 'form-control select2', 'data-placeholder' => 'Ketik nama project...']) }}
                                    </div>
                                </div>


                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('jenis', 'Select Jenis Perangkat', ['class' => 'form-control-label']) }}
{{ Form::select(
    'jenis',
    ['' => ''] + $jenisperangkates->toArray(),
    null,
    [
        'class'=> 'form-control select2',
        'data-placeholder' => 'Ketik jenis perangkat...'
    ]
) }}
                                    </div>
                                </div>


                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('merk', 'Merk Perangkat', ['class' => 'form-control-label']) }}
                                        {{ Form::text('merk', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>



                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('type', 'Type Perangkat', ['class' => 'form-control-label']) }}
                                        {{ Form::text('type', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('serialnumber', 'Serial Number', ['class' => 'form-control-label']) }}
                                        {{ Form::text('serialnumber', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>


                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="status" class="form-control-label">Pilih Status Perangkat</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="" disabled selected>Pilih Status Perangkat...</option>
                                            @foreach($statusperangkat as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>



                        <hr class="my-4" />


                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('site', 'Site / Lokasi', ['class' => 'form-control-label']) }}
                                        {{ Form::select('site', $sitelist, null, ['class' => 'form-control select2']) }}
                                    </div>
                                </div>


                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('lantai', 'Lantai / Rack', ['class' => 'form-control-label']) }}
                                        {{ Form::text('lantai', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>



                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('start_warranty', 'Tanggal Awal Warranty', ['class' => 'form-control-label']) }}
                                        {{ Form::date('start_warranty', null, [
                                            'class' => 'form-control',
                                            'id' => 'start_warranty'
                                        ]) }}
                                    </div>
                                </div>


                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('end_warranty', 'Tanggal Akhir Warranty', ['class' => 'form-control-label']) }}
                                        {{ Form::date('end_warranty', null, [
                                            'class' => 'form-control',
                                            'id' => 'end_warranty'
                                        ]) }}
                                    </div>
                                </div>



                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('tgl_online', 'Tanggal Online Perangkat', ['class' => 'form-control-label']) }}
                                        {{ Form::date('tgl_online', null, [
                                            'class' => 'form-control',
                                            'id' => 'tgl_online'
                                        ]) }}
                                    </div>
                                </div>




                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            {{ Form::label('keterangan', 'Keterangan', ['class' => 'form-control-label']) }}
                                            {!! Form::textarea('keterangan', null, ['id'=>"summernote", 'class'=> 'form-control',]) !!}
                                        </div>
                                    </div>
                                </div>




                                <div class="col-md-12">
                                    {{ Form::submit('Submit', ['class'=> 'mt-5 btn btn-primary']) }}
                                </div>

                        {!! Form::close() !!}
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/summernote-bs4.min.css') }}">

<style>
/* Optional: Styling tambahan */
.tanggal-spk-input {
    font-family: monospace;
    letter-spacing: 1px;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 80%;
    color: #dc3545;
}

/* === FINAL SELECT2 FIX (Bootstrap 4 / Argon) === */
.select2-container {
    width: 100% !important;
}

.select2-container--bootstrap4 .select2-selection {
    min-height: 44px;
    border: 1px solid #cad1d7;
    border-radius: .375rem;
    padding: .375rem .75rem;
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

/* Search input di dropdown */
.select2-search--dropdown .select2-search__field {
    height: 40px;
    padding: .375rem .75rem;
    border-radius: .375rem;
}
</style>
@endpush



@push('scripts')

<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });
});
</script>



<script src="{{ asset('assets/js/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>
<script>
    jQuery(document).ready(function() {
        jQuery('#summernote').summernote({
            height: 150,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
              ]

        });
        jQuery('#uploadFile').filemanager('file');
    });
  </script>
@endpush

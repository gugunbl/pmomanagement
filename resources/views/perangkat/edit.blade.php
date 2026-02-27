@extends('layouts.app')
@push('pg_btn')
    <a href="{{route('perangkat.index')}}" class="btn btn-sm btn-neutral">Data Semua Perangkat</a>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    {!! Form::model($perangkat, ['route' => ['perangkat.update', $perangkat->id_perangkat], 'method' => 'PUT', 'files' => true]) !!}

    @csrf
                    <h6 class="heading-small text-muted mb-4">Edit Informasi Perangkat: {{ $perangkat->serialnumber }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group font-weight-bold border-left border-primary pl-3">
                                    {{ Form::label('id_project', '1. Select Project', ['class' => 'form-control-label']) }}
                                    {{ Form::select('id_project', ['' => ''] + $projectes->toArray(), null, ['class'=> 'form-control select2', 'data-placeholder' => 'Ketik nama project...']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group font-weight-bold border-left border-primary pl-3">
                                    {{ Form::label('jenis', '2. Select Jenis Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::select('jenis', ['' => ''] + $jenisperangkates->toArray(), null, ['class'=> 'form-control select2', 'data-placeholder' => 'Ketik jenis perangkat...']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('merk', 'Merk Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::text('merk', null, ['class' => 'form-control', 'placeholder' => 'e.g. Dell, HP, etc.']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('type', 'Type Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::text('type', null, ['class' => 'form-control', 'placeholder' => 'e.g. PowerEdge R760']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('dimensi', 'Dimensi (U)', ['class' => 'form-control-label']) }}
                                    {{ Form::text('dimensi', null, ['class' => 'form-control', 'placeholder' => 'e.g. 2U']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('id_lokasi', 'Site / Alamat Lokasi', ['class' => 'form-control-label']) }}
                                    <select name="id_lokasi" id="id_lokasi_select" class="form-control select2" data-placeholder="Select Site...">
                                        <option value=""></option>
                                        @foreach($lokasis as $lok)
                                            <option value="{{ $lok->id_lokasi }}" {{ $perangkat->id_lokasi == $lok->id_lokasi ? 'selected' : '' }} data-alamat="{{ $lok->alamat }}" data-nama="{{ $lok->nama_lokasi }}">{{ $lok->nama_lokasi }}</option>
                                        @endforeach
                                    </select>
                                    {{ Form::hidden('site', null, ['id' => 'site_hidden']) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Foto Perangkat (Kosongkan jika tidak ingin mengubah)</label>
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            @if($perangkat->foto_1)
                                                <img src="{{ asset('storage/'.$perangkat->foto_1) }}" class="img-fluid rounded mb-2 border" style="max-height: 100px;">
                                            @endif
                                            {{ Form::file('foto_1', ['class' => 'form-control-file', 'accept' => 'image/*']) }}
                                            <small class="text-muted">Foto 1</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            @if($perangkat->foto_2)
                                                <img src="{{ asset('storage/'.$perangkat->foto_2) }}" class="img-fluid rounded mb-2 border" style="max-height: 100px;">
                                            @endif
                                            {{ Form::file('foto_2', ['class' => 'form-control-file', 'accept' => 'image/*']) }}
                                            <small class="text-muted">Foto 2</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            @if($perangkat->foto_3)
                                                <img src="{{ asset('storage/'.$perangkat->foto_3) }}" class="img-fluid rounded mb-2 border" style="max-height: 100px;">
                                            @endif
                                            {{ Form::file('foto_3', ['class' => 'form-control-file', 'accept' => 'image/*']) }}
                                            <small class="text-muted">Foto 3</small>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">Format: JPG/PNG. Max: 5MB per file</small>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="row_alamat" style="{{ $perangkat->id_lokasi ? '' : 'display:none;' }}">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('alamat', 'Detail Alamat Gedung / DC', ['class' => 'form-control-label']) }}
                                    {{ Form::textarea('alamat', $perangkat->lokasi->alamat ?? null, ['id' => 'alamat_display', 'class' => 'form-control', 'rows' => 2, 'readonly' => true]) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('keterangan', 'Spesifikasi Detail / Catatan Umum', ['class' => 'form-control-label']) }}
                                    {!! Form::textarea('keterangan', null, ['id'=>"summernote", 'class'=> 'form-control']) !!}
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" />
                        <h6 class="heading-small text-muted mb-4">Detail Unit (Serial Number)</h6>

                        <div class="table-responsive border rounded bg-white shadow-sm mb-4">
                            <table class="table table-sm align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="min-width: 180px;">Serial Number</th>
                                        <th style="min-width: 120px;">Koordinat</th>
                                        <th style="min-width: 120px;">Lantai/Rak</th>
                                        <th style="min-width: 140px;">Status Server</th>
                                        <th style="min-width: 140px;">License</th>
                                        <th style="min-width: 150px;">Warranty Start</th>
                                        <th style="min-width: 150px;">Warranty End</th>
                                        <th style="min-width: 150px;">Tanggal Online</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ Form::text('serialnumber', null, ['class' => 'form-control form-control-sm', 'required' => true]) }}</td>
                                        <td>{{ Form::text('koordinat', null, ['class' => 'form-control form-control-sm']) }}</td>
                                        <td>{{ Form::text('lantai', null, ['class' => 'form-control form-control-sm']) }}</td>
                                        <td>
                                            {{ Form::select('status_server', ['New Device' => 'New Device', 'Used Device' => 'Used Device'], null, ['class' => 'form-control form-control-sm']) }}
                                        </td>
                                        <td>{{ Form::text('type_lisensi', null, ['class' => 'form-control form-control-sm']) }}</td>
                                        <td>{{ Form::date('start_warranty', null, ['class' => 'form-control form-control-sm']) }}</td>
                                        <td>{{ Form::date('end_warranty', null, ['class' => 'form-control form-control-sm']) }}</td>
                                        <td>{{ Form::date('tgl_online', null, ['class' => 'form-control form-control-sm']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right">
                            <a href="{{ route('perangkat.show', $perangkat->id_perangkat) }}" class="btn btn-secondary px-5">Batal</a>
                            {{ Form::submit('Simpan Perubahan', ['class'=> 'btn btn-primary px-5 shadow']) }}
                        </div>
                        {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/summernote-bs4.min.css') }}">
<style>
.select2-container { width: 100% !important; }
.select2-container--bootstrap4 .select2-selection { min-height: 44px; border: 1px solid #cad1d7; border-radius: .375rem; padding: .375rem .75rem; }
.select2-container--bootstrap4 .select2-selection--single { display: flex; align-items: center; }
.select2-container--bootstrap4 .select2-selection__rendered { padding-left: 0; line-height: normal; color: #8898aa; }
.select2-container--bootstrap4 .select2-selection__arrow { height: 100%; top: 0; right: 8px; }
.select2-container--bootstrap4.select2-container--focus .select2-selection, .select2-container--bootstrap4.select2-container--open .select2-selection { border-color: #5e72e4; box-shadow: 0 0 0 0.2rem rgba(94,114,228,.25); }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/summernote-bs4.min.js') }}"></script>
<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: function () { return $(this).data('placeholder'); }
    });

    $('#id_lokasi_select').on('change', function() {
        var selected = $(this).find('option:selected');
        var alamat = selected.data('alamat');
        var nama = selected.data('nama');
        
        if (selected.val()) {
            $('#alamat_display').val(alamat || '-');
            $('#row_alamat').show();
            $('#site_hidden').val(nama);
        } else {
            $('#alamat_display').val('');
            $('#row_alamat').hide();
            $('#site_hidden').val('');
        }
    });

    jQuery('#summernote').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]
    });

    $('input[name="merk"], input[name="type"]').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
});
</script>
@endpush

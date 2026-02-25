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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group font-weight-bold border-left border-primary pl-3">
                                    {{ Form::label('id_project', '1. Select Project', ['class' => 'form-control-label']) }}
                                    {{ Form::select('id_project',['' => ''] + $projectes->toArray(),null,['class'=> 'form-control select2', 'data-placeholder' => 'Ketik nama project...']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group font-weight-bold border-left border-primary pl-3">
                                    {{ Form::label('jenis', '2. Select Jenis Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::select('jenis',['' => ''] + $jenisperangkates->toArray(),null,['class'=> 'form-control select2', 'data-placeholder' => 'Ketik jenis perangkat...']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('merk', 'Merk Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::text('merk', null, ['class' => 'form-control', 'placeholder' => 'e.g. Dell, HP, etc.']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('type', 'Type Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::text('type', null, ['class' => 'form-control', 'placeholder' => 'e.g. PowerEdge R760']) }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('dimensi', 'Dimensi (U)', ['class' => 'form-control-label']) }}
                                    {{ Form::text('dimensi', null, ['class' => 'form-control', 'placeholder' => 'e.g. 2U']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('qty', 'Quantity (Jumlah Unit)', ['class' => 'form-control-label']) }}
                                    {{ Form::number('qty', 1, ['class' => 'form-control', 'id' => 'qty_input', 'min' => 1]) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('site', 'Site / Lokasi Global', ['class' => 'form-control-label']) }}
                                    {{ Form::select('site', $sitelist, null, ['class' => 'form-control select2']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('id_lokasi', 'Lokasi Gedung / DC', ['class' => 'form-control-label']) }}
                                    {{ Form::select('id_lokasi', \App\Lokasi::pluck('nama_lokasi', 'id_lokasi'), null, ['class' => 'form-control select2', 'placeholder' => 'Pilih Lokasi...']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('upload_do', 'Upload DO (Signed)', ['class' => 'form-control-label']) }}
                                    {{ Form::file('upload_do', ['class' => 'form-control-file']) }}
                                    <small class="text-muted">Format: PDF/JPG/PNG. Max: 5MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('keterangan', 'Spesifikasi Detail / Catatan Umum (Berlaku untuk semua unit)', ['class' => 'form-control-label']) }}
                                    {!! Form::textarea('keterangan', null, ['id'=>"summernote", 'class'=> 'form-control']) !!}
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" />
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="heading-small text-muted mb-0">Input Detail Unit (Per Serial Number)</h6>
                            <div class="custom-control custom-checkbox pt-2">
                                <input type="checkbox" class="custom-control-input" id="apply_to_all_dates">
                                <label class="custom-control-label text-xs" for="apply_to_all_dates">Samakan Tanggal Garansi & Online (Gunakan Baris 1)</label>
                            </div>
                        </div>

                        <div class="table-responsive border rounded bg-white shadow-sm mb-4" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-sm align-items-center table-flush" id="unit_grid">
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
                                <tbody id="unit_tbody">
                                    <!-- Row 1 (Default) -->
                                    <tr class="unit-row">
                                        <td><input type="text" name="units[0][sn]" class="form-control form-control-sm" required placeholder="SN..."></td>
                                        <td><input type="text" name="units[0][koordinat]" class="form-control form-control-sm" placeholder="RCK-..."></td>
                                        <td><input type="text" name="units[0][lantai]" class="form-control form-control-sm"></td>
                                        <td>
                                            <select name="units[0][status_server]" class="form-control form-control-sm">
                                                <option value="New Device">New Device</option>
                                                <option value="Used Device">Used Device</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="units[0][type_lisensi]" class="form-control form-control-sm"></td>
                                        <td><input type="date" name="units[0][start_warranty]" class="form-control form-control-sm row-date-start"></td>
                                        <td><input type="date" name="units[0][end_warranty]" class="form-control form-control-sm row-date-end"></td>
                                        <td><input type="date" name="units[0][tgl_online]" class="form-control form-control-sm row-date-online"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right">
                            {{ Form::submit('Simpan Unit-Unit Perangkat', ['class'=> 'btn btn-primary btn-lg px-5 shadow']) }}
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

    // Handle Quantity -> Unit Grid
    $('#qty_input').on('change input', function() {
        var qty = $(this).val();
        var tbody = $('#unit_tbody');
        var currentCount = $('.unit-row').length;

        if (qty > currentCount) {
            for (var i = currentCount; i < qty; i++) {
                var html = `
                    <tr class="unit-row">
                        <td><input type="text" name="units[${i}][sn]" class="form-control form-control-sm" required placeholder="SN..."></td>
                        <td><input type="text" name="units[${i}][koordinat]" class="form-control form-control-sm" placeholder="RCK-..."></td>
                        <td><input type="text" name="units[${i}][lantai]" class="form-control form-control-sm"></td>
                        <td>
                            <select name="units[${i}][status_server]" class="form-control form-control-sm">
                                <option value="New Device">New Device</option>
                                <option value="Used Device">Used Device</option>
                            </select>
                        </td>
                        <td><input type="text" name="units[${i}][type_lisensi]" class="form-control form-control-sm"></td>
                        <td><input type="date" name="units[${i}][start_warranty]" class="form-control form-control-sm row-date-start"></td>
                        <td><input type="date" name="units[${i}][end_warranty]" class="form-control form-control-sm row-date-end"></td>
                        <td><input type="date" name="units[${i}][tgl_online]" class="form-control form-control-sm row-date-online"></td>
                    </tr>
                `;
                tbody.append(html);
            }
        } else if (qty < currentCount) {
            $('.unit-row').slice(qty).remove();
        }
        
        // Trigger date sync if enabled
        syncDates();
    });

    // Handle Date Sync
    function syncDates() {
        if ($('#apply_to_all_dates').is(':checked')) {
            var firstStart = $('.row-date-start').first().val();
            var firstEnd = $('.row-date-end').first().val();
            var firstOnline = $('.row-date-online').first().val();
            
            $('.row-date-start').val(firstStart);
            $('.row-date-end').val(firstEnd);
            $('.row-date-online').val(firstOnline);
        }
    }

    $(document).on('change input', '.row-date-start, .row-date-end, .row-date-online', function() {
        syncDates();
    });

    $('#apply_to_all_dates').on('change', function() {
        syncDates();
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

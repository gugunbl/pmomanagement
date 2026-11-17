@extends('layouts.app')
@section('body_class', 'perangkat-page')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/perangkat.css') }}?v={{ @filemtime(public_path('assets/css/perangkat.css')) ?: '1' }}">
@endpush
@push('pg_btn')
    <a href="{{ route('perangkat.summary') }}" class="btn btn-sm btn-neutral">Kembali ke Summary</a>
@endpush
@section('content')
<div class="device-workspace">
    @include('perangkat._workspace_nav', ['activeDevicePage' => 'summary'])
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5 device-panel device-form-card">
                <div class="device-page-header"><div class="device-page-heading"><span class="device-page-heading__icon"><i class="fas fa-layer-group"></i></span><div><h2>Detail Kelompok Perangkat</h2><p>Kelola informasi umum dan unit perangkat dalam satu workspace.</p></div></div></div>
                <div class="card-body">
                    {!! Form::open(['route' => 'perangkat.view-detail-summary-update','method' => 'POST', 'files' => true]) !!}
                    @csrf
                    {{ Form::hidden('id_project', $id_project) }}
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="heading-small text-muted mb-0">Informasi Perangkat</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="collapse" data-target="#collapseDeviceInfo" aria-expanded="false" aria-controls="collapseDeviceInfo">
                            <i class="fas fa-arrows-alt-v mr-1"></i> Tampilkan / Sembunyikan Info
                        </button>
                    </div>

                    <div class="collapse show" id="collapseDeviceInfo">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group font-weight-bold border-left border-primary pl-3">
                                    {{ Form::label('project_display', 'Project', ['class' => 'form-control-label']) }}
                                    {{ Form::text('project_display', $project->nama_project, ['class' => 'form-control', 'readonly' => true]) }}
                                    {{ Form::hidden('id_project', $id_project) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group font-weight-bold border-left border-primary pl-3">
                                    {{ Form::label('jenis', 'Pilih Jenis Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::select('jenis',['' => ''] + $jenisperangkates->toArray(), $basePerangkat ? $basePerangkat->jenis : null, ['class'=> 'form-control select2', 'data-placeholder' => 'Ketik jenis perangkat...']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('merk', 'Merk Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::text('merk', $merk ?: ($basePerangkat ? $basePerangkat->merk : null), ['class' => 'form-control', 'placeholder' => 'e.g. Dell, HP, etc.']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('type', 'Type Perangkat', ['class' => 'form-control-label']) }}
                                    {{ Form::text('type', $type ?: ($basePerangkat ? $basePerangkat->type : null), ['class' => 'form-control', 'placeholder' => 'e.g. PowerEdge R760']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('dimensi', 'Dimensi (U)', ['class' => 'form-control-label']) }}
                                    {{ Form::text('dimensi', $basePerangkat ? $basePerangkat->dimensi : null, ['class' => 'form-control', 'placeholder' => 'e.g. 2U']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Foto Perangkat (Max 3)</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            @if($basePerangkat && $basePerangkat->foto_1)
                                                <img src="{{ asset('storage/'.$basePerangkat->foto_1) }}" class="img-fluid mb-2 rounded shadow-sm" style="max-height: 100px;">
                                            @endif
                                            {{ Form::file('foto_1', ['class' => 'form-control-file', 'accept' => 'image/*']) }}
                                            <small class="text-muted">Foto 1</small>
                                        </div>
                                        <div class="col-md-4">
                                            @if($basePerangkat && $basePerangkat->foto_2)
                                                <img src="{{ asset('storage/'.$basePerangkat->foto_2) }}" class="img-fluid mb-2 rounded shadow-sm" style="max-height: 100px;">
                                            @endif
                                            {{ Form::file('foto_2', ['class' => 'form-control-file', 'accept' => 'image/*']) }}
                                            <small class="text-muted">Foto 2</small>
                                        </div>
                                        <div class="col-md-4">
                                            @if($basePerangkat && $basePerangkat->foto_3)
                                                <img src="{{ asset('storage/'.$basePerangkat->foto_3) }}" class="img-fluid mb-2 rounded shadow-sm" style="max-height: 100px;">
                                            @endif
                                            {{ Form::file('foto_3', ['class' => 'form-control-file', 'accept' => 'image/*']) }}
                                            <small class="text-muted">Foto 3</small>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">Format: JPG/PNG. Max: 5MB per file. Kosongkan jika tidak ingin merubah foto.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {{ Form::label('keterangan', 'Spesifikasi Detail / Catatan Umum (Berlaku untuk semua unit)', ['class' => 'form-control-label']) }}
                                    {!! Form::textarea('keterangan', $basePerangkat ? $basePerangkat->keterangan : null, ['id'=>"summernote", 'class'=> 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />
                    
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <h6 class="heading-small text-muted mb-0">Input Detail Unit (Per Serial Number)</h6>
                        <div class="btn-group">
                            <a href="{{ route('perangkat.template') }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-excel mr-1"></i> Download Template
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" id="btn_export_current">
                                <i class="fas fa-file-excel mr-1"></i> Download Detail
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn_import_excel">
                                <i class="fas fa-file-import mr-1"></i> Import from Excel
                            </button>
                            <input type="file" id="import_excel_file" style="display:none;" accept=".xlsx, .xls">
                        </div>
                    </div>
                    
                    <div class="alert alert-secondary py-2 px-3 mt-2 mb-0" style="font-size: 0.75rem; border-left: 3px solid #f5365c;">
                        <i class="fas fa-info-circle mr-1 text-danger"></i> 
                        <strong>Petunjuk:</strong> Anda dapat mengedit data langsung pada tabel atau melalui Import Excel. Gunakan "Download Detail" untuk mendapatkan data saat ini.
                    </div>
                    <div class="alert alert-secondary py-2 px-3 mt-1 mb-0" style="font-size: 0.75rem; border-left: 3px solid #2dce89;">
                        <i class="fas fa-info-circle mr-1 text-success"></i> 
                        <strong>Template:</strong> Gunakan "Download Template". Baris ke-2 adalah sample dan akan di-skip saat import.
                    </div>
                    
                    <div class="custom-control custom-checkbox pt-2 mb-3 text-right">
                        <input type="checkbox" class="custom-control-input" id="apply_to_all_dates">
                        <label class="custom-control-label text-xs" for="apply_to_all_dates">Samakan Tanggal Garansi & Online (Gunakan Baris 1)</label>
                    </div>

                    <div class="table-responsive border rounded bg-white shadow-sm mb-4" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm align-items-center table-flush" id="unit_grid">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width: 180px;">Serial Number</th>
                                    <th style="min-width: 150px;">SIK</th>
                                    <th style="min-width: 150px;">Hostname</th>
                                    <th style="min-width: 200px;">Site / Alamat Lokasi</th>
                                    <th style="min-width: 120px;">Koordinat</th>
                                    <th style="min-width: 120px;">Lantai/Rak</th>
                                    <th style="min-width: 160px;">Status</th>
                                    <th style="min-width: 150px;">Warranty Start</th>
                                    <th style="min-width: 150px;">Warranty End</th>
                                    <th style="min-width: 150px;">Tanggal Online</th>
                                    <th style="min-width: 140px;" id="license_header">License</th>
                                </tr>
                            </thead>
                            <tbody id="unit_tbody">
                                @foreach($perangkats as $index => $unit)
                                <tr class="unit-row" data-id="{{ $unit->id_perangkat }}">
                                    {{ Form::hidden("units[$index][id_perangkat]", $unit->id_perangkat) }}
                                    <td><input type="text" name="units[{{$index}}][sn]" class="form-control form-control-sm" required value="{{ $unit->serialnumber }}"></td>
                                    <td><input type="text" name="units[{{$index}}][sik]" class="form-control form-control-sm" value="{{ $unit->sik }}"></td>
                                    <td><input type="text" name="units[{{$index}}][hostname]" class="form-control form-control-sm" value="{{ $unit->hostname }}"></td>
                                    <td>
                                        <select name="units[{{$index}}][id_lokasi]" class="form-control form-control-sm select2" data-placeholder="Select Site...">
                                            <option value=""></option>
                                            @foreach($lokasis as $lok)
                                                <option value="{{ $lok->id_lokasi }}" {{ $unit->id_lokasi == $lok->id_lokasi ? 'selected' : '' }}>{{ $lok->nama_lokasi }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="units[{{$index}}][koordinat]" class="form-control form-control-sm" value="{{ $unit->koordinat }}"></td>
                                    <td><input type="text" name="units[{{$index}}][lantai]" class="form-control form-control-sm" value="{{ $unit->lantai }}"></td>
                                    <td>
                                        <select name="units[{{$index}}][status]" class="form-control form-control-sm" required>
                                            @foreach($statusperangkat as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" {{ (string) $unit->status === (string) $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="date" name="units[{{$index}}][start_warranty]" class="form-control form-control-sm row-date-start" value="{{ $unit->start_warranty }}"></td>
                                    <td><input type="date" name="units[{{$index}}][end_warranty]" class="form-control form-control-sm row-date-end" value="{{ $unit->end_warranty }}"></td>
                                    <td><input type="date" name="units[{{$index}}][tgl_online]" class="form-control form-control-sm row-date-online" value="{{ $unit->tgl_online }}"></td>
                                    <td><input type="text" name="units[{{$index}}][type_lisensi]" class="form-control form-control-sm" value="{{ $unit->type_lisensi }}"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between device-form-footer">
                        <a href="{{ route('perangkat.summary') }}" class="btn btn-secondary btn-lg px-5 shadow">Batal</a>
                        {{ Form::submit('Update Unit-Unit Perangkat', ['class'=> 'btn btn-primary btn-lg px-5 shadow']) }}
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
    .select2-container { width: 100% !important; }
    .select2-container--bootstrap4 .select2-selection {
        min-height: 38px;
        border: 1px solid #cad1d7;
        border-radius: .375rem;
    }
    .unit-row.new-row { background-color: #f0fff4; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/summernote-bs4.min.js') }}"></script>
<script>
$(document).ready(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%', allowClear: true });

    $('#summernote').summernote({
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
    $(document).on('change input', '.row-date-start, .row-date-end, .row-date-online', syncDates);
    $('#apply_to_all_dates').on('change', syncDates);

    // Excel Export (Client Side)
    $('#btn_export_current').on('click', function() {
        let csv = [];
        let rows = document.querySelectorAll("#unit_grid tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length; j++) {
                let text = "";
                let input = cols[j].querySelector("input, select");
                if (input) {
                    if (input.tagName === "SELECT") {
                        text = input.options[input.selectedIndex].text;
                    } else {
                        text = input.value;
                    }
                } else {
                    text = cols[j].innerText;
                }
                row.push('"' + text.replace(/"/g, '""') + '"');
            }
            csv.push(row.join(","));
        }
        
        let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        let downloadLink = document.createElement("a");
        downloadLink.download = "detail_perangkat_{{ $project->nama_project }}.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
    });

    // Excel Import
    $('#btn_import_excel').on('click', function() {
        $('#import_excel_file').click();
    });

    $('#import_excel_file').on('change', function() {
        var file_data = $(this).prop('files')[0];
        if (!file_data) return;

        var form_data = new FormData();
        form_data.append('file_excel', file_data);
        form_data.append('_token', '{{ csrf_token() }}');
        $(this).val('');

        $.ajax({
            url: '{{ route("perangkat.import", [], false) }}',
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            beforeSend: function() {
                Swal.fire({ title: 'Processing...', text: 'Importing data...', allowOutsideClick: false, onBeforeOpen: () => { Swal.showLoading(); }});
            },
            success: function(response) {
                Swal.close();
                if (response.success && response.data.length > 0) {
                    var data = response.data;
                    var tbody = $('#unit_tbody');
                    var startIndex = $('.unit-row').length;
                    
                    data.forEach(function(row, i) {
                        var index = startIndex + i;
                        var html = `
                            <tr class="unit-row new-row">
                                <input type="hidden" name="units[${index}][id_perangkat]" value="">
                                <td><input type="text" name="units[${index}][sn]" class="form-control form-control-sm" required value="${row.sn}"></td>
                                <td><input type="text" name="units[${index}][sik]" class="form-control form-control-sm" value="${row.sik}"></td>
                                <td><input type="text" name="units[${index}][hostname]" class="form-control form-control-sm" value="${row.hostname}"></td>
                                <td>
                                    <select name="units[${index}][id_lokasi]" class="form-control form-control-sm select2-new" data-placeholder="Select Site...">
                                        <option value=""></option>
                                        @foreach($lokasis as $lok)
                                            <option value="{{ $lok->id_lokasi }}">{{ $lok->nama_lokasi }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="units[${index}][koordinat]" class="form-control form-control-sm" value="${row.koordinat}"></td>
                                <td><input type="text" name="units[${index}][lantai]" class="form-control form-control-sm" value="${row.lantai}"></td>
                                <td>
                                    <select name="units[${index}][status]" class="form-control form-control-sm" required>
                                        <option value="0" ${String(row.status) === '0' ? 'selected' : ''}>Tidak Aktif</option>
                                        <option value="1" ${String(row.status ?? 1) === '1' ? 'selected' : ''}>Aktif</option>
                                        <option value="3" ${String(row.status) === '3' ? 'selected' : ''}>Golden Unit/Stock</option>
                                    </select>
                                </td>
                                <td><input type="date" name="units[${index}][start_warranty]" class="form-control form-control-sm row-date-start" value="${row.start_warranty}"></td>
                                <td><input type="date" name="units[${index}][end_warranty]" class="form-control form-control-sm row-date-end" value="${row.end_warranty}"></td>
                                <td><input type="date" name="units[${index}][tgl_online]" class="form-control form-control-sm row-date-online" value="${row.tgl_online}"></td>
                                <td><input type="text" name="units[${index}][type_lisensi]" class="form-control form-control-sm" value="${row.type_lisensi}"></td>
                            </tr>
                        `;
                        tbody.prepend(html); // Put at top as requested
                        $(`select[name="units[${index}][id_lokasi]"]`).val(row.id_lokasi);
                    });

                    $('.select2-new').select2({ theme: 'bootstrap4', width: '100%', allowClear: true }).removeClass('select2-new');
                    
                    Swal.fire({ icon: 'success', title: 'Import Success', text: `${data.length} units added to the top of the list.` });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Import failed' });
            }
        });
    });
});
</script>
@endpush

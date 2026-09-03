@extends('layouts.app')

@push('pg_btn')
    <a href="{{ route('traffic-ap.mapping.index', ['id_project' => $selectedProjectId, 'id_lokasi' => $selectedLokasiId]) }}" class="btn btn-sm btn-neutral">
        <i class="fas fa-layer-group mr-1 text-primary"></i> Mapping Lantai & AP
    </a>
    <a href="{{ route('traffic-ap.monitor', ['id_project' => $selectedProjectId, 'id_lokasi' => $selectedLokasiId]) }}" class="btn btn-sm btn-neutral">
        <i class="fas fa-chart-bar mr-1 text-info"></i> Monitor Traffic
    </a>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center mb-3">
                    <div class="col">
                        <h3 class="mb-0">{{ $title }}</h3>
                        <p class="text-sm text-muted mb-0">Input data traffic AP harian per lantai untuk project, lokasi, dan tanggal yang dipilih.</p>
                    </div>
                </div>

                {{-- FORM FILTER / HEADER PILIHAN PROJECT, LOKASI & TANGGAL --}}
                <div class="card bg-secondary shadow-none border p-3 mb-3">
                    <div class="row align-items-center">
                        {{-- Dropdown Project --}}
                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label class="form-control-label font-weight-bold">
                                    <i class="fas fa-project-diagram mr-1 text-primary"></i> Project <span class="text-danger">*</span>
                                </label>
                                @if($fixedProject)
                                    <input type="text" class="form-control bg-white font-weight-bold" value="{{ $selectedProject->nama_project ?? '-' }}" readonly>
                                    <input type="hidden" name="id_project" id="input_id_project" value="{{ $selectedProjectId }}">
                                    <small class="text-info d-block mt-1"><i class="fas fa-lock mr-1"></i> Project ditentukan untuk akun Anda.</small>
                                @else
                                    <select name="id_project" id="input_id_project" class="form-control select2">
                                        @foreach($projects as $proj)
                                            <option value="{{ $proj->id_project }}" {{ $selectedProjectId == $proj->id_project ? 'selected' : '' }}>
                                                {{ $proj->nama_project }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        {{-- Dropdown Lokasi --}}
                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label class="form-control-label font-weight-bold">
                                    <i class="fas fa-building mr-1 text-primary"></i> Lokasi Gedung / Site <span class="text-danger">*</span>
                                </label>
                                <select name="id_lokasi" id="input_id_lokasi" class="form-control select2" data-placeholder="Pilih Lokasi...">
                                    <option value=""></option>
                                    @foreach($lokasis as $lok)
                                        <option value="{{ $lok->id_lokasi }}" {{ $selectedLokasiId == $lok->id_lokasi ? 'selected' : '' }}>
                                            {{ $lok->nama_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Input Tanggal --}}
                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label class="form-control-label font-weight-bold">
                                    <i class="fas fa-calendar-alt mr-1 text-primary"></i> Tanggal <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal" id="input_tanggal" class="form-control" value="{{ $selectedTanggal }}" required>
                            </div>
                        </div>

                        <div class="col-md-2 text-md-right mt-3 mt-md-0">
                            <button type="button" class="btn btn-primary btn-block btn-load-data">
                                <i class="fas fa-sync-alt mr-1"></i> Muat Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                {{-- Form Submission --}}
                <form id="formTrafficInput" action="{{ route('traffic-ap.input.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_project" id="submit_id_project" value="{{ $selectedProjectId }}">
                    <input type="hidden" name="id_lokasi" id="submit_id_lokasi" value="{{ $selectedLokasiId }}">
                    <input type="hidden" name="tanggal" id="submit_tanggal" value="{{ $selectedTanggal }}">

                    {{-- Toolbar Helpers --}}
                    <div id="table-toolbar" class="d-none justify-content-between align-items-center mb-3 bg-white p-2 rounded border">
                        <div>
                            <span class="text-sm text-muted mr-2">Quick Set Satuan:</span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary btn-quick-unit" data-unit="GB">Semua GB</button>
                                <button type="button" class="btn btn-outline-secondary btn-quick-unit" data-unit="MB">Semua MB</button>
                                <button type="button" class="btn btn-outline-secondary btn-quick-unit" data-unit="TB">Semua TB</button>
                            </div>
                        </div>
                        <div>
                            <span id="data-status-badge" class="badge badge-pill badge-info px-3 py-2">
                                <i class="fas fa-info-circle mr-1"></i> Data Siap Diinput
                            </span>
                        </div>
                    </div>

                    {{-- Loading State --}}
                    <div id="loading-state" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Memuat daftar lantai...</p>
                    </div>

                    {{-- Empty State --}}
                    <div id="empty-state" class="text-center py-5">
                        <i class="fas fa-building fa-3x text-light mb-3"></i>
                        <h4 class="text-muted">Pilih Project, Lokasi, dan Tanggal terlebih dahulu</h4>
                        <p class="text-sm text-muted">Daftar lantai pada project & lokasi terpilih akan ditampilkan secara otomatis di bawah ini.</p>
                    </div>

                    {{-- Table Content --}}
                    <div class="table-responsive d-none" id="table-container">
                        <table class="table align-items-center table-bordered table-hover" id="traffic_table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px; text-align: center;">No</th>
                                    <th style="width: 280px;">Lantai</th>
                                    <th style="min-width: 200px;">Input Text (Traffic)</th>
                                    <th style="width: 160px;">Pilihan Size</th>
                                    <th>Info AP Terpasang</th>
                                </tr>
                            </thead>
                            <tbody id="traffic_tbody">
                                {{-- Dynamically populated via AJAX --}}
                            </tbody>
                        </table>

                        <div class="row align-items-center mt-4">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Input hanya angka/desimal (misal: <code>10.55</code>, <code>359.64</code>, <code>1.28</code>). Baris yang dikosongkan tidak akan tersimpan.
                                </small>
                            </div>
                            <div class="col-md-6 text-md-right mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow" id="btn-save-traffic">
                                    <i class="fas fa-save mr-1"></i> Simpan Data Traffic
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.select2-container--bootstrap4 .select2-selection {
    min-height: 44px;
    border: 1px solid #cad1d7;
    border-radius: .375rem;
    padding: .375rem .75rem;
}
.traffic-input-field {
    font-size: 1.05rem;
    font-weight: bold;
    color: #2b3553;
}
.traffic-input-field:focus {
    border-color: #5e72e4;
    background-color: #f8f9fe;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });

    // Dynamic location loading when project changes
    $('#input_id_project').on('change', function() {
        var projectId = $(this).val();
        if (!projectId) {
            $('#input_id_lokasi').empty().append('<option value="">Pilih Lokasi...</option>').trigger('change');
            return;
        }

        $.ajax({
            url: "{{ route('traffic-ap.ajax.project-locations', [], false) }}",
            type: 'GET',
            data: { id_project: projectId },
            success: function(locations) {
                var select = $('#input_id_lokasi');
                select.empty().append('<option value="">Pilih Lokasi...</option>');
                if (locations && locations.length > 0) {
                    locations.forEach(function(lok) {
                        select.append(`<option value="${lok.id_lokasi}">${lok.nama_lokasi}</option>`);
                    });
                    // Auto select the first location
                    select.val(locations[0].id_lokasi).trigger('change');
                } else {
                    select.trigger('change');
                }
            }
        });
    });

    function loadFloorsData() {
        var projectId = $('#input_id_project').val();
        var lokasiId = $('#input_id_lokasi').val();
        var tanggal = $('#input_tanggal').val();

        if (!lokasiId) {
            $('#empty-state').removeClass('d-none');
            $('#table-container').addClass('d-none');
            $('#table-toolbar').addClass('d-none');
            return;
        }

        $('#submit_id_project').val(projectId);
        $('#submit_id_lokasi').val(lokasiId);
        $('#submit_tanggal').val(tanggal);

        $('#empty-state').addClass('d-none');
        $('#table-container').addClass('d-none');
        $('#table-toolbar').addClass('d-none');
        $('#loading-state').removeClass('d-none');

        $.ajax({
            url: "{{ route('traffic-ap.input.get-floors', [], false) }}",
            type: 'GET',
            data: {
                id_project: projectId,
                id_lokasi: lokasiId,
                tanggal: tanggal
            },
            success: function(response) {
                $('#loading-state').addClass('d-none');

                if (response.success && response.floors.length > 0) {
                    var tbody = $('#traffic_tbody');
                    tbody.empty();

                    var hasAnyData = false;

                    response.floors.forEach(function(item, index) {
                        if (item.has_data) {
                            hasAnyData = true;
                        }

                        var apBadges = '';
                        if (item.ap_names && item.ap_names.length > 0) {
                            apBadges = item.ap_names.map(function(name) {
                                return `<span class="badge badge-secondary border mr-1 mb-1 font-weight-normal text-dark"><i class="fas fa-wifi text-primary mr-1"></i>${name}</span>`;
                            }).join('');
                        } else {
                            apBadges = '<span class="text-muted font-italic text-xs">-</span>';
                        }

                        var unitOptions = ['KB', 'MB', 'GB', 'TB'].map(function(u) {
                            var sel = (item.traffic_unit === u) ? 'selected' : '';
                            return `<option value="${u}" ${sel}>${u}</option>`;
                        }).join('');

                        var rowHtml = `
                            <tr class="traffic-row">
                                <td class="text-center font-weight-bold align-middle">${index + 1}</td>
                                <td class="align-middle">
                                    <input type="hidden" name="entries[${index}][nama_lantai]" value="${item.nama_lantai}">
                                    <input type="hidden" name="entries[${index}][ap_floor_id]" value="${item.ap_floor_id || ''}">
                                    <strong class="text-primary font-weight-600" style="font-size: 1rem;">${item.nama_lantai}</strong>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" step="any" min="0" 
                                               name="entries[${index}][traffic_value]" 
                                               class="form-control traffic-input-field" 
                                               value="${item.traffic_value}" 
                                               placeholder="0.00">
                                    </div>
                                </td>
                                <td>
                                    <select name="entries[${index}][traffic_unit]" class="form-control unit-select font-weight-bold">
                                        ${unitOptions}
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex flex-wrap align-items-center">
                                        ${apBadges}
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.append(rowHtml);
                    });

                    if (hasAnyData) {
                        $('#data-status-badge').removeClass('badge-info badge-secondary').addClass('badge-success')
                            .html('<i class="fas fa-check-circle mr-1"></i> Data tersimpan termuat (Mode Edit)');
                    } else {
                        $('#data-status-badge').removeClass('badge-success badge-secondary').addClass('badge-info')
                            .html('<i class="fas fa-edit mr-1"></i> Form Baru (Belum ada data untuk tanggal ini)');
                    }

                    $('#table-toolbar').removeClass('d-none').addClass('d-flex');
                    $('#table-container').removeClass('d-none');
                } else {
                    $('#loading-state').addClass('d-none');
                    $('#empty-state').removeClass('d-none').html(`
                        <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                        <h4 class="text-muted">Belum ada mapping lantai pada project dan lokasi ini</h4>
                        <p class="text-sm text-muted">Silakan lakukan mapping lantai terlebih dahulu pada menu <strong>Mapping Lantai & AP</strong>.</p>
                        <a href="{{ route('traffic-ap.mapping.index') }}?id_project=${projectId}&id_lokasi=${lokasiId}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-layer-group mr-1"></i> Buka Menu Mapping Lantai
                        </a>
                    `);
                }
            },
            error: function(xhr) {
                $('#loading-state').addClass('d-none');
                $('#empty-state').removeClass('d-none').html(`
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h4 class="text-danger">Gagal memuat data</h4>
                    <p class="text-sm text-muted">Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
                `);
            }
        });
    }

    // Auto reload when location or date changes
    $('#input_id_lokasi, #input_tanggal').on('change', function() {
        loadFloorsData();
    });

    $('.btn-load-data').on('click', function() {
        loadFloorsData();
    });

    // Quick Unit Switcher
    $(document).on('click', '.btn-quick-unit', function() {
        var unit = $(this).data('unit');
        $('.unit-select').val(unit);
    });

    // Form submit AJAX
    $('#formTrafficInput').on('submit', function(e) {
        e.preventDefault();

        var btn = $('#btn-save-traffic');
        var originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#data-status-badge').removeClass('badge-info').addClass('badge-success')
                        .html('<i class="fas fa-check-circle mr-1"></i> Data berhasil diperbarui!');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                var msg = 'Gagal menyimpan data traffic.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });

    // Initial load if location is already selected
    if ($('#input_id_lokasi').val()) {
        loadFloorsData();
    }
});
</script>
@endpush

@extends('layouts.app')

@push('pg_btn')
    <a href="{{ route('traffic-ap.input', ['id_project' => $selectedProjectId, 'id_lokasi' => $selectedLokasiId]) }}" class="btn btn-sm btn-neutral">
        <i class="fas fa-edit mr-1 text-primary"></i> Input Traffic AP
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
                    <div class="col-md-6">
                        <h3 class="mb-0">{{ $title }}</h3>
                        <p class="text-sm text-muted mb-0">Kelola mapping lantai per project & lokasi serta asosiasi perangkat Access Point (AP).</p>
                    </div>
                    <div class="col-md-6 text-md-right mt-3 mt-md-0">
                        @if($selectedLokasi && $selectedProject)
                            <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalAddFloor">
                                <i class="fas fa-plus mr-1"></i> Tambah Lantai Baru
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filter Project & Lokasi --}}
                <div class="card bg-secondary shadow-none border p-3 mb-4">
                    <form method="GET" action="{{ route('traffic-ap.mapping.index') }}" id="filterLokasiForm">
                        <div class="row align-items-center">
                            {{-- Dropdown Project --}}
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-control-label font-weight-bold">
                                        <i class="fas fa-project-diagram mr-1 text-primary"></i> Project <span class="text-danger">*</span>
                                    </label>
                                    @if($fixedProject)
                                        <input type="text" class="form-control bg-white font-weight-bold" value="{{ $selectedProject->nama_project ?? '-' }}" readonly>
                                        <input type="hidden" name="id_project" id="id_project_filter" value="{{ $selectedProjectId }}">
                                        <small class="text-info d-block mt-1"><i class="fas fa-lock mr-1"></i> Project ditentukan untuk akun Anda.</small>
                                    @else
                                        <select name="id_project" id="id_project_filter" class="form-control select2">
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
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-control-label font-weight-bold">
                                        <i class="fas fa-building mr-1 text-primary"></i> Lokasi Gedung / Site <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_lokasi" id="id_lokasi_filter" class="form-control select2">
                                        @if($lokasis->isEmpty())
                                            <option value="">Tidak ada lokasi pada project ini</option>
                                        @else
                                            @foreach($lokasis as $lok)
                                                <option value="{{ $lok->id_lokasi }}" {{ $selectedLokasiId == $lok->id_lokasi ? 'selected' : '' }}>
                                                    {{ $lok->nama_lokasi }} ({{ $lok->alamat ?: 'Tidak ada alamat' }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body pt-0">
                @if(!$selectedProject)
                    <div class="alert alert-warning py-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Belum ada project yang dapat diakses oleh akun Anda.
                    </div>
                @elseif(!$selectedLokasi)
                    <div class="alert alert-warning py-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Belum ada data lokasi pada project <strong>{{ $selectedProject->nama_project }}</strong>.
                    </div>
                @elseif($floors->isEmpty())
                    <div class="text-center py-5">
                        <div class="text-muted mb-3">
                            <i class="fas fa-layer-group fa-4x text-light"></i>
                        </div>
                        <h4 class="text-muted">Belum ada mapping lantai untuk lokasi <strong>{{ $selectedLokasi->nama_lokasi }}</strong></h4>
                        <p class="text-sm text-muted mx-auto" style="max-width: 480px;">
                            Tambahkan lantai baru dan pilih perangkat AP secara manual dari dropdown, atau gunakan fitur <strong>Auto-Detect</strong> untuk otomatis menemukan perangkat berdasarkan nama lantai.
                        </p>
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAddFloor">
                                <i class="fas fa-plus mr-1"></i> Tambah Lantai Baru
                            </button>
                        </div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 200px;">Nama Lantai</th>
                                    <th style="width: 100px;">Urutan</th>
                                    <th>Daftar AP Ter-mapping (m_perangkat)</th>
                                    <th style="width: 150px;" class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($floors as $index => $floor)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong class="text-primary" style="font-size: 1rem;">{{ $floor->nama_lantai }}</strong>
                                            @if($floor->keterangan)
                                                <small class="d-block text-muted">{{ $floor->keterangan }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $floor->urutan }}</span>
                                        </td>
                                        <td>
                                            @if($floor->perangkats->isNotEmpty())
                                                <div class="d-flex flex-wrap" style="gap: 5px;">
                                                    @foreach($floor->perangkats as $p)
                                                        @php
                                                            $jns = optional($p->jenisPerangkat)->jns_perangkat ?? 'AP';
                                                            $sn = $p->serialnumber ?: '-';
                                                        @endphp
                                                        <span class="badge badge-secondary border py-1 px-2 text-dark font-weight-normal" title="SN: {{ $sn }} | Merk: {{ $p->merk }} {{ $p->type }}">
                                                            <i class="fas fa-wifi text-primary mr-1"></i>
                                                            <strong>{{ $p->hostname ?: $p->merk }}</strong> [{{ $sn }}]
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted font-italic text-sm">Belum ada AP yang dihubungkan</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-floor"
                                                data-id="{{ $floor->id }}"
                                                data-nama="{{ $floor->nama_lantai }}"
                                                data-urutan="{{ $floor->urutan }}"
                                                data-keterangan="{{ $floor->keterangan }}"
                                                data-perangkats="{{ json_encode($floor->perangkats->pluck('id_perangkat')) }}"
                                                title="Edit Mapping">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                            <form action="{{ route('traffic-ap.mapping.destroy', $floor->id) }}" method="POST" class="d-inline form-delete-floor">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-floor" title="Hapus Lantai">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- MODAL TAMBAH LANTAI & MAPPING AP                                   --}}
{{-- ================================================================== --}}
<div class="modal fade" id="modalAddFloor" role="dialog" aria-labelledby="modalAddFloorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('traffic-ap.mapping.store') }}" method="POST" id="formAddFloor">
                @csrf
                <input type="hidden" name="id_project" value="{{ $selectedProjectId }}">
                <input type="hidden" name="id_lokasi" value="{{ $selectedLokasiId }}">
                {{-- Hidden inputs to collect checked device IDs --}}
                <div id="add_checked_device_inputs"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddFloorLabel"><i class="fas fa-plus-circle mr-1 text-primary"></i> Tambah Lantai & Mapping AP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Project & Lokasi (readonly) --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Project</label>
                                <input type="text" class="form-control bg-light font-weight-bold" value="{{ $selectedProject->nama_project ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Lokasi Gedung / Site</label>
                                <input type="text" class="form-control bg-light font-weight-bold" value="{{ $selectedLokasi->nama_lokasi ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Nama Lantai & Urutan --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-control-label">Nama Lantai <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lantai" id="add_nama_lantai" class="form-control" placeholder="Contoh: LT1, LANTAI 2, LANTAI GF" required>
                                <small class="text-muted">Nama lantai akan otomatis diubah ke huruf kapital.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Urutan Tampilan</label>
                                <input type="number" name="urutan" class="form-control" value="{{ ($floors->max('urutan') ?? 0) + 1 }}" min="0">
                            </div>
                        </div>
                    </div>

                    {{-- Pilih Mode Perangkat: Tab Auto-Detect vs Manual --}}
                    <div class="form-group mb-2">
                        <label class="form-control-label d-block">Pilih Perangkat AP</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-info active" style="flex: 1;">
                                <input type="radio" name="device_mode" id="mode_auto" value="auto" checked>
                                <i class="fas fa-magic mr-1"></i> Auto-Detect dari Data Perangkat
                            </label>
                            <label class="btn btn-outline-primary" style="flex: 1;">
                                <input type="radio" name="device_mode" id="mode_manual" value="manual">
                                <i class="fas fa-list mr-1"></i> Pilih Manual dari Dropdown
                            </label>
                        </div>
                    </div>

                    {{-- ===== PANEL AUTO-DETECT ===== --}}
                    <div id="panel_auto_detect" class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Ketik nama lantai di atas, lalu klik tombol <strong>Auto-Detect</strong> untuk mencari perangkat yang terdaftar pada lantai tersebut.
                            </small>
                        </div>
                        <button type="button" class="btn btn-info btn-sm mb-3" id="btn_auto_detect" disabled>
                            <i class="fas fa-search mr-1"></i> Auto-Detect Perangkat
                            <span class="spinner-border spinner-border-sm ml-1 d-none" id="auto_detect_spinner" role="status"></span>
                        </button>

                        {{-- Hasil auto-detect akan muncul di sini --}}
                        <div id="auto_detect_results">
                            <div class="text-center text-muted py-3" id="auto_detect_placeholder">
                                <i class="fas fa-wifi fa-2x text-light mb-2 d-block"></i>
                                Masukkan nama lantai terlebih dahulu, lalu klik "Auto-Detect Perangkat".
                            </div>
                        </div>
                    </div>

                    {{-- ===== PANEL MANUAL DROPDOWN ===== --}}
                    <div id="panel_manual_select" class="mb-3" style="display: none;">
                        <select name="perangkat_ids_manual[]" id="add_perangkat_manual" class="form-control selectpicker" multiple data-live-search="true" data-size="6" data-actions-box="true" title="Ketik untuk mencari perangkat AP...">
                            @foreach($availableDevices as $d)
                                @php
                                    $jns = optional($d->jenisPerangkat)->jns_perangkat ?? 'Perangkat';
                                    $sn = $d->serialnumber ?? '-';
                                    $host = $d->hostname ? " ({$d->hostname})" : '';
                                    $lt = $d->lantai ? " [Lantai: {$d->lantai}]" : '';
                                @endphp
                                <option value="{{ $d->id_perangkat }}" data-subtext="SN: {{ $sn }}{{ $lt }}">
                                    {{ $jns }} - {{ $d->merk }} {{ $d->type }}{{ $host }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Gunakan kotak pencarian di dalam dropdown untuk mencari perangkat AP.</small>
                    </div>

                    {{-- Keterangan --}}
                    <div class="form-group">
                        <label class="form-control-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan opsional mengenai lantai ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_add">
                        <i class="fas fa-save mr-1"></i> Simpan Lantai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- MODAL EDIT LANTAI & MAPPING AP                                     --}}
{{-- ================================================================== --}}
<div class="modal fade" id="modalEditFloor" role="dialog" aria-labelledby="modalEditFloorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formEditFloor" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_project" value="{{ $selectedProjectId }}">
                <div id="edit_checked_device_inputs"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditFloorLabel"><i class="fas fa-edit mr-1 text-primary"></i> Edit Mapping Lantai & AP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Project</label>
                                <input type="text" class="form-control bg-light font-weight-bold" value="{{ $selectedProject->nama_project ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Lokasi Gedung / Site</label>
                                <input type="text" class="form-control bg-light font-weight-bold" value="{{ $selectedLokasi->nama_lokasi ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-control-label">Nama Lantai <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lantai" id="edit_nama_lantai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Urutan Tampilan</label>
                                <input type="number" name="urutan" id="edit_urutan" class="form-control" min="0">
                            </div>
                        </div>
                    </div>

                    {{-- Edit: Tab Mode --}}
                    <div class="form-group mb-2">
                        <label class="form-control-label d-block">Pilih Perangkat AP</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-info" style="flex: 1;">
                                <input type="radio" name="edit_device_mode" id="edit_mode_auto" value="auto">
                                <i class="fas fa-magic mr-1"></i> Auto-Detect dari Data Perangkat
                            </label>
                            <label class="btn btn-outline-primary active" style="flex: 1;">
                                <input type="radio" name="edit_device_mode" id="edit_mode_manual" value="manual" checked>
                                <i class="fas fa-list mr-1"></i> Pilih Manual dari Dropdown
                            </label>
                        </div>
                    </div>

                    {{-- Edit: Auto-Detect panel --}}
                    <div id="edit_panel_auto_detect" class="border rounded p-3 mb-3 bg-light" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Klik tombol <strong>Auto-Detect</strong> untuk mencari perangkat berdasarkan nama lantai di atas.
                            </small>
                        </div>
                        <button type="button" class="btn btn-info btn-sm mb-3" id="edit_btn_auto_detect">
                            <i class="fas fa-search mr-1"></i> Auto-Detect Perangkat
                            <span class="spinner-border spinner-border-sm ml-1 d-none" id="edit_auto_detect_spinner" role="status"></span>
                        </button>

                        <div id="edit_auto_detect_results">
                            <div class="text-center text-muted py-3" id="edit_auto_detect_placeholder">
                                <i class="fas fa-wifi fa-2x text-light mb-2 d-block"></i>
                                Klik "Auto-Detect Perangkat" untuk mencari perangkat pada lantai ini.
                            </div>
                        </div>
                    </div>

                    {{-- Edit: Manual dropdown panel --}}
                    <div id="edit_panel_manual_select" class="mb-3">
                        <select name="perangkat_ids[]" id="edit_perangkat_ids" class="form-control selectpicker" multiple data-live-search="true" data-size="6" data-actions-box="true" title="Ketik untuk mencari perangkat AP...">
                            @foreach($availableDevices as $d)
                                @php
                                    $jns = optional($d->jenisPerangkat)->jns_perangkat ?? 'Perangkat';
                                    $sn = $d->serialnumber ?? '-';
                                    $host = $d->hostname ? " ({$d->hostname})" : '';
                                    $lt = $d->lantai ? " [Lantai: {$d->lantai}]" : '';
                                @endphp
                                <option value="{{ $d->id_perangkat }}" data-subtext="SN: {{ $sn }}{{ $lt }}">
                                    {{ $jns }} - {{ $d->merk }} {{ $d->type }}{{ $host }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Gunakan kotak pencarian di dalam dropdown untuk mencari perangkat AP.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_edit">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
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

/* Checklist styling */
.device-checklist {
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: .375rem;
    background: #fff;
}
.device-checklist .device-item {
    padding: 0.6rem 0.85rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    transition: background-color 0.15s;
}
.device-checklist .device-item:last-child {
    border-bottom: none;
}
.device-checklist .device-item:hover {
    background-color: #f7fafc;
}
.device-checklist .device-item label {
    margin-bottom: 0;
    cursor: pointer;
    flex: 1;
    font-size: 0.875rem;
}
.device-checklist .device-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    cursor: pointer;
    flex-shrink: 0;
}
.device-checklist .device-item .device-meta {
    font-size: 0.78rem;
    color: #8898aa;
}
.check-all-bar {
    padding: 0.5rem 0.85rem;
    background: #f1f3f5;
    border-bottom: 2px solid #dee2e6;
    border-radius: .375rem .375rem 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // ===================== SELECT2 INITS =====================
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Init for Add modal manual dropdown
    function initAddSelectpicker() {
        $('#add_perangkat_manual').selectpicker('refresh');
    }

    // Init for Edit modal manual dropdown
    function initEditSelectpicker() {
        $('#edit_perangkat_ids').selectpicker('refresh');
    }

    $('#modalAddFloor').on('shown.bs.modal', function() { initAddSelectpicker(); });
    $('#modalEditFloor').on('shown.bs.modal', function() { initEditSelectpicker(); });

    // Reset Add modal state when closed
    $('#modalAddFloor').on('hidden.bs.modal', function() {
        $('#add_nama_lantai').val('');
        $('#auto_detect_results').html($('#auto_detect_placeholder').clone().show());
        $('#add_checked_device_inputs').empty();
        $('#add_perangkat_manual').selectpicker('deselectAll');
        $('#btn_auto_detect').prop('disabled', true);
        // Switch back to Auto-Detect tab
        $('input[name="device_mode"][value="auto"]').prop('checked', true).closest('label').addClass('active').siblings().removeClass('active');
        $('#panel_auto_detect').show();
        $('#panel_manual_select').hide();
    });

    // ===================== FILTER FORM =====================
    $('#id_project_filter').on('change', function() {
        $('#id_lokasi_filter').val('');
        showLoading('Memuat Lokasi...', 'Mengambil data lokasi untuk project terpilih...');
        $('#filterLokasiForm').submit();
    });

    $('#id_lokasi_filter').on('change', function() {
        showLoading('Memuat Data Lantai...', 'Mengambil mapping lantai dan perangkat lokasi...');
        $('#filterLokasiForm').submit();
    });

    // ===================== ADD MODAL: TAB SWITCHING =====================
    $('input[name="device_mode"]').on('change', function() {
        if ($(this).val() === 'auto') {
            $('#panel_auto_detect').show();
            $('#panel_manual_select').hide();
        } else {
            $('#panel_auto_detect').hide();
            $('#panel_manual_select').show();
        }
    });

    // ===================== ADD MODAL: ENABLE AUTO-DETECT BUTTON =====================
    $('#add_nama_lantai').on('input', function() {
        var val = $(this).val().trim();
        $('#btn_auto_detect').prop('disabled', val.length === 0);
    });

    // ===================== ADD MODAL: AUTO-DETECT AJAX =====================
    $('#btn_auto_detect').on('click', function() {
        var namaLantai = $('#add_nama_lantai').val().trim();
        if (!namaLantai) return;

        var btn = $(this);
        var spinner = $('#auto_detect_spinner');
        btn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: "{{ route('traffic-ap.ajax.auto-detect-devices', [], false) }}",
            type: 'GET',
            data: {
                id_project: '{{ $selectedProjectId }}',
                id_lokasi: '{{ $selectedLokasiId }}',
                nama_lantai: namaLantai
            },
            success: function(response) {
                renderChecklist(response.devices, response.count, '#auto_detect_results', 'add');
            },
            error: function() {
                $('#auto_detect_results').html(
                    '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data perangkat.</div>'
                );
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // ===================== ADD MODAL: FORM SUBMIT =====================
    $('#formAddFloor').on('submit', function() {
        var mode = $('input[name="device_mode"]:checked').val();
        var container = $('#add_checked_device_inputs');
        container.empty();

        if (mode === 'auto') {
            // Collect checked checkboxes from auto-detect panel
            $('#auto_detect_results input.device-checkbox:checked').each(function() {
                container.append('<input type="hidden" name="perangkat_ids[]" value="' + $(this).val() + '">');
            });
        } else {
            // Collect from manual select2 dropdown
            var selected = $('#add_perangkat_manual').val() || [];
            selected.forEach(function(id) {
                container.append('<input type="hidden" name="perangkat_ids[]" value="' + id + '">');
            });
        }

        showLoading('Menyimpan Lantai...', 'Mohon tunggu sebentar...');
        return true;
    });

    // ===================== EDIT MODAL: TAB SWITCHING =====================
    $('input[name="edit_device_mode"]').on('change', function() {
        if ($(this).val() === 'auto') {
            $('#edit_panel_auto_detect').show();
            $('#edit_panel_manual_select').hide();
        } else {
            $('#edit_panel_auto_detect').hide();
            $('#edit_panel_manual_select').show();
        }
    });

    // ===================== EDIT MODAL: AUTO-DETECT AJAX =====================
    $('#edit_btn_auto_detect').on('click', function() {
        var namaLantai = $('#edit_nama_lantai').val().trim();
        if (!namaLantai) {
            Swal.fire('Nama Lantai Kosong', 'Ketik nama lantai terlebih dahulu.', 'warning');
            return;
        }

        var btn = $(this);
        var spinner = $('#edit_auto_detect_spinner');
        btn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: "{{ route('traffic-ap.ajax.auto-detect-devices', [], false) }}",
            type: 'GET',
            data: {
                id_project: '{{ $selectedProjectId }}',
                id_lokasi: '{{ $selectedLokasiId }}',
                nama_lantai: namaLantai
            },
            success: function(response) {
                renderChecklist(response.devices, response.count, '#edit_auto_detect_results', 'edit');
            },
            error: function() {
                $('#edit_auto_detect_results').html(
                    '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data perangkat.</div>'
                );
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // ===================== EDIT MODAL: FORM SUBMIT =====================
    $('#formEditFloor').on('submit', function() {
        var mode = $('input[name="edit_device_mode"]:checked').val();
        var container = $('#edit_checked_device_inputs');
        container.empty();

        if (mode === 'auto') {
            $('#edit_auto_detect_results input.device-checkbox:checked').each(function() {
                container.append('<input type="hidden" name="perangkat_ids[]" value="' + $(this).val() + '">');
            });
            // Remove manual select name so it doesn't conflict
            $('#edit_perangkat_ids').attr('name', '');
        }

        showLoading('Menyimpan Perubahan...', 'Mohon tunggu sebentar...');
        return true;
    });

    // ===================== EDIT FLOOR BUTTON =====================
    $('.btn-edit-floor').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var urutan = $(this).data('urutan');
        var keterangan = $(this).data('keterangan');
        var perangkats = $(this).data('perangkats');

        var updateUrl = "{{ route('traffic-ap.mapping.update', ':id') }}".replace(':id', id);
        $('#formEditFloor').attr('action', updateUrl);
        $('#edit_nama_lantai').val(nama);
        $('#edit_urutan').val(urutan);
        $('#edit_keterangan').val(keterangan);

        // Reset to manual mode with existing selection
        $('input[name="edit_device_mode"][value="manual"]').prop('checked', true).closest('label').addClass('active').siblings().removeClass('active');
        $('#edit_panel_auto_detect').hide();
        $('#edit_panel_manual_select').show();
        $('#edit_auto_detect_results').html($('#edit_auto_detect_placeholder').clone().show());
        $('#edit_checked_device_inputs').empty();
        $('#edit_perangkat_ids').attr('name', 'perangkat_ids[]');

        $('#modalEditFloor').modal('show');

        // Set selectpicker values after modal shown
        setTimeout(function() {
            $('#edit_perangkat_ids').selectpicker('val', perangkats);
        }, 300);
    });

    // ===================== DELETE FLOOR BUTTON =====================
    $('.btn-delete-floor').on('click', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        Swal.fire({
            title: 'Hapus Lantai Ini?',
            text: "Data lantai dan mapping AP di lantai ini akan dihapus.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading('Menghapus Lantai...', 'Mohon tunggu sebentar...');
                form.submit();
            }
        });
    });

    // ===================== HELPER: RENDER CHECKLIST =====================
    function renderChecklist(devices, count, containerSelector, prefix) {
        var container = $(containerSelector);

        if (!devices || devices.length === 0) {
            container.html(
                '<div class="alert alert-warning py-3 mb-0 text-center">' +
                    '<i class="fas fa-search fa-2x text-warning mb-2 d-block"></i>' +
                    '<strong>Tidak ditemukan perangkat pada lantai ini.</strong><br>' +
                    '<small class="text-muted">Pastikan nama lantai yang diketik sesuai dengan isian kolom "lantai" pada data perangkat, atau gunakan mode <strong>Pilih Manual</strong>.</small>' +
                '</div>'
            );
            return;
        }

        var html = '';
        // Check-all bar
        html += '<div class="check-all-bar">';
        html += '  <label class="mb-0 font-weight-bold" style="cursor:pointer;">';
        html += '    <input type="checkbox" id="' + prefix + '_check_all" class="mr-2" checked> Pilih Semua';
        html += '  </label>';
        html += '  <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> ' + count + ' perangkat ditemukan</span>';
        html += '</div>';

        // Device list
        html += '<div class="device-checklist">';
        devices.forEach(function(d, idx) {
            html += '<div class="device-item">';
            html += '  <input type="checkbox" class="device-checkbox" id="' + prefix + '_dev_' + d.id + '" value="' + d.id + '" checked>';
            html += '  <label for="' + prefix + '_dev_' + d.id + '">';
            html += '    <i class="fas fa-wifi text-primary mr-1"></i>';
            html += '    <strong>' + (d.hostname !== '-' ? d.hostname : d.merk) + '</strong>';
            html += '    <span class="device-meta ml-2">SN: ' + d.serialnumber + ' &middot; ' + d.merk + ' &middot; ' + d.jenis + '</span>';
            html += '  </label>';
            html += '</div>';
        });
        html += '</div>';

        container.html(html);

        // Check-all toggle
        $('#' + prefix + '_check_all').on('change', function() {
            var checked = $(this).is(':checked');
            container.find('.device-checkbox').prop('checked', checked);
        });

        // Individual checkbox updates check-all state
        container.on('change', '.device-checkbox', function() {
            var total = container.find('.device-checkbox').length;
            var checkedCount = container.find('.device-checkbox:checked').length;
            $('#' + prefix + '_check_all').prop('checked', total === checkedCount);
        });
    }

    // ===================== HELPER: LOADING =====================
    function showLoading(title, text) {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
    }
});
</script>
@endpush

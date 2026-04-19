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
                    <div class="col text-right">
                        <button type="button" class="btn btn-sm btn-info" id="btnReplaceDevice">
                            <i class="fas fa-exchange-alt mr-1"></i> Lakukan Pergantian Perangkat
                        </button>
                        <a href="{{ route('cases.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {!! Form::model($case, ['route' => ['cases.update', $case->id_case], 'method' => 'PUT', 'id' => 'formCase', 'files' => true]) !!}
                
                <h6 class="heading-small text-muted mb-4">INFORMASI USER & PROJECT</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            @if(count(Auth::user()->projects) == 1)
                                {{ Form::select('id_project', $projects, null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'project_select']) }}
                            @else
                                {{ Form::select('id_project', ['' => 'Pilih Project...'] + $projects->toArray(), null, ['class' => 'form-control select2', 'id' => 'project_select']) }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Open Case</label>
                            <input type="datetime-local" name="open_date" class="form-control" required value="{{ date('Y-m-d\TH:i', strtotime($case->open_date)) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Closed Case</label>
                            <input type="datetime-local" name="closed_date" id="closed_date" class="form-control" value="{{ $case->closed_date ? date('Y-m-d\TH:i', strtotime($case->closed_date)) : '' }}" {{ $case->status == 'Closed' ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">DETAIL CASE</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Type</label>
                            {{ Form::select('type', ['' => 'Pilih Type...', 'Problem/Incident' => 'Problem/Incident', 'Request/Kegiatan' => 'Request/Kegiatan'], null, ['class' => 'form-control', 'required']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Subject Case</label>
                            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Subject case...', 'required']) }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Case</label>
                            {{ Form::textarea('detail', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Jelaskan detail case...', 'required']) }}
                        </div>
                    </div>
                </div>

                <div class="selection-container mt-4">
                    @php
                        $isPerangkat = !empty($case->affected_sn);
                        $isLokasi = !$isPerangkat && !empty($case->location) && $lokasis->contains('nama_lokasi', $case->location);
                        $isManual = !$isPerangkat && !$isLokasi;
                    @endphp

                    <!-- Option 1: Perangkat -->
                    <div class="row align-items-center mb-3 selection-row" data-type="perangkat">
                        <div class="col-md-5">
                            <div class="custom-control custom-radio custom-control-alternative">
                                <input name="selection_type" class="custom-control-input" id="radioPerangkat" type="radio" value="perangkat" {{ $isPerangkat ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold d-flex align-items-center" for="radioPerangkat">
                                    <span class="square-indicator"></span>
                                    Pilih Perangkat terdampak dari list
                                </label>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="text-xs text-muted mb-1 d-block">Perangkat Terdampak (Jenis - Merk - SN - Lantai/Rak)</label>
                                {{ Form::select('affected_sn', [], null, ['class' => 'form-control select2', 'id' => 'sn_select', 'data-selected' => $case->affected_sn, $isPerangkat ? '' : 'disabled']) }}
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Lokasi List -->
                    <div class="row align-items-center mb-3 selection-row" data-type="lokasi">
                        <div class="col-md-5">
                            <div class="custom-control custom-radio custom-control-alternative">
                                <input name="selection_type" class="custom-control-input" id="radioLokasi" type="radio" value="lokasi" {{ $isLokasi ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold d-flex align-items-center" for="radioLokasi">
                                    <span class="square-indicator"></span>
                                    Pilih Lokasi Terdampak dari list
                                </label>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="text-xs text-muted mb-1 d-block">Pilih Site / Lokasi Terdaftar</label>
                                <select id="location_dropdown" class="form-control select2" {{ $isLokasi ? '' : 'disabled' }}>
                                    <option value="">Pilih Lokasi...</option>
                                    @foreach($lokasis as $lok)
                                        <option value="{{ $lok->nama_lokasi }}" {{ ($case->location == $lok->nama_lokasi) ? 'selected' : '' }}>
                                            {{ $lok->nama_lokasi }} - {{ $lok->alamat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Option 3: Manual -->
                    <div class="row align-items-center mb-3 selection-row" data-type="manual">
                        <div class="col-md-5">
                            <div class="custom-control custom-radio custom-control-alternative">
                                <input name="selection_type" class="custom-control-input" id="radioManual" type="radio" value="manual" {{ $isManual ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold d-flex align-items-center" for="radioManual">
                                    <span class="square-indicator"></span>
                                    Input Lokasi Manual (Gunakan jika data perangkat tidak ditemukan di sistem)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="text-xs text-muted mb-1 d-block">Ketik Lokasi Perangkat (Manual)</label>
                                {{ Form::text('location_manual_input', $isManual ? $case->location : '', ['class' => 'form-control', 'id' => 'location_manual', 'placeholder' => 'Contoh: DC GTI - LT 20', $isManual ? '' : 'disabled']) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Severity</label>
                            {{ Form::select('severity', ['Minor' => 'Minor', 'Major' => 'Major', 'Critical' => 'Critical'], null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Status</label>
                            {{ Form::select('status', ['Open' => 'Open', 'In Progress' => 'In Progress', 'Closed' => 'Closed'], null, ['class' => 'form-control', 'id' => 'status_select']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No. Tiket Prinsipal (Opsional)</label>
                            {{ Form::text('principal_ticket_no', null, ['class' => 'form-control', 'placeholder' => 'Tiket prinsipal...']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Penyelesaian (Jika sudah Closed)</label>
                            {{ Form::textarea('resolution_detail', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Jelaskan langkah penyelesaian...']) }}
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">DOKUMENTASI & EVIDENCE</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Foto Evidence (PNG, JPG, JPEG)</label>
                            @if($case->evidence_photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $case->evidence_photo) }}" class="img-thumbnail" style="height: 100px;">
                                    <small class="d-block text-info">Foto saat ini</small>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" name="evidence_photo" class="custom-file-input" id="evidence_photo" accept="image/*">
                                <label class="custom-file-label" for="evidence_photo">Ganti Foto...</label>
                            </div>
                            <small class="text-muted">Maksimal file size: 5 MB. Kosongkan jika tidak ingin merubah.</small>
                            @error('evidence_photo') <br><small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">File RCA / Document (PDF)</label>
                            @if($case->rca_file)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $case->rca_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file-pdf mr-1"></i> Lihat PDF Saat Ini
                                    </a>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" name="rca_file" class="custom-file-input" id="rca_file" accept=".pdf">
                                <label class="custom-file-label" for="rca_file">Ganti File PDF...</label>
                            </div>
                            <small class="text-muted">Maksimal file size: 10 MB. Kosongkan jika tidak ingin merubah.</small>
                            @error('rca_file') <br><small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class="col-6 text-right offset-6 d-flex justify-content-end">
                         <a href="{{ route('cases.index') }}" class="btn btn-secondary px-5 mr-3" style="background-color: #0b5e79; color: white; border: none;">Kembali</a>
                        <button type="submit" class="btn btn-warning px-5">Update Case</button>
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Perangkat untuk Pergantian -->
<div class="modal fade" id="modalReplaceDevice" tabindex="-1" role="dialog" aria-labelledby="modalReplaceDeviceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header shadow-sm">
                <h5 class="modal-title" id="modalReplaceDeviceLabel"><i class="fas fa-list mr-2"></i> Pilih Perangkat yang Akan Diganti</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Jenis - Merk - Type</th>
                                <th>Serial Number</th>
                                <th>Lokasi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($affectedPerangkats as $p)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-dark">
                                        {{ optional($p->jenisPerangkat)->jns_perangkat ?? '-' }}
                                    </span><br>
                                    <small class="text-muted">{{ $p->merk }} {{ $p->type }}</small>
                                </td>
                                <td><code>{{ $p->serialnumber }}</code></td>
                                <td>
                                    <small class="d-block font-weight-bold">{{ $p->site }}</small>
                                    <small class="text-muted">{{ optional($p->lokasi)->nama_lokasi ?? '-' }}</small>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('pergantian.create', $p->id_perangkat) }}?id_case={{ $case->id_case }}" class="btn btn-sm btn-primary shadow-sm hover-lift">
                                        Pilih & Ganti <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-secondary">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
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

.select2-search--dropdown .select2-search__field {
    height: 40px;
    padding: .375rem .75rem;
    border-radius: .375rem;
}

/* === Custom Selection Indicators === */
.square-indicator {
    width: 25px;
    height: 25px;
    background-color: #0b4ab1;
    border-radius: 6px;
    display: inline-block;
    margin-right: 15px;
    vertical-align: middle;
    transition: all 0.2s;
    opacity: 0.3; /* Default dimmed */
}

.custom-control-input:checked ~ .custom-control-label .square-indicator {
    opacity: 1;
    box-shadow: 0 0 8px rgba(11, 74, 177, 0.5);
}

.selection-row {
    transition: background 0.3s;
    padding: 10px;
    border-radius: 8px;
}

.selection-row:hover {
    background-color: #f8f9fe;
}

.custom-control-label {
    padding-top: 2px;
    cursor: pointer;
}

.custom-control-label::before, .custom-control-label::after {
    display: none !important; /* Hide default radio circles */
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnReplaceDevice').on('click', function() {
        let count = {{ count($affectedPerangkats) }};
        
        if (count === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perangkat Tidak Ditemukan',
                text: 'Tidak ada perangkat terdaftar yang ditemukan untuk case ini (SN: {{ $case->affected_sn ?? "-" }}).',
                confirmButtonColor: '#5e72e4',
            });
        } else if (count === 1) {
            let url = "{{ route('pergantian.create', ($affectedPerangkats[0]->id_perangkat ?? 0)) }}?id_case={{ $case->id_case }}";
            window.location.href = url;
        } else {
            $('#modalReplaceDevice').modal('show');
        }
    });

    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: false
    });

    // Function to fetch SNs
    function fetchSNs(projectId) {
        if (!projectId) {
            $('#sn_select').html('<option value="">Pilih Perangkat...</option>').trigger('change');
            return;
        }

        let url = "{{ route('cases.get-sn', ':id') }}";
        url = url.replace(':id', projectId);
        let selectedSn = $('#sn_select').data('selected');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih Perangkat...</option>';
                $.each(data, function(key, value) {
                    let isSelected = (key === selectedSn) ? 'selected' : '';
                    options += '<option value="' + key + '" ' + isSelected + '>' + value + '</option>';
                });
                $('#sn_select').html(options).trigger('change');
            }
        });
    }

    // Trigger on project change
    $('#project_select').on('change', function() {
        let projectId = $(this).val();
        fetchSNs(projectId);
    });

    // If project is already selected
    let initialProject = $('#project_select').val();
    if (initialProject) {
        fetchSNs(initialProject);
    }

    // Status logic for Closed Date
    function toggleClosedDate() {
        if ($('#status_select').val() === 'Closed') {
            $('#closed_date').prop('disabled', false).prop('required', true);
            if (!$('#closed_date').val()) {
                let now = new Date();
                let year = now.getFullYear();
                let month = String(now.getMonth() + 1).padStart(2, '0');
                let day = String(now.getDate()).padStart(2, '0');
                let hours = String(now.getHours()).padStart(2, '0');
                let minutes = String(now.getMinutes()).padStart(2, '0');
                let formatted = `${year}-${month}-${day}T${hours}:${minutes}`;
                $('#closed_date').val(formatted);
            }
        } else {
            $('#closed_date').prop('disabled', true).prop('required', false).val('');
        }
    }

    $('#status_select').on('change', toggleClosedDate);
    toggleClosedDate();

    // === Selection Logic for Affected Items ===
    function handleSelectionChange() {
        let type = $('input[name="selection_type"]:checked').val();
        
        // Disable all first
        $('#sn_select').prop('disabled', true).trigger('change').attr('name', '');
        $('#location_dropdown').prop('disabled', true).trigger('change').attr('name', '');
        $('#location_manual').prop('disabled', true).attr('name', '');

        if (type === 'perangkat') {
            $('#sn_select').prop('disabled', false).attr('name', 'affected_sn');
        } else if (type === 'lokasi') {
            $('#location_dropdown').prop('disabled', false).attr('name', 'location');
        } else if (type === 'manual') {
            $('#location_manual').prop('disabled', false).attr('name', 'location');
        }
    }

    $('input[name="selection_type"]').on('change', handleSelectionChange);

    // Specific Error logic as requested by user
    $('.selection-row').on('click', function(e) {
        let type = $(this).data('type');
        let currentActive = $('input[name="selection_type"]:checked').val();
        
        if (type !== currentActive) {
            if ($(e.target).closest('.form-group').length > 0) {
                 Swal.fire({
                    icon: 'warning',
                    title: 'Akses Dibatasi',
                    text: 'Silahkan tentukan mau pilih dari list atau input manual dengan mengklik opsi di sebelah kiri.',
                    confirmButtonColor: '#0b4ab1'
                });
                return false;
            }
            $(this).find('input[name="selection_type"]').prop('checked', true).trigger('change');
        }
    });

    // Form Validation before submit
    $('#formCase').on('submit', function() {
        let type = $('input[name="selection_type"]:checked').val();
        if (type === 'perangkat' && !$('#sn_select').val()) {
            Swal.fire('Peringatan', 'Silahkan pilih perangkat dari list!', 'warning');
            return false;
        }
        if (type === 'lokasi' && !$('#location_dropdown').val()) {
            Swal.fire('Peringatan', 'Silahkan pilih lokasi dari list!', 'warning');
            return false;
        }
        if (type === 'manual' && !$('#location_manual').val()) {
            Swal.fire('Peringatan', 'Silahkan isi lokasi manual!', 'warning');
            return false;
        }
        return true;
    });

    // Initial trigger
    handleSelectionChange();
});
</script>
@endpush

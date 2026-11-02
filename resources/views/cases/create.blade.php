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
                {!! Form::open(['route' => 'cases.store', 'method' => 'POST', 'id' => 'formCase', 'files' => true]) !!}
                
                <h6 class="heading-small text-muted mb-4">INFORMASI USER & PROJECT</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project <span class="text-danger">*</span></label>
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
                            <label class="form-control-label">Tgl Open Case <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="open_date" class="form-control" required value="{{ date('Y-m-d\TH:i:s') }}" step="1">
                            @error('open_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Closed Case</label>
                            <input type="datetime-local" name="closed_date" id="closed_date" class="form-control" value="2000-01-01T00:00:00" step="1" disabled>
                            @error('closed_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4 d-flex justify-content-between align-items-center" style="cursor: pointer;" data-toggle="collapse" data-target="#collapseDetailCase" aria-expanded="true">
                    DETAIL CASE
                    <i class="fas fa-chevron-down"></i>
                </h6>

                <div class="collapse show" id="collapseDetailCase">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Type <span class="text-danger">*</span></label>
                                {{ Form::select('type', ['' => 'Pilih Type...', 'Problem/Incident' => 'Problem/Incident', 'Request/Kegiatan' => 'Request/Kegiatan', 'Relokasi/Reposisi' => 'Relokasi/Reposisi'], null, ['class' => 'form-control', 'required']) }}
                                @error('type') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-control-label">Subject Case <span class="text-danger">*</span></label>
                                {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Subject case...', 'required']) }}
                                @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-control-label">Detail Case <span class="text-danger">*</span></label>
                                {{ Form::textarea('detail', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Jelaskan detail case...', 'required']) }}
                                @error('detail') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">INFORMASI PELAPOR & CUSTOMER</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Nama Pelapor</label>
                            {{ Form::text('reporter_name', null, ['class' => 'form-control', 'placeholder' => 'Nama pelapor...']) }}
                            @error('reporter_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No Tlp/Email Pelapor</label>
                            {{ Form::text('reporter_contact', null, ['class' => 'form-control', 'placeholder' => 'No tlp atau email...']) }}
                            @error('reporter_contact') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">NCM Customer</label>
                            {{ Form::text('ncm_customer', null, ['class' => 'form-control', 'placeholder' => 'NCM Customer...']) }}
                            @error('ncm_customer') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="selection-container mt-4">
                    <!-- Option 1: Perangkat -->
                    <div class="row align-items-center mb-3 selection-row" data-type="perangkat">
                        <div class="col-md-5">
                            <div class="custom-control custom-radio custom-control-alternative">
                                <input name="selection_type" class="custom-control-input" id="radioPerangkat" type="radio" value="perangkat">
                                <label class="custom-control-label font-weight-bold d-flex align-items-center" for="radioPerangkat">
                                    <span class="square-indicator"></span>
                                    Pilih Perangkat terdampak dari list
                                </label>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="text-xs text-muted mb-1 d-block">Perangkat Terdampak (Jenis - Merk - SN - Lantai/Rak)</label>
                                {{ Form::select('affected_sn', ['' => 'Pilih Perangkat...'], null, ['class' => 'form-control select2', 'id' => 'sn_select', 'disabled']) }}
                                @error('affected_sn') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Lokasi List -->
                    <div class="row align-items-center mb-3 selection-row" data-type="lokasi">
                        <div class="col-md-5">
                            <div class="custom-control custom-radio custom-control-alternative">
                                <input name="selection_type" class="custom-control-input" id="radioLokasi" type="radio" value="lokasi">
                                <label class="custom-control-label font-weight-bold d-flex align-items-center" for="radioLokasi">
                                    <span class="square-indicator"></span>
                                    Pilih Lokasi Terdampak dari list
                                </label>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="text-xs text-muted mb-1 d-block">Pilih Site / Lokasi Terdaftar</label>
                                <select id="location_dropdown" class="form-control select2" disabled>
                                    <option value="">Pilih Lokasi...</option>
                                    @foreach($lokasis as $lok)
                                        <option value="{{ $lok->nama_lokasi }}">
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
                                <input name="selection_type" class="custom-control-input" id="radioManual" type="radio" value="manual" checked>
                                <label class="custom-control-label font-weight-bold d-flex align-items-center" for="radioManual">
                                    <span class="square-indicator"></span>
                                    Input Lokasi Manual (Gunakan jika data perangkat tidak ditemukan di sistem)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="text-xs text-muted mb-1 d-block">Ketik Lokasi Perangkat (Manual)</label>
                                {{ Form::text('location', null, ['class' => 'form-control', 'id' => 'location_manual', 'placeholder' => 'Contoh: DC GTI - LT 20']) }}
                                @error('location') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Severity <span class="text-danger">*</span></label>
                            {{ Form::select('severity', ['Minor' => 'Minor', 'Major' => 'Major', 'Critical' => 'Critical'], 'Minor', ['class' => 'form-control']) }}
                            @error('severity') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Status <span class="text-danger">*</span></label>
                            {{ Form::select('status', ['Open' => 'Open', 'In Progress' => 'In Progress', 'Closed' => 'Closed'], 'Open', ['class' => 'form-control', 'id' => 'status_select']) }}
                            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No. Tiket Prinsipal (Opsional)</label>
                            {{ Form::text('principal_ticket_no', null, ['class' => 'form-control', 'placeholder' => 'Tiket prinsipal...']) }}
                            @error('principal_ticket_no') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Penyelesaian (Jika sudah Closed)</label>
                            {{ Form::textarea('resolution_detail', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Jelaskan langkah penyelesaian...']) }}
                            @error('resolution_detail') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">DOKUMENTASI & EVIDENCE</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Foto Evidence (PNG, JPG, JPEG)</label>
                            <div class="custom-file">
                                <input type="file" name="evidence_photo" class="custom-file-input" id="evidence_photo" accept="image/*">
                                <label class="custom-file-label" for="evidence_photo">Pilih Foto...</label>
                            </div>
                            <small class="text-muted">Maksimal file size: 5 MB</small>
                            @error('evidence_photo') <br><small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">File RCA / Document (PDF)</label>
                            <div class="custom-file">
                                <input type="file" name="rca_file" class="custom-file-input" id="rca_file" accept=".pdf">
                                <label class="custom-file-label" for="rca_file">Pilih File PDF...</label>
                            </div>
                            <small class="text-muted">Maksimal file size: 10 MB</small>
                            @error('rca_file') <br><small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Upload File MOP (PDF)</label>
                            <div class="custom-file">
                                <input type="file" name="mop_file" class="custom-file-input" id="mop_file" accept=".pdf">
                                <label class="custom-file-label" for="mop_file">Pilih File MOP...</label>
                            </div>
                            <small class="text-muted">Maksimal file size: 10 MB</small>
                            @error('mop_file') <br><small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class="col-6 text-right offset-6 d-flex justify-content-end">
                        <a href="{{ route('cases.index') }}" class="btn btn-secondary px-5 mr-3" style="background-color: #0b5e79; color: white; border: none;">Batal</a>
                        <button type="submit" class="btn btn-primary px-5" style="background-color: #0b4ab1;">Simpan Case</button>
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

.fa-chevron-down {
    transition: transform 0.3s ease;
}

[aria-expanded="false"] .fa-chevron-down {
    transform: rotate(-90deg);
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

    // Function to fetch SNs
    function fetchSNs(projectId) {
        if (!projectId) {
            $('#sn_select').html('<option value="">Pilih Perangkat...</option>').trigger('change');
            return;
        }

        let url = "{{ route('cases.get-sn', ':id', false) }}";
        url = url.replace(':id', projectId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih Perangkat...</option>';
                $.each(data, function(key, value) {
                    options += '<option value="' + key + '">' + value + '</option>';
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
            if ($('#closed_date').val() === '2000-01-01T00:00') {
                let now = new Date();
                let year = now.getFullYear();
                let month = String(now.getMonth() + 1).padStart(2, '0');
                let day = String(now.getDate()).padStart(2, '0');
                let hours = String(now.getHours()).padStart(2, '0');
                let minutes = String(now.getMinutes()).padStart(2, '0');
                let seconds = String(now.getSeconds()).padStart(2, '0');
                let formatted = `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
                $('#closed_date').val(formatted);
            }
        } else {
            $('#closed_date').prop('disabled', true).prop('required', false).val('2000-01-01T00:00:00');
        }
    }

    // Initial check
    toggleClosedDate();
    $('#status_select').on('change', toggleClosedDate);

    // === Selection Logic for Affected Items ===
    function handleSelectionChange() {
        let type = $('input[name="selection_type"]:checked').val();
        
        // Reset all
        $('#sn_select').prop('disabled', true).trigger('change');
        $('#sn_select').attr('name', '');
        
        $('#location_dropdown').prop('disabled', true).trigger('change');
        $('#location_dropdown').attr('name', '');
        
        $('#location_manual').prop('disabled', true).val('');
        $('#location_manual').attr('name', '');

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
            // If they clicked the input area specifically
            if ($(e.target).closest('.form-group').length > 0) {
                 Swal.fire({
                    icon: 'warning',
                    title: 'Akses Dibatasi',
                    text: 'Silahkan tentukan mau pilih dari list atau input manual dengan mengklik opsi di sebelah kiri.',
                    confirmButtonColor: '#0b4ab1'
                });
                return false;
            }
            // Otherwise switch radio
            $(this).find('input[name="selection_type"]').prop('checked', true).trigger('change');
        }
    });

    // Initial trigger
    handleSelectionChange();

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
});
</script>
@endpush

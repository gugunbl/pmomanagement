@extends('layouts.app')

@section('hide_page_header', '1')

@section('content')
<div class="ops-page">
    <section class="ops-hero">
        <div class="ops-hero-row">
            <div>
                <div class="ops-eyebrow">Service Operations · Case Management</div>
                <h1 class="ops-title">Buat Case Baru</h1>
                <p class="ops-subtitle">Catat informasi kejadian secara lengkap agar tindak lanjut lebih cepat dan mudah ditelusuri.</p>
            </div>
            <a href="{{ route('cases.index') }}" class="ops-secondary-action"><i class="fas fa-arrow-left"></i><span>Kembali ke daftar</span></a>
        </div>
    </section>

    {!! Form::open(['route' => 'cases.store', 'method' => 'POST', 'id' => 'formCase', 'files' => true]) !!}

    <section class="ops-card">
        <div class="ops-card-header">
            <div class="ops-section-heading">
                <span class="ops-section-icon"><i class="fas fa-briefcase"></i></span>
                <div><h2 class="ops-card-title">Project dan waktu</h2><p class="ops-card-description">Tentukan project serta waktu case dibuka.</p></div>
            </div>
        </div>
        <div class="ops-card-body">
            <div class="ops-form-grid">
                <div class="ops-col-4 form-group">
                    <label class="form-control-label" for="project_select">Project <span class="text-danger">*</span></label>
                    @if($fixedProject)
                        {{ Form::select('id_project', $projects, null, ['class' => 'form-control', 'id' => 'project_select']) }}
                        <small class="text-muted">Project telah disesuaikan dengan akses akun Anda.</small>
                    @else
                        {{ Form::select('id_project', ['' => 'Pilih project…'] + $projects->toArray(), null, ['class' => 'form-control select2', 'id' => 'project_select']) }}
                    @endif
                    @error('id_project') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="ops-col-4 form-group">
                    <label class="form-control-label" for="open_date">Waktu dibuka <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="open_date" id="open_date" class="form-control" required value="{{ old('open_date', now()->format('Y-m-d\TH:i:s')) }}" step="1">
                    @error('open_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="ops-col-4 form-group">
                    <label class="form-control-label" for="closed_date">Waktu ditutup</label>
                    <input type="datetime-local" name="closed_date" id="closed_date" class="form-control" value="{{ old('closed_date', '2000-01-01T00:00:00') }}" step="1" disabled>
                    <small class="text-muted">Aktif otomatis saat status Closed.</small>
                    @error('closed_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div class="ops-section-heading">
                <span class="ops-section-icon"><i class="fas fa-clipboard-list"></i></span>
                <div><h2 class="ops-card-title">Detail case</h2><p class="ops-card-description">Tuliskan konteks utama agar case mudah dipahami oleh tim support.</p></div>
            </div>
        </div>
        <div class="ops-card-body">
            <div class="ops-form-grid">
                <div class="ops-col-4 form-group">
                    <label class="form-control-label">Jenis case <span class="text-danger">*</span></label>
                    {{ Form::select('type', ['' => 'Pilih jenis…', 'Problem/Incident' => 'Problem / Incident', 'Request/Kegiatan' => 'Request / Kegiatan', 'Relokasi/Reposisi' => 'Relokasi / Reposisi'], null, ['class' => 'form-control', 'required']) }}
                    @error('type') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="ops-col-8 form-group">
                    <label class="form-control-label">Subject case <span class="text-danger">*</span></label>
                    {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Ringkasan singkat masalah atau kegiatan', 'required']) }}
                    @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="ops-col-12 form-group mb-0">
                    <label class="form-control-label">Detail case <span class="text-danger">*</span></label>
                    {{ Form::textarea('detail', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Jelaskan kronologi, dampak, dan informasi penting lainnya…', 'required']) }}
                    @error('detail') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div class="ops-section-heading">
                <span class="ops-section-icon"><i class="fas fa-user-tie"></i></span>
                <div><h2 class="ops-card-title">Pelapor dan customer</h2><p class="ops-card-description">Informasi kontak bersifat opsional, tetapi membantu proses klarifikasi.</p></div>
            </div>
        </div>
        <div class="ops-card-body">
            <div class="ops-form-grid">
                <div class="ops-col-4 form-group mb-0"><label class="form-control-label">Nama pelapor</label>{{ Form::text('reporter_name', null, ['class' => 'form-control', 'placeholder' => 'Nama lengkap']) }}@error('reporter_name') <small class="text-danger">{{ $message }}</small> @enderror</div>
                <div class="ops-col-4 form-group mb-0"><label class="form-control-label">Telepon / email</label>{{ Form::text('reporter_contact', null, ['class' => 'form-control', 'placeholder' => 'Nomor telepon atau email']) }}@error('reporter_contact') <small class="text-danger">{{ $message }}</small> @enderror</div>
                <div class="ops-col-4 form-group mb-0"><label class="form-control-label">NCM customer</label>{{ Form::text('ncm_customer', null, ['class' => 'form-control', 'placeholder' => 'Nomor NCM customer']) }}@error('ncm_customer') <small class="text-danger">{{ $message }}</small> @enderror</div>
            </div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div class="ops-section-heading">
                <span class="ops-section-icon"><i class="fas fa-map-marker-alt"></i></span>
                <div><h2 class="ops-card-title">Objek terdampak</h2><p class="ops-card-description">Pilih satu metode yang paling sesuai untuk menentukan perangkat atau lokasi.</p></div>
            </div>
        </div>
        <div class="ops-card-body">
            <div class="ops-choice-grid">
                <div class="ops-choice" data-type="perangkat">
                    <div>
                        <input name="selection_type" class="custom-control-input" id="radioPerangkat" type="radio" value="perangkat" {{ old('selection_type') === 'perangkat' ? 'checked' : '' }}>
                        <label class="ops-choice-label" for="radioPerangkat"><span class="ops-choice-dot"></span><span>Pilih perangkat<small>Gunakan perangkat yang sudah terdaftar pada project.</small></span></label>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-control-label" for="sn_select">Jenis · merk · serial number · hostname · lantai</label>
                        {{ Form::select('affected_sn', ['' => 'Pilih perangkat…'], null, ['class' => 'form-control select2', 'id' => 'sn_select', 'disabled']) }}
                        @error('affected_sn') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="ops-choice" data-type="lokasi">
                    <div>
                        <input name="selection_type" class="custom-control-input" id="radioLokasi" type="radio" value="lokasi" {{ old('selection_type') === 'lokasi' ? 'checked' : '' }}>
                        <label class="ops-choice-label" for="radioLokasi"><span class="ops-choice-dot"></span><span>Pilih lokasi terdaftar<small>Gunakan site atau lokasi yang tersedia pada master data.</small></span></label>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-control-label" for="location_dropdown">Site / lokasi</label>
                        <select id="location_dropdown" class="form-control select2" disabled>
                            <option value="">Pilih lokasi…</option>
                            @foreach($lokasis as $lok)<option value="{{ $lok->nama_lokasi }}" {{ old('location') === $lok->nama_lokasi ? 'selected' : '' }}>{{ $lok->nama_lokasi }} - {{ $lok->alamat }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="ops-choice" data-type="manual">
                    <div>
                        <input name="selection_type" class="custom-control-input" id="radioManual" type="radio" value="manual" {{ old('selection_type', 'manual') === 'manual' ? 'checked' : '' }}>
                        <label class="ops-choice-label" for="radioManual"><span class="ops-choice-dot"></span><span>Input lokasi manual<small>Gunakan jika perangkat atau lokasi belum tersedia di sistem.</small></span></label>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-control-label" for="location_manual">Lokasi perangkat</label>
                        {{ Form::text('location', null, ['class' => 'form-control', 'id' => 'location_manual', 'placeholder' => 'Contoh: DC GTI - Lantai 20']) }}
                        @error('location') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div class="ops-section-heading">
                <span class="ops-section-icon"><i class="fas fa-tasks"></i></span>
                <div><h2 class="ops-card-title">Prioritas dan penanganan</h2><p class="ops-card-description">Tetapkan tingkat dampak, status, dan informasi tiket terkait.</p></div>
            </div>
        </div>
        <div class="ops-card-body">
            <div class="ops-form-grid">
                <div class="ops-col-4 form-group"><label class="form-control-label">Severity <span class="text-danger">*</span></label>{{ Form::select('severity', ['Minor' => 'Minor', 'Major' => 'Major', 'Critical' => 'Critical'], 'Minor', ['class' => 'form-control']) }}@error('severity') <small class="text-danger">{{ $message }}</small> @enderror</div>
                <div class="ops-col-4 form-group"><label class="form-control-label">Status <span class="text-danger">*</span></label>{{ Form::select('status', ['Open' => 'Open', 'In Progress' => 'In Progress', 'Closed' => 'Closed'], 'Open', ['class' => 'form-control', 'id' => 'status_select']) }}@error('status') <small class="text-danger">{{ $message }}</small> @enderror</div>
                <div class="ops-col-4 form-group"><label class="form-control-label">Nomor tiket prinsipal</label>{{ Form::text('principal_ticket_no', null, ['class' => 'form-control', 'placeholder' => 'Opsional']) }}@error('principal_ticket_no') <small class="text-danger">{{ $message }}</small> @enderror</div>
                <div class="ops-col-12 form-group mb-0"><label class="form-control-label">Detail penyelesaian</label>{{ Form::textarea('resolution_detail', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Isi jika case sudah atau sedang ditangani…']) }}@error('resolution_detail') <small class="text-danger">{{ $message }}</small> @enderror</div>
            </div>
        </div>
    </section>

    <section class="ops-card">
        <div class="ops-card-header">
            <div class="ops-section-heading">
                <span class="ops-section-icon"><i class="fas fa-paperclip"></i></span>
                <div><h2 class="ops-card-title">Dokumentasi dan evidence</h2><p class="ops-card-description">File baru disimpan ke folder project <strong>31_Document_Maintenance</strong> di Nextcloud.</p></div>
            </div>
        </div>
        <div class="ops-card-body">
            <div class="ops-form-grid">
                <div class="ops-col-4"><div class="ops-upload"><label class="form-control-label">Foto evidence</label><div class="custom-file"><input type="file" name="evidence_photo" class="custom-file-input" id="evidence_photo" accept="image/*"><label class="custom-file-label" for="evidence_photo">Pilih foto…</label></div><small class="text-muted">PNG/JPG, maksimal 5 MB</small>@error('evidence_photo') <br><small class="text-danger">{{ $message }}</small> @enderror</div></div>
                <div class="ops-col-4"><div class="ops-upload"><label class="form-control-label">Dokumen RCA</label><div class="custom-file"><input type="file" name="rca_file" class="custom-file-input" id="rca_file" accept=".pdf"><label class="custom-file-label" for="rca_file">Pilih PDF…</label></div><small class="text-muted">PDF, maksimal 10 MB</small>@error('rca_file') <br><small class="text-danger">{{ $message }}</small> @enderror</div></div>
                <div class="ops-col-4"><div class="ops-upload"><label class="form-control-label">File MOP</label><div class="custom-file"><input type="file" name="mop_file" class="custom-file-input" id="mop_file" accept=".pdf"><label class="custom-file-label" for="mop_file">Pilih PDF…</label></div><small class="text-muted">PDF, maksimal 10 MB</small>@error('mop_file') <br><small class="text-danger">{{ $message }}</small> @enderror</div></div>
            </div>
            <div class="ops-form-actions">
                <a href="{{ route('cases.index') }}" class="ops-button ops-button-muted">Batal</a>
                <button type="submit" class="ops-button ops-button-primary"><i class="fas fa-save"></i>Simpan Case</button>
            </div>
        </div>
    </section>

    {!! Form::close() !!}
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/case-operations.css') }}?v={{ @filemtime(public_path('assets/css/case-operations.css')) ?: '1' }}">
@endpush

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%', allowClear: false });

    function fetchSNs(projectId) {
        if (!projectId) {
            $('#sn_select').html('<option value="">Pilih perangkat…</option>').trigger('change');
            return;
        }
        var url = "{{ route('cases.get-sn', ':id', false) }}".replace(':id', projectId);
        $.get(url, function (data) {
            var options = '<option value="">Pilih perangkat…</option>';
            $.each(data, function (key, value) {
                var selected = @json(old('affected_sn')) === key ? ' selected' : '';
                options += '<option value="' + $('<div>').text(key).html() + '"' + selected + '>' + $('<div>').text(value).html() + '</option>';
            });
            $('#sn_select').html(options).trigger('change');
        });
    }

    $('#project_select').on('change', function () { fetchSNs($(this).val()); });
    if ($('#project_select').val()) { fetchSNs($('#project_select').val()); }

    function toggleClosedDate() {
        var isClosed = $('#status_select').val() === 'Closed';
        $('#closed_date').prop('disabled', !isClosed).prop('required', isClosed);
        if (isClosed && (!$('#closed_date').val() || $('#closed_date').val().indexOf('2000-01-01') === 0)) {
            var now = new Date();
            var local = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 19);
            $('#closed_date').val(local);
        } else if (!isClosed) {
            $('#closed_date').val('2000-01-01T00:00:00');
        }
    }

    function handleSelectionChange() {
        var type = $('input[name="selection_type"]:checked').val() || 'manual';
        $('.ops-choice').removeClass('is-active');
        $('.ops-choice[data-type="' + type + '"]').addClass('is-active');
        $('#sn_select').prop('disabled', type !== 'perangkat').attr('name', type === 'perangkat' ? 'affected_sn' : '');
        $('#location_dropdown').prop('disabled', type !== 'lokasi').attr('name', type === 'lokasi' ? 'location' : '');
        $('#location_manual').prop('disabled', type !== 'manual').attr('name', type === 'manual' ? 'location' : '');
    }

    $('input[name="selection_type"]').on('change', handleSelectionChange);
    $('.ops-choice').on('click', function (event) {
        if ($(event.target).closest('.select2-container, input[type="text"], select').length) { return; }
        $(this).find('input[name="selection_type"]').prop('checked', true).trigger('change');
    });
    handleSelectionChange();
    toggleClosedDate();
    $('#status_select').on('change', toggleClosedDate);

    $('.custom-file-input').on('change', function () {
        var name = this.files && this.files.length ? this.files[0].name : 'Pilih file…';
        $(this).next('.custom-file-label').text(name);
    });

    $('#formCase').on('submit', function () {
        var type = $('input[name="selection_type"]:checked').val();
        var valid = type === 'perangkat' ? $('#sn_select').val() : (type === 'lokasi' ? $('#location_dropdown').val() : $('#location_manual').val());
        if (!valid) {
            Swal.fire('Data belum lengkap', 'Pilih atau isi objek terdampak sebelum menyimpan case.', 'warning');
            return false;
        }
        return true;
    });
});
</script>
@endpush

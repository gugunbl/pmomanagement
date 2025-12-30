@extends('layouts.app')
@push('pg_btn')
    <a href="{{route('project.index')}}" class="btn btn-sm btn-neutral">All Project</a>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    {!! Form::open(['route' => 'project.store', 'files' => true]) !!}
                    <h6 class="heading-small text-muted mb-4">Project information</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('nama_project', 'Project Title / Nama Project', ['class' => 'form-control-label']) }}
                                        {{ Form::text('nama_project', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('nomor_spk', 'Nomor SPK', ['class' => 'form-control-label']) }}
                                        {{ Form::text('nomor_spk', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>


                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('tanggal_spk', 'Tanggal SPK', ['class' => 'form-control-label']) }}
                                        {{ Form::date('tanggal_spk', null, [
                                            'class' => 'form-control',
                                            'id' => 'tanggal_spk'
                                        ]) }}
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label('link_dokumen', 'Masukan Link Document', ['class' => 'form-control-label']) }}
                                        {{ Form::text('link_dokumen', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>


                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('category', 'Select Category', ['class' => 'form-control-label']) }}
                                        {{ Form::select('category', $categories, null, [ 'class'=> 'selectpicker form-control', 'placeholder' => 'Select category...']) }}
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="customer" class="form-control-label">Pilih Customer</label>
                                        <select name="customer" id="customer" class="form-control">
                                            <option value="" disabled selected>Select Customer...</option>
                                            @foreach($clients as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="sales" class="form-control-label">Pilih Sales</label>
                                        <select name="sales" id="sales" class="form-control">
                                            <option value="" disabled selected>Select Sales...</option>
                                            @foreach($saless as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


<!--
                                <div class="col-lg-12">
                                    <div class="form-group">
                                            {{ Form::label('tanggal_spk', 'Tanggal SPK3', ['class' => 'form-control-label']) }}
                                            {{ Form::text('tanggal_spk', null, [
                                                'class' => 'form-control tanggal-spk-input',
                                                'id' => 'tanggal_spk',
                                                'placeholder' => 'YYYY-MM-DD',
                                                'maxlength' => '10'
                                            ]) }}
                                            <small class="form-text text-muted">Format: YYYY-MM-DD (contoh: 2022-03-24)</small>
                                        </div>
                                    </div>

                                            -->
 
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

                            <div class="col-lg-2">
                                    <div class="form-group">
                                        {{ Form::label('status_implementation', 'Status Implementasi', ['class' => 'form-control-label']) }}
                                        {{ Form::select('status_implementation', $statusim, null, [ 'class'=> 'selectpicker form-control', 'placeholder' => 'Pilih status...']) }}
                                    </div>

                                                                                                            <div class="form-group">
                                        {{ Form::label('pm_implementasi', 'Project Manager Implementasi', ['class' => 'form-control-label']) }}
                                        {{ Form::select('pm_implementasi', $mprojectmanager, null, [ 'class'=> 'selectpicker form-control', 'placeholder' => 'Pilih Project Manager...']) }}
                                    </div>

                                    
                        </div>

                        <hr class="my-4" />
                        <div class="pl-lg-4">
        <!-- Column 1: Status Maintenance -->
        <div class="col-lg-2">
            <div class="form-group">
                                        {{ Form::label('status_maintenance', 'Status Maintenance', ['class' => 'form-control-label']) }}
                                        {{ Form::select('status_maintenance', $statusma, null, [ 'class'=> 'selectpicker form-control', 'placeholder' => 'Pilih status...']) }}
                                    </div>

        <!-- Column 2: Project Manager Maintenance -->
        <div class="col-lg-10">
                                    <div class="form-group">
                                        {{ Form::label('pm_maintenance', 'Project Manager Maintenance', ['class' => 'form-control-label']) }}
                                        {{ Form::select('pm_maintenance', $mprojectmanager, null, [ 'class'=> 'selectpicker form-control', 'placeholder' => 'Pilih Project Manager...']) }}
                                    </div>
                                </div>
                                            
                            </div>
                        </div>
                        <hr class="my-4" />

                                            -
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

.tanggal-spk-input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 80%;
    color: #dc3545;
}
</style>
@endpush
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('tanggal_spk');
    
    if (dateInput) {
        console.log('Date input element found:', dateInput); // Debug log
        
        // 1. Format otomatis saat mengetik
        dateInput.addEventListener('input', function(e) {
            console.log('Input event:', this.value); // Debug log
            
            let value = this.value.replace(/\D/g, ''); // Hapus semua non-digit
            
            // Format: YYYY-MM-DD
            if (value.length > 4) {
                // Tambahkan dash setelah tahun
                value = value.substring(0, 4) + '-' + value.substring(4);
            }
            if (value.length > 7) {
                // Tambahkan dash setelah bulan
                value = value.substring(0, 7) + '-' + value.substring(7);
            }
            
            // Batasi panjang maksimal 10 karakter (YYYY-MM-DD)
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            this.value = value;
        });
        
        // 2. Auto-tab dengan keydown (lebih responsif untuk dash otomatis)
        dateInput.addEventListener('keydown', function(e) {
            console.log('Keydown:', e.key, 'KeyCode:', e.keyCode); // Debug log
            
            // Skip untuk tombol kontrol: Backspace, Tab, Enter, Arrow keys, Delete
            if ([8, 9, 13, 37, 38, 39, 40, 46].includes(e.keyCode)) {
                return;
            }
            
            // Hanya izinkan angka (0-9) baik dari keyboard utama maupun numpad
            if (!((e.keyCode >= 48 && e.keyCode <= 57) || // 0-9
                  (e.keyCode >= 96 && e.keyCode <= 105))) { // Numpad 0-9
                e.preventDefault();
                return;
            }
            
            const cursorPos = this.selectionStart;
            const currentValue = this.value;
            
            // Auto-insert dash setelah 4 digit (tahun)
            if (cursorPos === 4 && currentValue.length === 4) {
                e.preventDefault();
                this.value = currentValue + '-' + e.key;
                this.setSelectionRange(6, 6); // Pindahkan cursor setelah dash
            }
            
            // Auto-insert dash setelah 2 digit bulan (posisi 7)
            if (cursorPos === 7 && currentValue.length === 7) {
                e.preventDefault();
                this.value = currentValue + '-' + e.key;
                this.setSelectionRange(9, 9); // Pindahkan cursor setelah dash
            }
        });
        
        // 3. Handle paste event
        dateInput.addEventListener('paste', function(e) {
            // Allow paste, lalu format setelah paste
            setTimeout(() => {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 8) {
                    this.value = value.substr(0, 4) + '-' + 
                                 value.substr(4, 2) + '-' + 
                                 value.substr(6, 2);
                }
            }, 0);
        });
        
        // 4. Validasi saat keluar dari field
        dateInput.addEventListener('blur', function() {
            const regex = /^\d{4}-\d{2}-\d{2}$/;
            if (this.value && !regex.test(this.value)) {
                this.classList.add('is-invalid');
                // Tampilkan pesan error kecil
                if (!this.nextElementSibling.classList.contains('text-danger')) {
                    const errorSpan = document.createElement('span');
                    errorSpan.className = 'text-danger small';
                    errorSpan.textContent = ' Format harus YYYY-MM-DD';
                    this.parentNode.insertBefore(errorSpan, this.nextSibling);
                }
            } else {
                this.classList.remove('is-invalid');
                // Hapus pesan error jika ada
                const errorSpan = this.parentNode.querySelector('.text-danger.small');
                if (errorSpan) {
                    errorSpan.remove();
                }
            }
        });
        
        // 5. Hapus placeholder saat fokus
        dateInput.addEventListener('focus', function() {
            if (this.value === 'YYYY-MM-DD') {
                this.value = '';
            }
        });
        
        // 6. Tambahkan placeholder saat kosong
        dateInput.addEventListener('blur', function() {
            if (!this.value) {
                this.value = 'YYYY-MM-DD';
            }
        });
        
        // Inisialisasi: jika kosong, set placeholder
        if (!dateInput.value) {
            dateInput.value = 'YYYY-MM-DD';
            dateInput.classList.add('text-muted');
        }
    } else {
        console.error('Element with id "tanggal_spk" not found!');
    }
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

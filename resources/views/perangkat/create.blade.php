@extends('layouts.app')
@push('pg_btn')
    <a href="{{route('perangkat.index')}}" class="btn btn-sm btn-neutral">Data Semua Perangkat</a>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>

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
                                        {{ Form::select('id_project', $projectes, null, [ 'class'=> 'selectpicker form-control', 'data-live-search' => 'true', 'placeholder' => 'Select Project...']) }}
                                    </div>
                                </div>


                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('jenis', 'Select Jenis Perangkat', ['class' => 'form-control-label']) }}
                                        {{ Form::select('jenis', $jenisperangkates, null, [ 'class'=> 'selectpicker form-control', 'data-live-search' => 'true', 'placeholder' => 'Select Jenis Perangkat...']) }}
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
                                        {{ Form::select('site', $sitelist, null, ['class' => 'form-control selectpicker', 'data-live-search' => 'true']) }}
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

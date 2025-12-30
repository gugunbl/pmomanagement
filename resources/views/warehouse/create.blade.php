@extends('layouts.app')

@section('content')
<div class="container">

    <h4>{{ $title }}</h4>

    <form action="{{ route('warehouse.simpan') }}" method="POST">
        @csrf

    <div class="card mb-4">
    <div class="card-body">
    
        <!-- HEADER FORM -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Jenis Document</label>
                <select name="jenis_dokumen" id="jenis_dokumen" class="form-control">
                                            <option value="" disabled selected>Pilih Jenis Document...</option>
                                            @foreach($documents as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>

            </div>
            <div class="col-md-4">
                <label>No Dokumen</label>
                <input type="text" name="no_dokumen" class="form-control" value="Auto Generate" readonly>
            </div>
            <div class="col-md-4">
                <label>Tanggal Document</label>
                <input type="date" name="tgl_dokumen" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Nama Project</label>
            {{ Form::select('id_project', $projectes, null, [ 'class'=> 'selectpicker form-control', 'data-live-search' => 'true', 'placeholder' => 'Select Project...']) }}
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Nama Pemohon</label>
                <input type="text" name="nama_pemohon" class="form-control">
            </div>
            <div class="col-md-8">
                <label>Nama Perusahaan</label>
                <select name="nama_perusahaan" id="nama_perusahaan" class="form-control">
                                            <option value="" disabled selected>Pilih Nama Perusahaan...</option>
                                            @foreach($perusahaans as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>

            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>departemen</label>
                <input type="text" name="departemen" class="form-control">
            </div>
            <div class="col-md-8">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <select name="po_do" id="po_do" class="form-control">
                                            <option value="" disabled selected>Pilih PO atau DO...</option>
                                            @foreach($podos as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>

            </div>
            <div class="col-md-8">
                <input type="text" name="nomor_po_do" class="form-control" placeholder = 'Masukan Nomor Po atau DO' >
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Lokasi Tujuan atau Asal</label>
                <input type="text" name="lokasi_tujuan_asal" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Tanggal Masuk atau Keluar</label>
                <input type="date" name="tgl_masuk_keluar" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Waktu Masuk atau Keluar</label>
                <input type="text" name="jam_masuk_keluar" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Catatan</label>
            <input type="text" name="catatan" class="form-control">
        </div>


        <!-- TABEL BARANG -->
        <table class="table table-bordered" id="table-barang">
            <thead class="table-dark">
                <tr>
                    <th>Nama Barang</th>
                    <th>Deskripsi</th>
                    <th>Serial Number</th>
                    <th>Qty</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input type="text" name="barang[0][nama_barang]" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="barang[0][deskripsi]" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="barang[0][serialnumber]" class="form-control">
                    </td>
                    <td>
                        <input type="number" name="barang[0][quantity]" class="form-control">
                    </td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm tambah-row">
                            Tambah
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- BUTTON -->
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="#" class="btn btn-secondary">Batal</a>
     
        
</div>
</div>
    </form>
</div>
@endsection



@push('scripts')
<script>
let index = 1;

$(document).on('click', '.tambah-row', function () {
    let row = `
    <tr>
        <td><input type="text" name="barang[${index}][nama_barang]" class="form-control"></td>
        <td><input type="text" name="barang[${index}][deskripsi]" class="form-control"></td>
        <td><input type="text" name="barang[${index}][serialnumber]" class="form-control"></td>
        <td><input type="number" name="barang[${index}][quantity]" class="form-control"></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm hapus-row">
                Hapus
            </button>
        </td>
    </tr>
    `;
    $('#table-barang tbody').append(row);
    index++;
});

$(document).on('click', '.hapus-row', function () {
    $(this).closest('tr').remove();
});
</script>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '{{ session('success') }}',
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

@endpush

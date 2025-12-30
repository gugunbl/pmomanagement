@extends('layouts.app')

@push('pg_btn')
    <button type="submit" form="form-edit" class="btn btn-sm btn-primary">
        <i class="fa fa-save"></i> Simpan Perubahan
    </button>

    <a href="{{ route('warehouse.show', $warehouse->no_dokumen) }}"
       class="btn btn-sm btn-neutral">
        Back
    </a>
@endpush

@section('content')
<div class="container-fluid">

<form id="form-edit"
      action="{{ route('warehouse.update', $warehouse->no_dokumen) }}"
      method="POST">
    @csrf
    @method('PUT')

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-header">Edit Transaksi Warehouse</div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>No Dokumen</label>
                    <input type="text" class="form-control"
                           value="{{ $warehouse->no_dokumen }}" readonly>
                </div>
                <div class="col-md-4">
                    <label>Tanggal Dokumen</label>
                    <input type="date" name="tgl_dokumen"
                           class="form-control"
                           value="{{ $warehouse->tgl_dokumen }}">
                </div>
                <div class="col-md-4">
                    <label>Jenis Dokumen</label>
                    <select class="form-control" disabled>
                        @foreach($documents as $k => $v)
                            <option value="{{ $k }}"
                                {{ $warehouse->jenis_dokumen == $k ? 'selected' : '' }}>
                                {{ $v }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nama Pemohon</label>
                    <input type="text" name="nama_pemohon"
                           class="form-control"
                           value="{{ $warehouse->nama_pemohon }}">
                </div>
                <div class="col-md-8">
                    <label>Nama Perusahaan</label>
                    <input type="text" name="nama_perusahaan"
                           class="form-control"
                           value="{{ $warehouse->nama_perusahaan }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Departemen</label>
                    <input type="text" name="departemen"
                           class="form-control"
                           value="{{ $warehouse->departemen }}">
                </div>
                <div class="col-md-8">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan"
                           class="form-control"
                           value="{{ $warehouse->jabatan }}">
                </div>
            </div>

        </div>
    </div>

    <!-- BARANG -->
    <div class="card">
        <div class="card-header">
            Daftar Barang
            <button type="button"
                    class="btn btn-sm btn-success float-end tambah-row">
                + Tambah
            </button>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0" id="table-barang">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Serial Number</th>
                        <th>Deskripsi</th>
                        <th width="100">Qty</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($barangs as $i => $b)
                    <tr>
                        <td>
                            <input type="text"
                                   name="barang[{{ $i }}][nama_barang]"
                                   class="form-control"
                                   value="{{ $b->nama_barang }}">
                        </td>
                        <td>
                            <input type="text"
                                   name="barang[{{ $i }}][serialnumber]"
                                   class="form-control"
                                   value="{{ $b->serialnumber }}">
                        </td>
                        <td>
                            <input type="text"
                                   name="barang[{{ $i }}][deskripsi]"
                                   class="form-control"
                                   value="{{ $b->deskripsi }}">
                        </td>
                        <td>
                            <input type="number"
                                   name="barang[{{ $i }}][quantity]"
                                   class="form-control"
                                   value="{{ $b->quantity }}">
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-danger btn-sm hapus-row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</form>

</div>
@endsection

@push('scripts')
<script>
let index = {{ $barangs->count() }};

$(document).on('click', '.tambah-row', function () {
    let row = `
    <tr>
        <td><input type="text" name="barang[${index}][nama_barang]" class="form-control"></td>
        <td><input type="text" name="barang[${index}][serialnumber]" class="form-control"></td>
        <td><input type="text" name="barang[${index}][deskripsi]" class="form-control"></td>
        <td><input type="number" name="barang[${index}][quantity]" class="form-control"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm hapus-row">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>`;
    $('#table-barang tbody').append(row);
    index++;
});

$(document).on('click', '.hapus-row', function () {
    $(this).closest('tr').remove();
});
</script>
@endpush

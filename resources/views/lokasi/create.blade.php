@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <h3 class="mb-0">Tambah Lokasi Baru</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('lokasi.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-control-label">Nama Lokasi</label>
                        <input type="text" name="nama_lokasi" class="form-control" required placeholder="e.g. GTI Ragunan, SCC Warung Buncit">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control">
                    </div>
                    <div class="text-right">
                        <a href="{{ route('lokasi.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

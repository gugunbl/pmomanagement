@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Daftar Lokasi DC/Gedung</h3>
                    </div>
                    <div class="col text-right">
                        <a href="{{ route('lokasi.create') }}" class="btn btn-sm btn-primary">Tambah Lokasi Baru</a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Lokasi</th>
                            <th>Alamat</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lokasis as $lokasi)
                        <tr>
                            <td><strong>{{ $lokasi->nama_lokasi }}</strong></td>
                            <td>{{ $lokasi->alamat }}</td>
                            <td>{{ $lokasi->keterangan }}</td>
                            <td>
                                <form action="{{ route('lokasi.destroy', $lokasi->id_lokasi) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus lokasi ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Belum ada data lokasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-4">
                {{ $lokasis->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

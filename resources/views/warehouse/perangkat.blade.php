@extends('layouts.app')

@section('content')
<div class="container-fluid">

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Perangkat Aktif di Warehouse</h5>
    </div>

    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Serial Number</th>
                    <th>Perangkat</th>
                    <th>Dokumen Masuk</th>
                    <th>Tanggal Masuk</th>
                    <th>Lokasi DC/Gedung</th>
                    <th>Rack / Koordinat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perangkats as $p)
                <tr>
                    <td><code>{{ $p->serialnumber }}</code></td>
                    <td>
                        <strong>{{ $p->merk }}</strong> {{ $p->type }}<br>
                        <small class="text-muted">{{ $p->nama_barang }}</small>
                    </td>
                    <td>{{ $p->no_dokumen }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tgl_dokumen)->format('d-m-Y') }}</td>
                    <td>{{ $p->nama_lokasi ?? $p->lokasi_tujuan_asal }}</td>
                    <td>{{ $p->lantai }} / {{ $p->koordinat }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Tidak ada perangkat di warehouse
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $perangkats->links() }}
    </div>
</div>

</div>
@endsection

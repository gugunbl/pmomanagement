@extends('layouts.app')

@push('pg_btn')
    <a href="{{ route('warehouse.pdf', $warehouse->no_dokumen) }}"
       class="btn btn-sm btn-danger">
        <i class="fa fa-file-pdf"></i> Export PDF
    </a>

    <a href="{{ route('warehouse.index') }}"
       class="btn btn-sm btn-neutral">
        Back
    </a>
@endpush

@section('content')
<div class="container-fluid">

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Detail Transaksi Warehouse</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="200">No Dokumen</th>
                    <td>: {{ $warehouse->no_dokumen }}</td>
                </tr>
                <tr>
                    <th>Tanggal Dokumen</th>
                    <td>: {{ \Carbon\Carbon::parse($warehouse->tgl_dokumen)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <th>Jenis Dokumen</th>
                    <td>
                        :
                        @if ($warehouse->jenis_dokumen == 1)
                            <span class="badge bg-primary">Barang Masuk</span>
                        @else
                            <span class="badge bg-warning text-dark">Barang Keluar</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Nama Pemohon</th>
                    <td>: {{ $warehouse->nama_pemohon }}</td>
                </tr>
                <tr>
                    <th>Nama Perusahaan</th>
                    <td>: {{ $warehouse->nama_perusahaan }}</td>
                </tr>
                <tr>
                    <th>Departemen</th>
                    <td>: {{ $warehouse->departemen }}</td>
                </tr>
                <tr>
                    <th>Jabatan</th>
                    <td>: {{ $warehouse->jabatan }}</td>
                </tr>
                <tr>
                    <th>Lokasi</th>
                    <td>: {{ $warehouse->lokasi_tujuan_asal }}</td>
                </tr>
                <tr>
                    <th>Catatan</th>
                    <td>: {{ $warehouse->catatan }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- LIST BARANG -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Daftar Barang</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Barang</th>
                        <th>Serial Number</th>
                        <th>Deskripsi</th>
                        <th width="100" class="text-center">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangs as $i => $b)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $b->nama_barang }}</td>
                            <td>{{ $b->serialnumber }}</td>
                            <td>{{ $b->deskripsi }}</td>
                            <td class="text-center">{{ $b->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Tidak ada barang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

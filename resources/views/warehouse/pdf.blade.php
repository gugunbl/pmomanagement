<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h3>Detail Transaksi Warehouse</h3>

<table style="margin-bottom:15px;">
    <tr>
        <td width="30%">No Dokumen</td>
        <td>{{ $warehouse->no_dokumen }}</td>
    </tr>
    <tr>
        <td>Tanggal Dokumen</td>
        <td>{{ $warehouse->tgl_dokumen }}</td>
    </tr>
    <tr>
        <td>Jenis Dokumen</td>
        <td>
            {{ $warehouse->jenis_dokumen == 1 ? 'Barang Masuk' : 'Barang Keluar' }}
        </td>
    </tr>
    <tr>
        <td>Nama Pemohon</td>
        <td>{{ $warehouse->nama_pemohon }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Serial Number</th>
            <th>Deskripsi</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($barangs as $i => $b)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $b->nama_barang }}</td>
            <td>{{ $b->serialnumber }}</td>
            <td>{{ $b->deskripsi }}</td>
            <td>{{ $b->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>

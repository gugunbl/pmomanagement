<h4>
Dear Pak Wida dan Team,
<p>
Izin reminder kembali untuk data project yang sudah berakhir dan akan berakhir dalam 6 bulan ke depan. Informasi ini untuk memastikan kelancaran project agar sesuai dengan timeline masing-masing project.
<p><p>
</h4>

<h3>🔴 Project Maintenance Expired</h3>

@if($expired->count())
<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Project</th>
    <th>Nomor SPK</th>
    <th>Tgl Berakhir</th>
    <th>Client</th>
</tr>
@foreach($expired as $p)
<tr>
    <td>{{ $p->nama_project }}</td>
    <td>{{ $p->nomor_spk }}</td>
    <td>{{ \Carbon\Carbon::parse($p->tanggal_akhir_maintenance)->format('d-m-Y') }}</td>
    <td>{{ $p->customer}}</td>
</tr>
@endforeach
</table>
@else
<p>- Tidak ada -</p>
@endif

<br>

<h3>🟡 Project Maintenance Akan Berakhir ≤ 5 Bulan</h3>

@if($warning->count())
<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Project</th>
    <th>Nomor SPK</th>
    <th>Tgl Berakhir</th>
    <th>Client</th>
</tr>
@foreach($warning as $p)
<tr>
    <td>{{ $p->nama_project }}</td>
    <td>{{ $p->nomor_spk }}</td>
    <td>{{ \Carbon\Carbon::parse($p->tanggal_akhir_maintenance)->format('d-m-Y') }}</td>
    <td>{{ $p->customer}}</td>
</tr>
@endforeach
</table>
@else
<p>- Tidak ada -</p>
@endif

<h4>
<p>
Demikian informasi ini kami sampaikan
<p>
Terima kasih
<br>
PMO Maintenance
</h4>
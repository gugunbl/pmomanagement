<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BA Pergantian Perangkat</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 6px;
        }
    </style>
</head>
<body>

<h3 style="text-align:center">
    BERITA ACARA PERGANTIAN PERANGKAT
</h3>

<p><strong>ID:</strong> {{ $data->id_pergantian }}</p>
<p><strong>Tanggal:</strong> {{ $data->tanggal_pergantian }}</p>
<p><strong>Project:</strong> {{ optional($data->perangkatLama->project)->nama_project }}</p>

<table>
    <tr>
        <th>Serial Lama</th>
        <th>Serial Baru</th>
    </tr>
    <tr>
        <td>{{ $data->perangkatLama->serialnumber }}</td>
        <td>{{ optional($data->perangkatBaru)->serialnumber ?? '-' }}</td>
    </tr>
</table>

<p><strong>Alasan Pergantian:</strong></p>
<p>{{ $data->alasan_penggantian }}</p>

</body>
</html>

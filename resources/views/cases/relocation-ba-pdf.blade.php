<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Draft BA Relokasi/Reposisi</title>
    <style>
        @page { margin: 28mm 22mm 22mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #222; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        .header td { border: 1px solid #555; padding: 8px; vertical-align: middle; text-align: center; }
        .brand { width: 20%; }
        .brand img { max-width: 110px; max-height: 55px; }
        .customer { width: 20%; font-weight: bold; color: #555; }
        h1 { font-size: 14pt; margin: 22px 0 14px; text-align: center; text-decoration: underline; }
        .draft { color: #a00; font-weight: bold; text-align: center; font-size: 12pt; border: 1px solid #a00; padding: 5px; margin-bottom: 15px; }
        .meta td { padding: 2px 4px; vertical-align: top; }
        .meta .label { width: 26%; }
        .meta .separator { width: 3%; }
        .paragraph { margin: 18px 0 12px; text-align: justify; }
        .equipment th, .equipment td { border: 1px solid #444; padding: 6px; }
        .equipment th { background: #e8e8e8; text-align: center; }
        .signatures { margin-top: 45px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 4px 12px; }
        .signature-space { height: 75px; }
        .muted { color: #666; font-size: 9pt; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="brand">
                <img src="{{ public_path('assets/img/idsolusi/logo.png') }}" alt="Info Data">
            </td>
            <td>
                <strong>{{ $case->project->nama_project ?? 'Nama Project' }}</strong>
            </td>
            <td class="customer">{{ $case->project->customer ?? 'CUSTOMER' }}</td>
        </tr>
    </table>

    <h1>BERITA ACARA RELOKASI / REPOSISI PERANGKAT</h1>
    <div class="draft">DRAFT - PERLU DILENGKAPI DAN DISETUJUI</div>

    <table class="meta">
        <tr><td class="label">Pekerjaan</td><td class="separator">:</td><td>{{ $case->project->nama_project ?? '-' }}</td></tr>
        <tr><td class="label">Nomor SPK</td><td class="separator">:</td><td>{{ $case->project->nomor_spk ?? '________________________________' }}</td></tr>
        <tr><td class="label">Nomor Berita Acara</td><td class="separator">:</td><td>{{ $case->ba_number ?: '________________________________' }}</td></tr>
        <tr><td class="label">Tanggal Pelaksanaan</td><td class="separator">:</td><td>{{ $case->ba_date ? \Carbon\Carbon::parse($case->ba_date)->format('d-m-Y') : '________________________________' }}</td></tr>
    </table>

    <p class="paragraph">
        Pada hari __________ tanggal __________ bulan __________ tahun __________, telah dilakukan
        relokasi/reposisi perangkat berdasarkan Case #{{ $case->id_case }} dengan rincian berikut:
    </p>

    <table class="meta">
        <tr><td class="label">Customer</td><td class="separator">:</td><td>{{ $case->project->customer ?? '________________________________' }}</td></tr>
        <tr><td class="label">PIC Customer</td><td class="separator">:</td><td>{{ $case->ba_customer_pic_name ?: ($case->reporter_name ?: '________________________________') }}{{ $case->ba_customer_pic_title ? ' ('.$case->ba_customer_pic_title.')' : '' }}</td></tr>
        <tr><td class="label">Pelaksana</td><td class="separator">:</td><td>{{ $case->ba_executor_pic_name ?: ($case->user->name ?? '________________________________') }}{{ $case->ba_executor_pic_title ? ' ('.$case->ba_executor_pic_title.')' : '' }}</td></tr>
        <tr><td class="label">Lokasi Asal</td><td class="separator">:</td><td>{{ $case->ba_origin_location ?: (optional($case->perangkat)->site ?: ($case->location ?: '________________________________')) }}</td></tr>
        <tr><td class="label">Lokasi Tujuan</td><td class="separator">:</td><td>{{ $case->ba_destination_location ?: '________________________________' }}</td></tr>
        <tr><td class="label">Subject Case</td><td class="separator">:</td><td>{{ $case->subject }}</td></tr>
    </table>

    <p class="paragraph"><strong>Rincian perangkat dan pekerjaan:</strong></p>
    <table class="equipment">
        <thead>
            <tr>
                <th style="width: 7%;">No.</th>
                <th style="width: 25%;">Perangkat</th>
                <th style="width: 20%;">Serial Number</th>
                <th style="width: 24%;">Lokasi Asal</th>
                <th style="width: 24%;">Lokasi Tujuan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td>{{ optional(optional($case->perangkat)->jenisPerangkat)->jns_perangkat ?? '________________________________' }}</td>
                <td>{{ $case->affected_sn ?: '________________________________' }}</td>
                <td>{{ $case->ba_origin_location ?: (optional($case->perangkat)->site ?: ($case->location ?: '________________________________')) }}</td>
                <td>{{ $case->ba_destination_location ?: '________________________________' }}</td>
            </tr>
        </tbody>
    </table>

    <p class="paragraph"><strong>Detail pekerjaan / keterangan:</strong><br>
        {!! nl2br(e($case->detail)) !!}
    </p>
    <p class="paragraph">
        Demikian berita acara ini dibuat sebagai bukti pelaksanaan relokasi/reposisi perangkat.
        Dokumen ini masih berupa draft dan berlaku setelah data dilengkapi serta ditandatangani para pihak.
    </p>

    <table class="signatures">
        <tr>
            <td>Pihak Customer,<div class="signature-space"></div>( {{ $case->ba_customer_pic_name ?: '__________________________' }} )<br><span class="muted">{{ $case->ba_customer_pic_title ?: 'Nama / Jabatan' }} / Tanggal</span></td>
            <td>Pihak Pelaksana,<div class="signature-space"></div>( {{ $case->ba_executor_pic_name ?: ($case->user->name ?? '__________________________') }} )<br><span class="muted">{{ $case->ba_executor_pic_title ?: 'Nama / Jabatan' }} / Tanggal</span></td>
        </tr>
    </table>
</body>
</html>

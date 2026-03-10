<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { width: 95%; margin: 20px auto; }
        h3 { color: #d9534f; border-bottom: 2px solid #d9534f; padding-bottom: 5px; margin-top: 30px; }
        h3.warning { color: #f0ad4e; border-bottom: 2px solid #f0ad4e; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 40px; font-size: 14px; }
        .dot { height: 12px; width: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .dot-red { background-color: #d9534f; }
        .dot-orange { background-color: #f0ad4e; }
    </style>
</head>
<body>
    <div class="container">
        <p>{!! nl2br(e($bodyMessage)) !!}</p>

        @if($endingSoonProjects->count() > 0)
            <h3 class="warning"><span class="dot dot-orange"></span> Project Maintenance Akan Berakhir &le; 5 Bulan</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Project</th>
                        <th style="width: 25%;">Nomor SPK</th>
                        <th style="width: 20%;">Tgl Berakhir</th>
                        <th style="width: 15%;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($endingSoonProjects as $p)
                        <tr>
                            <td>{{ $p->nama_project }}</td>
                            <td>{{ $p->nomor_spk }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_akhir_maintenance)->format('d-M-Y') }}</td>
                            <td>{{ $p->customer ?? $p->client }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($expiredProjects->count() > 0)
            <h3><span class="dot dot-red"></span> Project Maintenance Expired</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Project</th>
                        <th style="width: 25%;">Nomor SPK</th>
                        <th style="width: 20%;">Tgl Berakhir</th>
                        <th style="width: 15%;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiredProjects as $p)
                        <tr>
                            <td>{{ $p->nama_project }}</td>
                            <td>{{ $p->nomor_spk }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_akhir_maintenance)->format('d-M-Y') }}</td>
                            <td>{{ $p->customer ?? $p->client }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            Demikian saya sampaikan informasi tersebut.<br>
            <strong>Terima kasih</strong>
        </div>
    </div>
</body>
</html>
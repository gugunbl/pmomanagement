<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#243247;">
    <div style="max-width:680px;margin:0 auto;padding:24px 12px;">
        <div style="overflow:hidden;border:1px solid #e1e6ed;border-radius:14px;background:#fff;">
            <div style="padding:22px 26px;background:linear-gradient(120deg,#da251d,#7d2525);color:#fff;">
                <div style="font-size:11px;font-weight:bold;letter-spacing:1.4px;text-transform:uppercase;">PMO Maintenance</div>
                <h1 style="margin:8px 0 0;font-size:21px;line-height:1.35;">Periode laporan memasuki tanggal akhir</h1>
            </div>
            <div style="padding:24px 26px;">
                <p style="margin-top:0;line-height:1.65;">{!! nl2br(e($bodyMessage)) !!}</p>
                <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:13px;">
                    <tr><td style="width:34%;padding:9px;border-bottom:1px solid #edf0f4;color:#667085;">Project</td><td style="padding:9px;border-bottom:1px solid #edf0f4;font-weight:bold;">{{ $report->project->nama_project ?? '-' }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #edf0f4;color:#667085;">Customer</td><td style="padding:9px;border-bottom:1px solid #edf0f4;">{{ $report->project->customer ?? '-' }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #edf0f4;color:#667085;">Laporan</td><td style="padding:9px;border-bottom:1px solid #edf0f4;">#{{ $report->report_ke }}</td></tr>
                    <tr><td style="padding:9px;border-bottom:1px solid #edf0f4;color:#667085;">Periode</td><td style="padding:9px;border-bottom:1px solid #edf0f4;">{{ optional($report->period_start)->format('d M Y') }} – {{ optional($report->period_end)->format('d M Y') }}</td></tr>
                    <tr><td style="padding:9px;color:#667085;">Status</td><td style="padding:9px;text-transform:capitalize;">{{ $report->status }}</td></tr>
                </table>
                <p style="line-height:1.65;">Silakan mengatur waktu untuk pembuatan, review, dan upload laporan Preventive Maintenance.</p>
                <p style="margin:24px 0 4px;"><a href="{{ $monitoringUrl }}" style="display:inline-block;padding:11px 16px;border-radius:8px;background:#da251d;color:#fff;text-decoration:none;font-size:13px;font-weight:bold;">Buka Monitoring Laporan</a></p>
            </div>
            <div style="padding:14px 26px;background:#f8fafc;color:#7b8798;font-size:11px;">Email otomatis dari Monitoring System PMO Maintenance.</div>
        </div>
    </div>
</body>
</html>

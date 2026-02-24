<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .bg-danger-dark { background-color: #8B0000; color: white; }
        .bg-warning-custom { background-color: #FFFF00; color: black; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Project</th>
                <th>Nomor SPK</th>
                <th>PM Maintenance</th>
                <th width="50">TOP Ke</th>
                <th width="80">Start Date BA</th>
                <th width="80">End Date BA</th>
                <th width="70">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listBA as $index => $ba)
                @php
                    $highlight = '';
                    if ($ba->periode_end) {
                        $endDate = \Carbon\Carbon::parse($ba->periode_end);
                        $threeMonthsAgo = \Carbon\Carbon::now()->subMonths(3);
                        $today = \Carbon\Carbon::now();

                        if ($endDate->lt($threeMonthsAgo)) {
                            $highlight = 'bg-danger-dark';
                        } elseif ($endDate->lte($today)) {
                            $highlight = 'bg-warning-custom';
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $ba->project->nama_project ?? '-' }}</td>
                    <td>{{ $ba->project->nomor_spk ?? '-' }}</td>
                    <td>{{ $ba->project->mPMMaintenance->nama_pm ?? '-' }}</td>
                    <td class="text-center">{{ $ba->top_ke }}</td>
                    <td class="{{ $highlight }}">{{ $ba->periode_start ? \Carbon\Carbon::parse($ba->periode_start)->format('d M Y') : '-' }}</td>
                    <td class="{{ $highlight }}">{{ $ba->periode_end ? \Carbon\Carbon::parse($ba->periode_end)->format('d M Y') : '-' }}</td>
                    <td>{{ strtoupper($ba->status) }}</td>
                    <td>{{ $ba->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

@extends('layouts.app')

@section('content')
@php
    $statusOk = in_array($daily_monitoring->status, ['healthy', 'ok']);
    $indicatorOk = $daily_monitoring->health_indicator === 'normal';
    $firstImageUrl = !empty($daily_monitoring->images) ? route('daily-monitoring.image', [$daily_monitoring->id, 0]) : null;
    $waText = ($daily_monitoring->project->nama_project ?? '-') . " || " . ($daily_monitoring->site_environment_name ?? '-') . "\n"
        . $daily_monitoring->report_name . "\n"
        . "Jam " . ($daily_monitoring->report_datetime ? $daily_monitoring->report_datetime->format('H:i') : '-') . " WIB\n"
        . "================================\n"
        . "Server\n"
        . "Total Server : " . $daily_monitoring->total_server . "\n"
        . "Server ON : " . $daily_monitoring->server_on . "\n"
        . "Offline : " . $daily_monitoring->offline . "\n"
        . "Total Server OFF:-\n"
        . "================================\n"
        . "Health Indicator : Indicator " . ucfirst($daily_monitoring->health_indicator) . "\n"
        . "Server Warning : " . $ticketServerWarningCount . "\n"
        . "Alert Status :";

    if ($projectCases->isNotEmpty()) {
        foreach ($projectCases as $case) {
            $principalTicket = !empty($case['principal_ticket_no']) ? $case['principal_ticket_no'] : '-';
            $statusIcon = in_array($case['status'], ['Open', 'In Progress']) ? '[OPEN]' : '[' . strtoupper($case['status']) . ']';
            $waText .= "\n- " . $case['open_date'] . " || " . $case['subject'] . " || " . $case['lokasi_perangkat'] . " || " . $statusIcon . " || " . $principalTicket;
        }
    } else {
        $waText .= "\n- Tidak ada ticket aktif";
    }

    $waText .= "\n================================";

    if (!empty($daily_monitoring->images)) {
        $waText .= "\n\nImage Monitoring:";
        foreach ($daily_monitoring->images as $imageIndex => $image) {
            $waText .= "\n" . ($imageIndex + 1) . ". " . route('daily-monitoring.image', [$daily_monitoring->id, $imageIndex]);
        }
    }
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="card mb-5">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Detail Daily Monitoring</h3>
                <div>
                    <a href="{{ route('daily-monitoring.edit', $daily_monitoring->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <button type="button" class="btn btn-sm btn-success" id="share-wa-report-image">
                        Share WA Report Image
                    </button>
                    <a href="{{ route('daily-monitoring.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted mb-1">Project Name</label>
                        <div class="font-weight-bold">{{ $daily_monitoring->project->nama_project ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted mb-1">Site / Environment Name</label>
                        <div class="font-weight-bold">{{ $daily_monitoring->site_environment_name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted mb-1">Report Name</label>
                        <div class="font-weight-bold">{{ $daily_monitoring->report_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted mb-1">Report Tanggal dan Time</label>
                        <div class="font-weight-bold">{{ $daily_monitoring->report_datetime ? $daily_monitoring->report_datetime->format('d-m-Y H:i') : '-' }}</div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    @foreach([
                        'total_server' => 'Total Server',
                        'server_on' => 'Server ON',
                        'offline' => 'Offline',
                        'total_server_off' => 'Total Server OFF',
                    ] as $field => $label)
                        <div class="col-md-3 mb-3">
                            <label class="text-muted mb-1">{{ $label }}</label>
                            <div class="font-weight-bold">{{ $daily_monitoring->{$field} }}</div>
                        </div>
                    @endforeach
                    <div class="col-md-3 mb-3">
                        <label class="text-muted mb-1">Server Warning</label>
                        <div class="font-weight-bold">{{ $ticketServerWarningCount }}</div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted mb-1">Status</label>
                        <div>
                            <span class="badge badge-pill {{ $statusOk ? 'badge-success' : 'badge-warning' }}">
                                {{ ucwords(str_replace('_', ' ', $daily_monitoring->status)) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-muted mb-1">Health Indicator</label>
                        <div>
                            <span class="badge badge-pill {{ $indicatorOk ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($daily_monitoring->health_indicator) }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr>
                <h6 class="heading-small text-muted mb-4">Tiket Project</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Type</th>
                                <th>Tgl Open Case</th>
                                <th>Subject Case</th>
                                <th>Lokasi Perangkat</th>
                                <th>Jam & Tgl Closed</th>
                                <th>Severity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectCases as $index => $case)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $case['type'] }}</td>
                                    <td>{{ $case['open_date'] }}</td>
                                    <td>{{ $case['subject'] }}</td>
                                    <td>{{ $case['lokasi_perangkat'] }}</td>
                                    <td>{{ $case['closed_date'] }}</td>
                                    <td>{{ $case['severity'] }}</td>
                                    <td>{{ $case['status'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada tiket untuk project ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(!empty($daily_monitoring->images))
                    <hr>
                    <h6 class="heading-small text-muted mb-4">Image Monitoring</h6>
                    <div class="row">
                        @foreach($daily_monitoring->images as $imageIndex => $image)
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('daily-monitoring.image', [$daily_monitoring->id, $imageIndex]) }}" target="_blank">
                                    <img src="{{ route('daily-monitoring.image', [$daily_monitoring->id, $imageIndex]) }}" class="img-fluid rounded border shadow-sm daily-monitoring-thumb" alt="Daily monitoring image">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="dailyMonitoringCaptureContainer" style="position: absolute; left: -9999px; top: -9999px;">
    <div id="dailyMonitoringWaCapture" style="background: white; color: #111; padding: 12px; width: 620px; font-family: Arial, sans-serif; font-size: 16px; line-height: 1.35;">
        @if($firstImageUrl)
            <img src="{{ $firstImageUrl }}" style="width: 100%; max-height: 260px; object-fit: contain; margin-bottom: 10px;" alt="Daily monitoring image">
        @endif
        <div style="white-space: pre-wrap;">{{ $waText }}</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .daily-monitoring-thumb {
        height: 140px;
        width: 100%;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    $(document).ready(function() {
        var shareText = @json($waText);

        $('#share-wa-report-image').on('click', function(e) {
            e.preventDefault();

            var button = $(this);
            var originalText = button.html();
            var encodedText = encodeURIComponent(shareText);

            if (typeof html2canvas === 'undefined') {
                window.open('https://wa.me/?text=' + encodedText, '_blank');
                return;
            }

            button.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

            html2canvas(document.querySelector('#dailyMonitoringWaCapture'), {
                scale: 2,
                useCORS: true
            }).then(function(canvas) {
                canvas.toBlob(function(blob) {
                    if (navigator.clipboard && navigator.clipboard.write && window.ClipboardItem) {
                        try {
                            var item = new ClipboardItem({ 'image/png': blob });
                            navigator.clipboard.write([item]).then(function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Gambar disalin!',
                                    text: 'Teks sudah disiapkan. Anda akan dialihkan ke WhatsApp. Silahkan Paste (Ctrl+V) di kolom chat untuk melampirkan gambar.',
                                    confirmButtonColor: '#28a745',
                                    confirmButtonText: 'Buka WhatsApp'
                                }).then(function() {
                                    window.open('https://wa.me/?text=' + encodedText, '_blank');
                                    button.html(originalText).prop('disabled', false);
                                });
                            }).catch(function() {
                                fallbackDailyMonitoringDownload(canvas, encodedText, button, originalText);
                            });
                        } catch (error) {
                            fallbackDailyMonitoringDownload(canvas, encodedText, button, originalText);
                        }
                    } else {
                        fallbackDailyMonitoringDownload(canvas, encodedText, button, originalText);
                    }
                }, 'image/png');
            }).catch(function() {
                window.open('https://wa.me/?text=' + encodedText, '_blank');
                button.html(originalText).prop('disabled', false);
            });
        });

        function fallbackDailyMonitoringDownload(canvas, encodedText, button, originalText) {
            var link = document.createElement('a');
            link.download = 'Daily-Monitoring-Report.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            Swal.fire({
                icon: 'info',
                title: 'Gambar didownload',
                text: 'Browser tidak mendukung copy gambar otomatis. Gambar report sudah didownload, lanjutkan ke WhatsApp dan sisipkan manual.',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Buka WhatsApp'
            }).then(function() {
                window.open('https://wa.me/?text=' + encodedText, '_blank');
                button.html(originalText).prop('disabled', false);
            });
        }
    });
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">{{ $title }}</h3>
                    </div>
                    <div class="col text-right">
                        <a href="{{ route('interference-ratio.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                        <button type="button" id="btnShareWa" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i> Share ke WA</button>
                        @can('menu-interferance-edit')
                        <a href="{{ route('interference-ratio.edit', $ratio->id) }}" class="btn btn-sm btn-warning">Edit Data</a>
                        @endcan
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <h6 class="heading-small text-muted mb-4">Informasi Dasar & Perangkat</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            <p class="form-control-static">{{ optional($ratio->project)->nama_project ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal</label>
                            <p class="form-control-static">{{ date('d M Y', strtotime($ratio->tanggal)) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Waktu Monitor</label>
                            <p class="form-control-static">{{ $ratio->waktu }}</p>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Interference Data</h6>
                
                @foreach($ratios as $r)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group mb-2">
                            <label class="form-control-label text-primary"><i class="fas fa-wifi"></i> AP Name</label>
                            @php
                                $apParts = explode(' - ', $r->ap_name);
                                $hostname = count($apParts) >= 4 ? trim($apParts[3]) : $r->ap_name;
                                if ((empty($hostname) || $hostname == '-') && count($apParts) >= 5) {
                                    $hostname = trim($apParts[4]) != '-' ? trim($apParts[4]) : $r->ap_name;
                                }
                                if (empty($hostname) || $hostname == '-') {
                                    $hostname = $r->ap_name;
                                }
@endphp
                            <p class="form-control-static font-weight-bold ml-4 mb-0">{{ $hostname }}</p>
                            @if($hostname != $r->ap_name)
                                <small class="text-muted ml-4">{{ $r->ap_name }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-secondary shadow-none border">
                            <div class="card-body">
                                <h5 class="card-title text-center text-muted">CHANNEL</h5>
                                <div class="row text-center">
                                    <div class="col-6 border-right">
                                        <label class="form-control-label text-xs">BEFORE</label>
                                        <p class="h3 font-weight-bold mb-0 text-primary">{{ $r->channel_before }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-control-label text-xs">AFTER</label>
                                        <p class="h3 font-weight-bold mb-0 text-success">{{ $r->channel_after }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-secondary shadow-none border">
                            <div class="card-body">
                                <h5 class="card-title text-center text-muted">UTILISASI INTERFERENCE</h5>
                                <div class="row text-center">
                                    <div class="col-6 border-right">
                                        <label class="form-control-label text-xs">BEFORE</label>
                                        <p class="h3 font-weight-bold mb-0 text-danger">{{ $r->utilisasi_before }}%</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-control-label text-xs">AFTER</label>
                                        <p class="h3 font-weight-bold mb-0 text-success">{{ $r->utilisasi_after }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Keterangan</label>
                            <div class="p-3 bg-secondary rounded">
                                {!! nl2br(e($ratio->keterangan ?? '-')) !!}
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4" />
                <div class="row">
                    <div class="col-md-12 text-muted text-sm">
                        <p class="mb-0">Dibuat oleh: <strong>{{ optional($ratio->user)->name ?? 'Sistem' }}</strong> pada {{ date('d M Y H:i', strtotime($ratio->created_at)) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Elements for WA Image Capture -->
<div id="captureAreaContainer" style="position: absolute; left: -9999px; top: -9999px;">
    <div id="waCaptureTable" style="background: white; padding: 10px; width: 600px; font-family: Arial, sans-serif;">
        <table style="width: 100%; border-collapse: collapse; text-align: center; border: 2px solid black;">
            <thead>
                <tr>
                    <th rowspan="2" style="border: 1px solid black; background-color: #ffff00; padding: 8px;">AP Name</th>
                    <th colspan="2" style="border: 1px solid black; background-color: #ffff00; padding: 8px;">Channel</th>
                    <th colspan="2" style="border: 1px solid black; background-color: #ffff00; padding: 8px;">Interference Utilisasi</th>
                </tr>
                <tr>
                    <th style="border: 1px solid black; background-color: #ffff00; padding: 8px;">Before</th>
                    <th style="border: 1px solid black; background-color: #ffff00; padding: 8px;">After</th>
                    <th style="border: 1px solid black; background-color: #ffff00; padding: 8px;">Before</th>
                    <th style="border: 1px solid black; background-color: #ffff00; padding: 8px;">After</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratios as $r)
                @php
                    $apParts = explode(' - ', $r->ap_name);
                    $h = count($apParts) >= 4 ? trim($apParts[3]) : $r->ap_name;
                    if ((empty($h) || $h == '-') && count($apParts) >= 5) {
                        $h = trim($apParts[4]) != '-' ? trim($apParts[4]) : $r->ap_name;
                    }
                    if (empty($h) || $h == '-') {
                        $h = $r->ap_name;
                    }
                @endphp
                <tr>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">
                        <div style="font-weight: bold;">{{ $h }}</div>
                        @if($h != $r->ap_name)
                            <div style="font-size: 10px; color: #666;">{{ $r->ap_name }}</div>
                        @endif
                    </td>
                    <td style="border: 1px solid black; padding: 8px;">{{ $r->channel_before }}</td>
                    <td style="border: 1px solid black; padding: 8px; color: #5e72e4; font-weight: bold;">{{ $r->channel_after }}</td>
                    <td style="border: 1px solid black; padding: 8px; color: #f5365c;">{{ $r->utilisasi_before }}%</td>
                    <td style="border: 1px solid black; padding: 8px; color: #2dce89; font-weight: bold;">{{ $r->utilisasi_after }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
$(document).ready(function() {
    $('#btnShareWa').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

        // 1. Get raw template from PHP and replace variables
        var beforeList = "";
        var afterList = "";
        @foreach($ratios as $r)
        @php
            $apParts = explode(' - ', $r->ap_name);
            $hostname = count($apParts) >= 4 ? trim($apParts[3]) : $r->ap_name;
            if ((empty($hostname) || $hostname == '-') && count($apParts) >= 5) {
                $hostname = trim($apParts[4]) != '-' ? trim($apParts[4]) : $r->ap_name;
            }
            if (empty($hostname) || $hostname == '-') {
                $hostname = $r->ap_name;
            }
        @endphp
        beforeList += "- {{ $hostname }} : {{ $r->utilisasi_before }}% / Channel {{ $r->channel_before }}\n";
        afterList  += "- {{ $hostname }} : {{ $r->utilisasi_after }}% / Channel {{ $r->channel_after }}\n";
        @endforeach

        var template = `{!! addslashes(setting('wa_interference_template', "Selamat Siang,\n\nBerikut Report Adjust Channel Interference - Pukul [WAKTU]. Detail interference Ratio yang cukup tinggi sebagai berikut :\n\n[BEFORE_LIST]\n\nSetelah dilakukan penyesuaian, Rate interference nya menjadi berikut :\n\n[AFTER_LIST]\n\nBerikut laporan interference Ratio kami sampaikan.\n\nTerimakasih")) !!}`;
        
        // Handle old template placeholders specifically
        template = template.replace(/• \[AP_NAME\] : \[UTIL_BEFORE\]% \/ Channel \[CHANNEL_BEFORE\]/g, beforeList.trim());
        template = template.replace(/• \[AP_NAME\] : \[UTIL_AFTER\]% \/ Channel \[CHANNEL_AFTER\]/g, afterList.trim());
        
        // Replace list variables
        var text = template
            .replace(/\[BEFORE_LIST\]/g, beforeList.trim())
            .replace(/\[AFTER_LIST\]/g, afterList.trim())
            .replace(/\[WAKTU\]/g, "{{ $ratio->waktu }}");
            
        // Encode text
        var encodedText = encodeURIComponent(text);

        // 2. Capture Image
        html2canvas(document.querySelector("#waCaptureTable"), {
            scale: 2 // Better quality
        }).then(canvas => {
            canvas.toBlob(blob => {
                if (navigator.clipboard && navigator.clipboard.write) {
                    try {
                        const item = new ClipboardItem({ 'image/png': blob });
                        navigator.clipboard.write([item]).then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Gambar disalin!',
                                text: 'Teks sudah disiapkan. Anda akan dialihkan ke WhatsApp. Silahkan Paste (Ctrl+V) di kolom chat untuk melampirkan gambar!',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'Buka WhatsApp'
                            }).then((result) => {
                                window.open('https://wa.me/?text=' + encodedText, '_blank');
                                btn.html(originalText).prop('disabled', false);
                            });
                        }).catch(err => {
                            console.error("Clipboard Error:", err);
                            fallbackDownload(canvas, encodedText, btn, originalText);
                        });
                    } catch (e) {
                        fallbackDownload(canvas, encodedText, btn, originalText);
                    }
                } else {
                    fallbackDownload(canvas, encodedText, btn, originalText);
                }
            }, 'image/png');
        });
    });

    function fallbackDownload(canvas, encodedText, btn, originalText) {
        // Fallback if browser doesn't support Clipboard API for Images
        let link = document.createElement('a');
        link.download = 'Interference-Report.png';
        link.href = canvas.toDataURL("image/png");
        link.click();

        Swal.fire({
            icon: 'info',
            title: 'Berhasil',
            text: 'Karena browser tidak mendukung copy gambar secara otomatis, gambar telah didownload. Lanjutkan ke WA dan sisipkan secara manual ya!',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Buka WhatsApp'
        }).then((result) => {
            window.open('https://wa.me/?text=' + encodedText, '_blank');
            btn.html(originalText).prop('disabled', false);
        });
    }
});
</script>
@endpush

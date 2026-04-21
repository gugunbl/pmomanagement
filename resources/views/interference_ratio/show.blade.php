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
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">AP Name</label>
                            <p class="form-control-static font-weight-bold">{{ $ratio->ap_name }}</p>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Interference Data</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-secondary shadow-none border">
                            <div class="card-body">
                                <h5 class="card-title text-center text-muted">CHANNEL</h5>
                                <div class="row text-center">
                                    <div class="col-6 border-right">
                                        <label class="form-control-label text-xs">BEFORE</label>
                                        <p class="h3 font-weight-bold mb-0 text-primary">{{ $ratio->channel_before }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-control-label text-xs">AFTER</label>
                                        <p class="h3 font-weight-bold mb-0 text-success">{{ $ratio->channel_after }}</p>
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
                                        <p class="h3 font-weight-bold mb-0 text-danger">{{ $ratio->utilisasi_before }}%</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-control-label text-xs">AFTER</label>
                                        <p class="h3 font-weight-bold mb-0 text-success">{{ $ratio->utilisasi_after }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
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
                <tr>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">{{ $ratio->ap_name }}</td>
                    <td style="border: 1px solid black; padding: 8px;">{{ $ratio->channel_before }}</td>
                    <td style="border: 1px solid black; padding: 8px;">{{ $ratio->channel_after }}</td>
                    <td style="border: 1px solid black; padding: 8px;">{{ $ratio->utilisasi_before }}%</td>
                    <td style="border: 1px solid black; padding: 8px;">{{ $ratio->utilisasi_after }}%</td>
                </tr>
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
        var template = `{!! addslashes(setting('wa_interference_template', "Selamat Siang,\n\nBerikut Report Adjust Channel Interference - Pukul [WAKTU]. Detail interference Ratio yang cukup tinggi sebagai berikut :\n\n• [AP_NAME] : [UTIL_BEFORE]% / Channel [CHANNEL_BEFORE]\n\nSetelah dilakukan penyesuaian, Rate interference nya menjadi berikut :\n\n• [AP_NAME] : [UTIL_AFTER]% / Channel [CHANNEL_AFTER]\n\nBerikut laporan interference Ratio kami sampaikan.\n\nTerimakasih")) !!}`;
        
        var text = template
            .replace(/\[WAKTU\]/g, "{{ $ratio->waktu }}")
            .replace(/\[AP_NAME\]/g, "{{ $ratio->ap_name }}")
            .replace(/\[CHANNEL_BEFORE\]/g, "{{ $ratio->channel_before }}")
            .replace(/\[CHANNEL_AFTER\]/g, "{{ $ratio->channel_after }}")
            .replace(/\[UTIL_BEFORE\]/g, "{{ $ratio->utilisasi_before }}")
            .replace(/\[UTIL_AFTER\]/g, "{{ $ratio->utilisasi_after }}");
            
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

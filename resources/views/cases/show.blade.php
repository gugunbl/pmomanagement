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
                        <button type="button" class="btn btn-sm btn-info" id="btnReplaceDevice">
                            <i class="fas fa-exchange-alt mr-1"></i> Lakukan Pergantian Perangkat
                        </button>
                        <a href="{{ route('cases.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                        <a href="{{ route('cases.edit', $case->id_case) }}" class="btn btn-sm btn-warning">Edit</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="heading-small text-muted mb-4">Informasi User & Project</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            <p class="form-control-static">{{ optional($case->project)->nama_project ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">User Input</label>
                            <p class="form-control-static">{{ optional($case->user)->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Open Case</label>
                            <p class="form-control-static">{{ date('d-m-Y H:i', strtotime($case->open_date)) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Closed Case</label>
                            <p class="form-control-static">{{ $case->closed_date ? date('d-m-Y H:i', strtotime($case->closed_date)) : '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            @php
                                $openDate = \Carbon\Carbon::parse($case->open_date);
                                if ($case->status == 'Closed' && $case->closed_date) {
                                    $endDate = \Carbon\Carbon::parse($case->closed_date);
                                    $durasiLabel = 'Durasi Penyelesaian';
                                    $badgeClass = 'badge-success';
                                    $iconClass = 'fas fa-check-circle';
                                } else {
                                    $endDate = \Carbon\Carbon::now();
                                    $durasiLabel = 'Aging';
                                    $badgeClass = 'badge-warning';
                                    $iconClass = 'fas fa-clock';
                                }
                                $diff = $openDate->diff($endDate);
                                $parts = [];
                                if ($diff->d > 0 || $diff->m > 0 || $diff->y > 0) {
                                    $totalDays = $diff->days;
                                    $parts[] = $totalDays . ' Day' . ($totalDays > 1 ? 's' : '');
                                }
                                if ($diff->h > 0) {
                                    $parts[] = $diff->h . ' Hour' . ($diff->h > 1 ? 's' : '');
                                }
                                if ($diff->i > 0) {
                                    $parts[] = $diff->i . ' Minute' . ($diff->i > 1 ? 's' : '');
                                }
                                $durasiText = count($parts) > 0 ? implode(' ', $parts) : '0 Minutes';
                            @endphp
                            <label class="form-control-label">{{ $durasiLabel }}</label>
                            <p class="form-control-static">
                                <span class="badge {{ $badgeClass }} px-3 py-2" style="font-size: 0.85rem;">
                                    <i class="{{ $iconClass }} mr-1"></i> {{ $durasiText }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Informasi Pelapor & Customer</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Nama Pelapor</label>
                            <p class="form-control-static">{{ $case->reporter_name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No Tlp/Email Pelapor</label>
                            <p class="form-control-static">{{ $case->reporter_contact ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">NCM Customer</label>
                            <p class="form-control-static">{{ $case->ncm_customer ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Detail Case</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Type</label>
                            <p class="form-control-static">
                                @if($case->type == 'Problem/Incident')
                                    <span class="badge badge-warning">Problem/Incident</span>
                                @elseif($case->type == 'Request/Kegiatan')
                                    <span class="badge badge-info">Request/Kegiatan</span>
                                @else
                                    <span class="badge badge-secondary">-</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-control-label">Subject Case</label>
                            <p class="form-control-static">{{ $case->subject }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Perangkat Terdampak (SN)</label>
                            <p class="form-control-static">{{ $case->affected_sn ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Lokasi Perangkat</label>
                            <p class="form-control-static">{{ $case->location ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Case</label>
                            <div class="p-3 bg-secondary rounded">
                                {!! nl2br(e($case->detail)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Severity</label>
                            <p class="form-control-static">
                                <span class="badge badge-dot mr-4">
                                    @if($case->severity == 'Minor')
                                        <i class="bg-success"></i>
                                    @elseif($case->severity == 'Major')
                                        <i class="bg-warning"></i>
                                    @elseif($case->severity == 'Critical')
                                        <i class="bg-danger"></i>
                                    @else
                                        <i class="bg-info"></i>
                                    @endif
                                    <span class="status">{{ $case->severity }}</span>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Status</label>
                            <p class="form-control-static">
                                <span class="badge badge-dot mr-4">
                                    @if($case->status == 'Open')
                                        <i class="bg-danger"></i>
                                    @elseif($case->status == 'In Progress')
                                        <i class="bg-warning"></i>
                                    @elseif($case->status == 'Closed')
                                        <i class="bg-success"></i>
                                    @endif
                                    <span class="status">{{ $case->status }}</span>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No. Tiket Prinsipal</label>
                            <p class="form-control-static">{{ $case->principal_ticket_no ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Penyelesaian</label>
                            <div class="p-3 bg-secondary rounded">
                                {!! nl2br(e($case->resolution_detail ?? '-')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Dokumentasi & Evidence</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Foto Evidence</label>
                            @if($case->evidence_photo)
                                <div class="mt-2 text-center p-3 bg-secondary rounded">
                                    <a href="{{ asset('storage/' . $case->evidence_photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $case->evidence_photo) }}" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                                    </a>
                                    <small class="d-block mt-2 text-muted">Klik foto untuk memperbesar</small>
                                </div>
                            @else
                                <p class="form-control-static text-muted"><em>Tidak ada foto evidence.</em></p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">File RCA / Document</label>
                            @if($case->rca_file)
                                <div class="mt-2">
                                    <div class="card card-stats mb-4 mb-xl-0 border shadow-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </div>
                                                </div>
                                                <div class="col ml--2">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">RCA Document</h5>
                                                    <span class="h4 font-weight-bold mb-0">Attachment PDF</span>
                                                </div>
                                            </div>
                                            <p class="mt-3 mb-0 text-muted text-sm">
                                                <a href="{{ asset('storage/' . $case->rca_file) }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download mr-1"></i> Download / Lihat File
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="form-control-static text-muted"><em>Tidak ada file RCA.</em></p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">File MOP</label>
                            @if($case->mop_file)
                                <div class="mt-2">
                                    <div class="card card-stats mb-4 mb-xl-0 border shadow-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </div>
                                                </div>
                                                <div class="col ml--2">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">MOP Document</h5>
                                                    <span class="h4 font-weight-bold mb-0">Attachment PDF</span>
                                                </div>
                                            </div>
                                            <p class="mt-3 mb-0 text-muted text-sm">
                                                <a href="{{ asset('storage/' . $case->mop_file) }}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="fas fa-download mr-1"></i> Download / Lihat File
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="form-control-static text-muted"><em>Tidak ada file MOP.</em></p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- Modal Pilih Perangkat untuk Pergantian -->
<div class="modal fade" id="modalReplaceDevice" tabindex="-1" role="dialog" aria-labelledby="modalReplaceDeviceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header shadow-sm">
                <h5 class="modal-title" id="modalReplaceDeviceLabel"><i class="fas fa-list mr-2"></i> Pilih Perangkat yang Akan Diganti</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Jenis - Merk - Type</th>
                                <th>Serial Number</th>
                                <th>Lokasi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($affectedPerangkats as $p)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-dark">
                                        {{ optional($p->jenisPerangkat)->jns_perangkat ?? '-' }}
                                    </span><br>
                                    <small class="text-muted">{{ $p->merk }} {{ $p->type }}</small>
                                </td>
                                <td><code>{{ $p->serialnumber }}</code></td>
                                <td>
                                    <small class="d-block font-weight-bold">{{ $p->site }}</small>
                                    <small class="text-muted">{{ optional($p->lokasi)->nama_lokasi ?? '-' }}</small>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('pergantian.create', $p->id_perangkat) }}?id_case={{ $case->id_case }}" class="btn btn-sm btn-primary shadow-sm hover-lift">
                                        Pilih & Ganti <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-secondary">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnReplaceDevice').on('click', function() {
        let count = {{ count($affectedPerangkats) }};
        
        if (count === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perangkat Tidak Ditemukan',
                text: 'Tidak ada perangkat terdaftar yang ditemukan untuk case ini (SN: {{ $case->affected_sn ?? "-" }}).',
                confirmButtonColor: '#5e72e4',
            });
        } else if (count === 1) {
            let url = "{{ route('pergantian.create', ($affectedPerangkats[0]->id_perangkat ?? 0)) }}?id_case={{ $case->id_case }}";
            window.location.href = url;
        } else {
            $('#modalReplaceDevice').modal('show');
        }
    });
});
</script>
@endpush

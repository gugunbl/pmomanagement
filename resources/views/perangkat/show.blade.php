@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Detail Perangkat: {{ $perangkatss->serialnumber }}</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{ route('perangkat.edit', $perangkatss->id_perangkat) }}" class="btn btn-sm btn-warning">Edit Perangkat</a>
                            <a href="{{ route('perangkat.index') }}" class="btn btn-sm btn-neutral">Kembali ke List</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="heading-small text-muted mb-4">Informasi Utama</h6>
                    <div class="pl-lg-4">
                        <div class="row">
                            <div class="col-lg-6">
                                <p class="mb-1 text-sm text-muted">Project</p>
                                <h4 class="font-weight-bold">{{ $perangkatss->project->nama_project ?? '-' }}</h4>
                            </div>
                            <div class="col-lg-6">
                                <p class="mb-1 text-sm text-muted">Jenis Perangkat</p>
                                <h4>{{ $perangkatss->jenisPerangkat->jns_perangkat ?? ($perangkatss->jenis ?? '-') }}</h4>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Merk</p>
                                <h4>{{ $perangkatss->merk }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Type</p>
                                <h4>{{ $perangkatss->type }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Serial Number</p>
                                <h4 class="text-primary font-weight-bold">{{ $perangkatss->serialnumber }}</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />
                    <h6 class="heading-small text-muted mb-4">Penempatan & Spesifikasi</h6>
                    <div class="pl-lg-4">
                        <div class="row">
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Status Server</p>
                                <h4><span class="badge badge-info">{{ $perangkatss->status_server }}</span></h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Dimensi / Koordinat</p>
                                <h4>{{ $perangkatss->dimensi }} - {{ $perangkatss->koordinat }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Lokasi (DC/Gedung)</p>
                                <h4>
                                    <span class="font-weight-bold">{{ $perangkatss->site }}</span> / {{ $perangkatss->lokasi->nama_lokasi ?? '-' }}
                                    @if($perangkatss->lokasi && $perangkatss->lokasi->alamat)
                                        <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $perangkatss->lokasi->alamat }}</small>
                                    @endif
                                </h4>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Lantai / Ruangan</p>
                                <h4>{{ $perangkatss->lantai }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Type Lisensi</p>
                                <h4>{{ $perangkatss->type_lisensi }}</h4>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />
                    <h6 class="heading-small text-muted mb-4">Warranty & Dokumen</h6>
                    <div class="pl-lg-4">
                        <div class="row">
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Start Warranty</p>
                                <h4>{{ $perangkatss->start_warranty ? \Carbon\Carbon::parse($perangkatss->start_warranty)->format('d F Y') : '-' }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">End Warranty</p>
                                <h4>{{ $perangkatss->end_warranty ? \Carbon\Carbon::parse($perangkatss->end_warranty)->format('d F Y') : '-' }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1 text-sm text-muted">Tanggal Online</p>
                                <h4>{{ $perangkatss->tgl_online ? \Carbon\Carbon::parse($perangkatss->tgl_online)->format('d F Y') : '-' }}</h4>
                            </div>
                        </div>
                            <div class="col-lg-12">
                                <p class="mb-1 text-sm text-muted">Foto Perangkat</p>
                                <div class="row">
                                    @if($perangkatss->foto_1)
                                        <div class="col-md-4 mb-3">
                                            <a href="{{ asset('storage/'.$perangkatss->foto_1) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$perangkatss->foto_1) }}" class="img-fluid rounded shadow-sm border" style="max-height: 200px; object-fit: cover;" alt="Foto 1">
                                            </a>
                                        </div>
                                    @endif
                                    @if($perangkatss->foto_2)
                                        <div class="col-md-4 mb-3">
                                            <a href="{{ asset('storage/'.$perangkatss->foto_2) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$perangkatss->foto_2) }}" class="img-fluid rounded shadow-sm border" style="max-height: 200px; object-fit: cover;" alt="Foto 2">
                                            </a>
                                        </div>
                                    @endif
                                    @if($perangkatss->foto_3)
                                        <div class="col-md-4 mb-3">
                                            <a href="{{ asset('storage/'.$perangkatss->foto_3) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$perangkatss->foto_3) }}" class="img-fluid rounded shadow-sm border" style="max-height: 200px; object-fit: cover;" alt="Foto 3">
                                            </a>
                                        </div>
                                    @endif
                                    @if(!$perangkatss->foto_1 && !$perangkatss->foto_2 && !$perangkatss->foto_3)
                                        <div class="col-12">
                                            <p class="text-muted">Tidak ada foto perangkat</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                    </div>

                    <hr class="my-4" />
                    <h6 class="heading-small text-muted mb-4">Spesifikasi Detail / Keterangan</h6>
                    <div class="pl-lg-4">
                        <div class="border p-3 bg-lighter rounded">
                            {!! $perangkatss->keterangan !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

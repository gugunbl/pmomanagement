@extends('layouts.app')

@section('body_class', 'perangkat-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/perangkat.css') }}?v={{ @filemtime(public_path('assets/css/perangkat.css')) ?: '1' }}">
@endpush

@section('content')
<div class="device-workspace">
    @include('perangkat._workspace_nav', ['activeDevicePage' => 'inventory'])
    <section class="card device-panel mb-5">
        <div class="device-page-header">
            <div class="device-page-heading">
                <span class="device-page-heading__icon"><i class="fas fa-server"></i></span>
                <div>
                    <h2>{{ trim(($perangkatss->merk ?? '').' '.($perangkatss->type ?? '')) ?: 'Detail Perangkat' }}</h2>
                    <p><i class="fas fa-barcode mr-1"></i>{{ $perangkatss->serialnumber ?: 'Serial number belum tersedia' }} · {{ $perangkatss->project->nama_project ?? 'Tanpa project' }}</p>
                </div>
            </div>
            <div class="device-page-actions">
                <a href="{{ route('perangkat.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                @canany(['edit-perangkat', 'update-perangkat'])
                    <a href="{{ route('perangkat.edit', $perangkatss->id_perangkat) }}" class="btn btn-primary"><i class="fas fa-edit mr-1"></i> Edit</a>
                @endcanany
                @can('delete-perangkat')
                    {!! Form::open(['route' => ['perangkat.destroy', $perangkatss->id_perangkat], 'method' => 'delete', 'class' => 'd-inline']) !!}
                        <button type="submit" class="btn btn-outline-danger delete" aria-label="Hapus perangkat"><i class="fas fa-trash-alt"></i></button>
                    {!! Form::close() !!}
                @endcan
            </div>
        </div>

        <div class="card-body device-form-card">
            <h6 class="heading-small">Identitas & Project</h6>
            <div class="device-detail-grid mb-4">
                <div class="device-detail-item is-wide"><small>Project</small><strong>{{ $perangkatss->project->nama_project ?? '-' }}</strong></div>
                <div class="device-detail-item is-wide"><small>Jenis perangkat</small><strong>{{ $perangkatss->jenisPerangkat->jns_perangkat ?? ($perangkatss->jenis ?? '-') }}</strong></div>
                <div class="device-detail-item"><small>Merk</small><strong>{{ $perangkatss->merk ?: '-' }}</strong></div>
                <div class="device-detail-item"><small>Tipe</small><strong>{{ $perangkatss->type ?: '-' }}</strong></div>
                <div class="device-detail-item"><small>SIK</small><strong>{{ $perangkatss->sik ?: '-' }}</strong></div>
                <div class="device-detail-item"><small>Hostname</small><strong>{{ $perangkatss->hostname ?: '-' }}</strong></div>
            </div>

            <h6 class="heading-small">Penempatan & Status</h6>
            <div class="device-detail-grid mb-4">
                <div class="device-detail-item"><small>Status</small><strong><span class="badge badge-pill badge-primary">{{ $perangkatss->status_server ?: 'N/A' }}</span></strong></div>
                <div class="device-detail-item"><small>Dimensi</small><strong>{{ $perangkatss->dimensi ?: '-' }}</strong></div>
                <div class="device-detail-item"><small>Koordinat / Rak</small><strong>{{ $perangkatss->koordinat ?: '-' }}</strong></div>
                <div class="device-detail-item"><small>Lantai</small><strong>{{ $perangkatss->lantai ?: '-' }}</strong></div>
                <div class="device-detail-item is-wide"><small>Lokasi</small><strong>{{ $perangkatss->site ?: '-' }} / {{ $perangkatss->lokasi->nama_lokasi ?? '-' }}</strong></div>
                <div class="device-detail-item is-wide"><small>Alamat</small><strong>{{ $perangkatss->lokasi->alamat ?? 'Alamat belum tersedia' }}</strong></div>
                <div class="device-detail-item is-wide"><small>Lisensi / Core</small><strong>{{ $perangkatss->type_lisensi ?: '-' }}</strong></div>
            </div>

            <h6 class="heading-small">Warranty & Operasional</h6>
            <div class="device-detail-grid mb-4">
                <div class="device-detail-item"><small>Warranty mulai</small><strong>{{ $perangkatss->start_warranty ? date('d F Y', strtotime($perangkatss->start_warranty)) : '-' }}</strong></div>
                <div class="device-detail-item"><small>Warranty berakhir</small><strong>{{ $perangkatss->end_warranty ? date('d F Y', strtotime($perangkatss->end_warranty)) : '-' }}</strong></div>
                <div class="device-detail-item"><small>Tanggal online</small><strong>{{ $perangkatss->tgl_online ? date('d F Y', strtotime($perangkatss->tgl_online)) : '-' }}</strong></div>
            </div>

            <h6 class="heading-small">Foto Perangkat</h6>
            @if($perangkatss->foto_1 || $perangkatss->foto_2 || $perangkatss->foto_3)
                <div class="device-photo-grid mb-4">
                    @foreach([$perangkatss->foto_1, $perangkatss->foto_2, $perangkatss->foto_3] as $photo)
                        @if($photo)
                            <a href="{{ asset('storage/'.$photo) }}" target="_blank" rel="noopener"><img src="{{ asset('storage/'.$photo) }}" alt="Foto perangkat" loading="lazy"></a>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-4 border rounded mb-4"><i class="far fa-images fa-2x mb-2 d-block"></i>Belum ada foto perangkat.</div>
            @endif

            <h6 class="heading-small">Spesifikasi Detail / Keterangan</h6>
            <div class="border p-3 bg-lighter rounded">{!! $perangkatss->keterangan ?: '<span class="text-muted">Belum ada keterangan.</span>' !!}</div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/jquery-confirm.min.js') }}"></script>
<script>
$(function () {
    $('.delete').on('click', function (event) {
        event.preventDefault();
        var form = $(this).closest('form');
        $.confirm({
            icon: 'fas fa-exclamation-triangle',
            closeIcon: true,
            title: 'Hapus perangkat?',
            content: 'Data yang dihapus tidak dapat dikembalikan.',
            type: 'red',
            buttons: {
                confirm: { text: 'Hapus', btnClass: 'btn-red', action: function () { form.submit(); } },
                cancel: { text: 'Batal' }
            }
        });
    });
});
</script>
@endpush

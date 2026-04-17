@extends('layouts.app')

@section('content')
<div class="container"" style="margin-top: 100px;">

    <h4>Pergantian Perangkat</h4>

    <form action="{{ route('pergantian.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id_perangkat_lama" value="{{ $perangkat->id_perangkat }}">

        @if($case)
        <div class="card bg-light shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="heading-small text-muted mb-4"><i class="fas fa-ticket-alt mr-2"></i> Informasi Ticket Internal (Read Only)</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-control-label small text-uppercase">No. Ticket</label>
                            <p class="form-control-static font-weight-bold">#{{ $case->id_case }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-control-label small text-uppercase">Tgl Open Case</label>
                            <p class="form-control-static">{{ date('d M Y H:i', strtotime($case->open_date)) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-control-label small text-uppercase">Severity</label>
                            <p class="form-control-static">
                                @if($case->severity == 'Minor') <span class="badge badge-success">Minor</span>
                                @elseif($case->severity == 'Major') <span class="badge badge-warning">Major</span>
                                @elseif($case->severity == 'Critical') <span class="badge badge-danger">Critical</span>
                                @else <span class="badge badge-info">{{ $case->severity }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-2">
                            <label class="form-control-label small text-uppercase">Subject Case</label>
                            <p class="form-control-static">{{ $case->subject }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-control-label small text-uppercase">No. Ticket Prinsipal</label>
                            <p class="form-control-static">{{ $case->principal_ticket_no ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="form-group">
            <label>Perangkat Lama</label>
            <input type="text" class="form-control" 
                   value="{{ $perangkat->merk }} - {{ $perangkat->type }} ({{ $perangkat->serialnumber }})"
                   readonly>
        </div>

        <div class="form-group">
            <label>Jenis Penggantian</label>
            <select name="jenis_penggantian" id="jenis_penggantian" class="form-control" required>
                <option value="">-- Pilih --</option>
                <option value="stock">Dari Stock</option>
                <option value="rma_principal">RMA Principal</option>
            </select>
        </div>

        <div class="form-group">
            <label>Perangkat Pengganti (Jika dari Stock)</label>
            <select name="id_perangkat_baru" id="id_perangkat_baru" class="form-control" disabled>
                <option value="">-- Pilih Perangkat Stock --</option>
                @foreach($stockPerangkat as $stock)
                    <option value="{{ $stock->id_perangkat }}">
                        {{ $stock->merk }} - {{ $stock->type }} ({{ $stock->serialnumber }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Alasan Penggantian</label>
            <textarea name="alasan_penggantian" class="form-control" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label>Tanggal Penggantian</label>
            <input 
                type="date" 
                name="tanggal_pergantian" 
                class="form-control" 
                value="{{ old('tanggal_pergantian', date('Y-m-d')) }}"
                required
            >
        </div>

<hr>
<h6>Data Pelapor</h6>

<div class="form-group">
    <label>NIK / ID Karyawan Pelapor</label>
    <input type="text" name="user_pelapor_id" class="form-control"
           placeholder="Masukkan NIK / ID Karyawan"> (Opsional)
</div>

<div class="form-group">
    <label>Nama Pelapor</label>
    <input type="text" name="user_pelapor_nama" class="form-control"
           placeholder="Nama Lengkap"> (Opsional)
</div>

<div class="form-group">
    <label>Jabatan Pelapor</label>
    <input type="text" name="user_pelapor_jabatan" class="form-control"
           placeholder="Jabatan"> (Opsional)
</div>

<hr>
<h6>Data Approval</h6>

<div class="form-group">
    <label>NIK / ID Karyawan Approval</label>
    <input type="text" name="user_approval_id" class="form-control"
           placeholder="NIK / ID Karyawan Approval">
</div>

<div class="form-group">
    <label>Nama Approval</label>
    <input type="text" name="user_approval_nama" class="form-control"
           placeholder="Nama Approval">
</div>

<div class="form-group">
    <label>Jabatan Approval</label>
    <input type="text" name="user_approval_jabatan" class="form-control"
           placeholder="Jabatan Approval">
</div>
<hr>
<h6>Status</h6>

<div class="form-group">
    <select name="status" id="status" class="form-control" required>
        <option value="draft">Draft</option>
        <option value="waiting_rma">Waiting RMA</option>
        <option value="approved">Approved</option>
        <option value="done">Done</option>
        <option value="rejected">Rejected</option>
    </select>
</div>




        <button class="btn btn-primary">Simpan</button>
        <a href="{{route('perangkat.index')}}" class="btn btn-secondary">Batal/Kembali ke List Perangkat</a>

    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const jenis = document.getElementById('jenis_penggantian');
    const perangkatBaru = document.getElementById('id_perangkat_baru');

    function togglePerangkatBaru() {
        if (jenis.value === 'stock') {
            perangkatBaru.disabled = false;
        } else {
            perangkatBaru.disabled = true;
            perangkatBaru.value = '';
        }
    }

    jenis.addEventListener('change', togglePerangkatBaru);
    togglePerangkatBaru();
});
</script>
@endpush




@endsection

@extends('layouts.app')

@section('content')
<div class="container"" style="margin-top: 100px;">

    <h4>Pergantian Perangkat</h4>

    <form action="{{ route('pergantian.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id_perangkat_lama" value="{{ $perangkat->id_perangkat }}">

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
           placeholder="Masukkan NIK / ID Karyawan" required>
</div>

<div class="form-group">
    <label>Nama Pelapor</label>
    <input type="text" name="user_pelapor_nama" class="form-control"
           placeholder="Nama Lengkap" required>
</div>

<div class="form-group">
    <label>Jabatan Pelapor</label>
    <input type="text" name="user_pelapor_jabatan" class="form-control"
           placeholder="Jabatan" required>
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

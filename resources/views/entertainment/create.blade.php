@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Tambah Data Entertainment</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('entertainment.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf

                <div class="row">

                    {{-- Tanggal --}}
                    <div class="col-md-4 mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" 
                               class="form-control" required>
                    </div>

                    {{-- Karyawan --}}
                    <div class="col-md-4 mb-3">
                        <label>Karyawan</label>
                        <select name="karyawan_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id_pm }}">
                                    {{ $k->nama_pm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Project --}}
                    <div class="col-md-4 mb-3">
                        <label>Project</label>
                        <select name="project_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->nama_project }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis --}}
                    <div class="col-md-4 mb-3">
                        <label>Jenis Entertainment</label>
                        <select name="entertainment_type_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Relasi --}}
                    <div class="col-md-8 mb-3">
                        <label>Nama Relasi</label>
                        <input type="text" name="nama_relasi" 
                               class="form-control">
                    </div>

                    {{-- Keperluan --}}
                    <div class="col-md-12 mb-3">
                        <label>Keperluan</label>
                        <textarea name="keperluan" 
                                  class="form-control" rows="2"></textarea>
                    </div>

                    {{-- Perusahaan --}}
                    <div class="col-md-6 mb-3">
                        <label>Perusahaan</label>
                        <input type="text" name="perusahaan" 
                               class="form-control">
                    </div>

                    {{-- Tempat --}}
                    <div class="col-md-6 mb-3">
                        <label>Tempat</label>
                        <input type="text" name="tempat" 
                               class="form-control">
                    </div>

                    {{-- Alamat --}}
                    <div class="col-md-12 mb-3">
                        <label>Alamat</label>
                        <input type="text" name="alamat" 
                               class="form-control">
                    </div>

                    {{-- Jumlah --}}
                    <div class="col-md-4 mb-3">
                        <label>Jumlah (Rp)</label>
                        <input type="number" name="jumlah" 
                               class="form-control" required>
                    </div>

                    {{-- Upload Nota --}}
                    <div class="col-md-8 mb-3">
                        <label>Upload Nota</label>
                        <input type="file" name="nota" 
                               class="form-control">
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="is_transferred" 
                                   class="form-check-input">
                            <label class="form-check-label">
                                Sudah Transfer
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="is_reimbursed" 
                                   class="form-check-input">
                            <label class="form-check-label">
                                Sudah Diganti
                            </label>
                        </div>
                    </div>

                </div>

                <div class="text-end">
                    <a href="{{ route('entertainment.index') }}" 
                       class="btn btn-secondary">
                        Kembali
                    </a>
                    <button type="submit" 
                            class="btn btn-success">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Project Maintenance</h3>
    </div>

    {{-- Filter Tahun --}}
    <form method="GET" action="{{ route('project.maintenance') }}" class="form-inline mb-3">
        <div class="form-group mr-2">
            <label class="mr-2">Tahun Maintenance</label>
            <select name="tahun" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">-- Semua Tahun --</option>
                @foreach ($listTahun as $th)
                    <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>
                        {{ $th }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
<tr>
    <th>No</th>
    <th>Nama Project</th>
    <th>Nomor SPK</th>
    <th>Tgl Berakhir Maintenance</th>
    <th>Jumlah BA</th>
    <th>BA Paid</th>
    <th>Persentase</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
@forelse ($projects as $key => $item)
    @php
        $persen = $item->total_ba > 0
            ? round(($item->total_paid / $item->total_ba) * 100)
            : 0;
    @endphp
    <tr>
        <td>{{ $projects->firstItem() + $key }}</td>
        <td>{{ $item->nama_project }}</td>
        <td>{{ $item->nomor_spk }}</td>
        <td>{{ \Carbon\Carbon::parse($item->tanggal_akhir_maintenance)->format('d-m-Y') }}</td>
        <td class="text-center">{{ $item->total_ba }}</td>
        <td class="text-center">{{ $item->total_paid }}</td>
        <td style="min-width:180px">
            <div class="progress" style="height: 18px;">
                <div class="progress-bar
                    {{ $persen == 100 ? 'bg-success' : ($persen >= 50 ? 'bg-info' : 'bg-warning') }}"
                    role="progressbar"
                    style="width: {{ $persen }}%">
                    {{ $persen }}%
                </div>
            </div>
        </td>
        <td class="text-center">
            <a href="{{ route('project.edit', $item->id_project) }}"
               class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">Data tidak tersedia</td>
    </tr>
@endforelse
</tbody>

        </table>
    </div>

    <div class="card-footer">
        {{ $projects->links() }}
    </div>
</div>
@endsection

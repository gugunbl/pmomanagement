@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Project Maintenance</h3>
    </div>

    <form method="GET" action="{{ route('project.maintenance') }}" class="form-inline mb-3">

        {{-- Filter Tahun --}}
        <div class="form-group mr-3">
            <label class="mr-2">Tahun Maintenance</label>
            <select name="tahun" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">-- Semua Tahun --</option>
            @foreach ($listTahun as $th)
                <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>
                    {{ $th }}
                </option>
            @endforeach            </select>
        </div>

        {{-- Filter PM Maintenance --}}
        <div class="form-group">
            <label class="mr-2">PM Maintenance</label>
            <select name="pm_maintenance" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">-- Semua PM --</option>
@foreach ($listPM as $pm)
    <option value="{{ $pm->id_pm }}"
        {{ $pm_maintenance == $pm->id_pm ? 'selected' : '' }}>
        {{ $pm->nama_pm }}
    </option>
@endforeach            </select>
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
    // progress BA
    $progressBA = $item->total_ba > 0
        ? ($item->total_paid / $item->total_ba)
        : 0;

    // progress waktu
    $start = \Carbon\Carbon::parse($item->tanggal_mulai_maintenance);
    $end   = \Carbon\Carbon::parse($item->tanggal_akhir_maintenance);
    $now   = \Carbon\Carbon::now();

    if ($now < $start) {
        $progressTime = 0;
    } elseif ($now > $end) {
        $progressTime = 1;
    } else {
        $totalDays   = $start->diffInDays($end);
        $passedDays  = $start->diffInDays($now);
        $progressTime = $totalDays > 0 ? ($passedDays / $totalDays) : 0;
    }

    // average progress
    $persen = round((($progressBA + $progressTime) / 2) * 100);

    $today = \Carbon\Carbon::today();
    $endDate = \Carbon\Carbon::parse($item->tanggal_akhir_maintenance);

    if ($endDate->lt($today)) {
        // sudah lewat
        $rowClass = 'table-danger';
    } elseif ($endDate->lte($today->copy()->addMonths(5))) {
        // 5 bulan ke depan
        $rowClass = 'table-warning';
    } else {
        $rowClass = '';
    }

@endphp

    <tr class="{{ $rowClass }}">
        <td>{{ $projects->firstItem() + $key }}</td>


<td>
    <span
        data-toggle="tooltip"
        data-placement="top"
        title="{{ $item->nama_project }}"
        style="cursor: help;"
    >
        {{ \Illuminate\Support\Str::limit($item->nama_project, 80) }}
    </span>
</td>
        <td>{{ $item->nomor_spk }}</td>
        <td>{{ \Carbon\Carbon::parse($item->tanggal_akhir_maintenance)->format('d-m-Y') }}</td>
        <td class="text-center">{{ $item->total_ba }}</td>
        <td class="text-center">{{ $item->total_paid }}</td>
<td style="min-width:200px">
    <div class="progress position-relative" style="height: 22px;">
        <div class="progress-bar
            {{ $persen == 100 ? 'bg-success' : ($persen >= 50 ? 'bg-info' : 'bg-warning') }}"
            role="progressbar"
            style="width: {{ $persen }}%">
        </div>

        <span class="position-absolute w-100 text-center"
              style="
                font-weight: 600;
                font-size: 14px;
                line-height: 22px;
                color: #000;
              ">
            {{ $persen }}%
        </span>
    </div>
</td>           <td class="text-center">
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

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endpush


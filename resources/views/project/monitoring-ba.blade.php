@extends('layouts.app')

@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header border-0">
                    <form action="{{ route('project.ba.monitoring') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">PM Maintenance</label>
                                    <select name="pm_maintenance" class="form-control form-control-sm">
                                        <option value="">Semua PM</option>
                                        @foreach($listPM as $pm)
                                            <option value="{{ $pm->id_pm }}" {{ $pm_maintenance == $pm->id_pm ? 'selected' : '' }}>
                                                {{ $pm->nama_pm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Search Project</label>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama Project..." value="{{ $search }}">
                                </div>
                            </div>
                            <div class="col-md-4 mt-4">
                                    <button type="submit" class="btn btn-primary">Cari</button>
                                    <a href="{{ route('project.ba.export.pdf', request()->query()) }}" class="btn btn-danger">Export PDF</a>
                                    <a href="{{ route('project.ba.export.excel', request()->query()) }}" class="btn btn-success">Export Excel</a>
                                    <a href="{{ route('project.ba.monitoring') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Project</th>
                                <th scope="col">Nomor SPK</th>
                                <th scope="col">PM Maintenance</th>
                                <th scope="col">TOP Ke</th>
                                <th scope="col">Start Date BA</th>
                                <th scope="col">End Date BA</th>
                                <th scope="col">Status</th>
                                <th scope="col">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($listBA as $ba)
                                @php
                                    $highlight = '';
                                    if ($ba->periode_end) {
                                        $endDate = \Carbon\Carbon::parse($ba->periode_end);
                                        $threeMonthsAgo = \Carbon\Carbon::now()->subMonths(3);
                                        $today = \Carbon\Carbon::now();

                                        if ($endDate->lt($threeMonthsAgo)) {
                                            $highlight = 'bg-danger-dark'; // Merah Gelap (> 3 bulan overdue)
                                        } elseif ($endDate->lte($today)) {
                                            $highlight = 'bg-warning-custom'; // Kuning (<= 3 bulan overdue)
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ ($listBA->currentPage() - 1) * $listBA->perPage() + $loop->iteration }}</td>
                                    <td class="text-wrap" style="min-width: 200px;">{{ $ba->project->nama_project ?? '-' }}</td>
                                    <td>{{ $ba->project->nomor_spk ?? '-' }}</td>
                                    <td>{{ $ba->project->mPMMaintenance->nama_pm ?? '-' }}</td>
                                    <td class="text-center">{{ $ba->top_ke }}</td>
                                    <td class="{{ $highlight }}">{{ $ba->periode_start ? \Carbon\Carbon::parse($ba->periode_start)->format('d M Y') : '-' }}</td>
                                    <td class="{{ $highlight }}">{{ $ba->periode_end ? \Carbon\Carbon::parse($ba->periode_end)->format('d M Y') : '-' }}</td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            <i class="{{ $ba->status == 'paid' ? 'bg-success' : ($ba->status == 'submitted' ? 'bg-info' : 'bg-warning') }}"></i>
                                            <span class="status">{{ strtoupper($ba->status) }}</span>
                                        </span>
                                    </td>
                                    <td style="min-width: 250px;">
                                        <div style="max-height: 4.5em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.5em; white-space: normal;">
                                            {{ $ba->keterangan ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data BA.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-4">
                    {{ $listBA->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        white-space: normal !important;
        vertical-align: top;
    }
    
    /* Merah Gelap untuk < 6 bulan */
    .bg-danger-dark {
        background-color: #8B0000 !important;
        color: white !important;
    }
    
    /* Kuning untuk < 3 bulan */
    .bg-warning-custom {
        background-color: #FFFF00 !important;
        color: black !important;
    }
</style>
@endpush

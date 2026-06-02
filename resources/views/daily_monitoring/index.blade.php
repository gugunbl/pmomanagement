@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-5">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Daily Monitoring</h3>
                <a href="{{ route('daily-monitoring.create') }}" class="btn btn-sm btn-primary">
                    Tambah Daily Monitoring
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-items-center table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Project</th>
                                <th>Tanggal Report</th>
                                <th>Input By</th>
                                <th>Total Server</th>
                                <th>Status</th>
                                <th>Health Indicator</th>
                                <th style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monitorings as $key => $row)
                                @php
                                    $statusOk = in_array($row->status, ['healthy', 'ok']);
                                    $indicatorOk = $row->health_indicator === 'normal';
                                @endphp
                                <tr>
                                    <td>{{ ($monitorings->currentPage() - 1) * $monitorings->perPage() + $key + 1 }}</td>
                                    <td>{{ $row->project->nama_project ?? '-' }}</td>
                                    <td>{{ $row->report_datetime ? $row->report_datetime->format('d-m-Y H:i') : '-' }}</td>
                                    <td>{{ $row->user->name ?? '-' }}</td>
                                    <td>{{ $row->total_server }}</td>
                                    <td>
                                        <span class="badge badge-pill {{ $statusOk ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucwords(str_replace('_', ' ', $row->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill {{ $indicatorOk ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($row->health_indicator) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('daily-monitoring.show', $row->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <a href="{{ route('daily-monitoring.edit', $row->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">Tidak ada data daily monitoring.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer py-4">
                {{ $monitorings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(session('daily_monitoring_saved'))
<script>
    $(document).ready(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Berhasil', 'Data daily monitoring sudah tersimpan.', 'success');
        } else {
            alert('Data daily monitoring sudah tersimpan.');
        }
    });
</script>
@endif
@endpush

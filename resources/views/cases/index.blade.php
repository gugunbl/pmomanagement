@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h3 class="mb-0">{{ $title }}</h3>
                    </div>
                    <div class="col-4 text-right">
                        <a href="{{ route('cases.create') }}" class="btn btn-sm btn-primary">Tambah Case</a>
                    </div>
                </div>
            </div>

            <div class="card-body border-top">
                <form action="{{ route('cases.index') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="id_project" class="form-control-label">Filter Project</label>
                                <select name="id_project" id="id_project" class="form-control select2">
                                    <option value="">-- Semua Project --</option>
                                    @foreach($projects as $id => $nama)
                                    <option value="{{ $id }}" {{ request('id_project') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>User Input</th>
                                <th>Tgl Open Case</th>
                                <th>Subject Case</th>
                                <th>Lokasi Perangkat</th>
                                <th>No Tiket Prinsipal</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($cases as $key => $row)
                                @php
                                    $isGray = in_array($row->status, ['Open', 'In Progress']);
                                    $rowClass = $isGray ? 'bg-light text-dark' : '';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ optional($row->user)->name ?? '-' }}</td>
                                    <td>{{ date('d-m-Y', strtotime($row->open_date)) }}</td>
                                    <td>{{ $row->subject }}</td>
                                    <td>{{ $row->location ?? '-' }}</td>
                                    <td>{{ $row->principal_ticket_no ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            @if($row->severity == 'Minor')
                                                <i class="bg-success"></i>
                                            @elseif($row->severity == 'Major')
                                                <i class="bg-warning"></i>
                                            @elseif($row->severity == 'Critical')
                                                <i class="bg-danger"></i>
                                            @else
                                                <i class="bg-info"></i>
                                            @endif
                                            <span class="status">{{ $row->severity }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            @if($row->status == 'Open')
                                                <i class="bg-danger"></i>
                                            @elseif($row->status == 'In Progress')
                                                <i class="bg-warning"></i>
                                            @elseif($row->status == 'Closed')
                                                <i class="bg-success"></i>
                                            @endif
                                            <span class="status">{{ $row->status }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('cases.show', $row->id_case) }}" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                        <a href="{{ route('cases.edit', $row->id_case) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data case.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    });
</script>
@endpush

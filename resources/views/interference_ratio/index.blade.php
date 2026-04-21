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
                        @can('menu-interferance-create')
                        <a href="{{ route('interference-ratio.create') }}" class="btn btn-sm btn-primary">Tambah Data</a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush table-hover bordered table-bordered text-center">
                    <thead class="thead-light">
                        <tr>
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">AP Name</th>
                            <th colspan="2" class="align-middle border-bottom-0">Channel</th>
                            <th colspan="2" class="align-middle border-bottom-0">Utilisasi Interference</th>
                            <th rowspan="2" class="align-middle">Waktu</th>
                            <th rowspan="2" class="align-middle">Aksi</th>
                        </tr>
                        <tr>
                            <th>Before</th>
                            <th>After</th>
                            <th>Before</th>
                            <th>After</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($ratios as $key => $row)
                            <tr>
                                <td>{{ ($ratios->currentPage() - 1) * $ratios->perPage() + $key + 1 }}</td>
                                <td class="text-nowrap">{{ date('d/m/Y', strtotime($row->tanggal)) }}</td>
                                <td class="text-left">{{ $row->ap_name }}</td>
                                <td>{{ $row->channel_before }}</td>
                                <td>{{ $row->channel_after }}</td>
                                <td>{{ $row->utilisasi_before }}%</td>
                                <td>{{ $row->utilisasi_after }}%</td>
                                <td>{{ $row->waktu }}</td>
                                <td>
                                    <a href="{{ route('interference-ratio.show', $row->id) }}" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                    @can('menu-interferance-edit')
                                    <a href="{{ route('interference-ratio.edit', $row->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-4">
                {{ $ratios->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

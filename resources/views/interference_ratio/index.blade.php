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
                            <th rowspan="2" class="align-middle">Tanggal & Waktu</th>
                            <th rowspan="2" class="align-middle">AP Name</th>
                            <th colspan="2" class="align-middle border-bottom-0">Channel</th>
                            <th colspan="2" class="align-middle border-bottom-0">Utilisasi Interference</th>
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
                        @php
                            $groupedRatios = [];
                            foreach($ratios as $row) {
                                $key = $row->tanggal . '_' . $row->waktu;
                                if (!isset($groupedRatios[$key])) {
                                    $groupedRatios[$key] = [];
                                }
                                $groupedRatios[$key][] = $row;
                            }
                            $pageOffset = ($ratios->currentPage() - 1) * $ratios->perPage();
                            $groupIndex = 0;
                        @endphp
                        
                        @forelse($groupedRatios as $key => $group)
                            @php
                                $groupSize = count($group);
                                $firstItem = $group[0];
                                $groupIndex++;
                            @endphp
                            @foreach($group as $index => $row)
                            <tr>
                                @if($index == 0)
                                <td rowspan="{{ $groupSize }}" class="align-middle">{{ $pageOffset + $groupIndex }}</td>
                                <td rowspan="{{ $groupSize }}" class="align-middle text-nowrap">
                                    {{ date('d/m/Y', strtotime($firstItem->tanggal)) }}<br>
                                    <small class="font-weight-bold text-muted">{{ $firstItem->waktu }}</small>
                                </td>
                                @endif
                                
                                <td class="text-left align-middle">
                                    @php
                                        $apParts = explode(' - ', $row->ap_name);
                                        $hostname = count($apParts) >= 4 ? trim($apParts[3]) : $row->ap_name;
                                        if ((empty($hostname) || $hostname == '-') && count($apParts) >= 5) {
                                            $hostname = trim($apParts[4]) != '-' ? trim($apParts[4]) : $row->ap_name;
                                        }
                                        if (empty($hostname) || $hostname == '-') {
                                            $hostname = $row->ap_name;
                                        }
                                    @endphp
                                    <span class="font-weight-bold" title="{{ $row->ap_name }}">{{ $hostname }}</span>
                                    @if($hostname != $row->ap_name)
                                        <br><small class="text-muted">{{ Str::limit($row->ap_name, 30) }}</small>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $row->channel_before }}</td>
                                <td class="align-middle text-primary font-weight-bold">{{ $row->channel_after }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-pill badge-{{ $row->utilisasi_before > 50 ? 'danger' : ($row->utilisasi_before > 30 ? 'warning' : 'success') }}">
                                        {{ $row->utilisasi_before }}%
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-pill badge-{{ $row->utilisasi_after > 50 ? 'danger' : ($row->utilisasi_after > 30 ? 'warning' : 'success') }}">
                                        {{ $row->utilisasi_after }}%
                                    </span>
                                </td>
                                
                                @if($index == 0)
                                <td rowspan="{{ $groupSize }}" class="align-middle">
                                    <a href="{{ route('interference-ratio.show', $firstItem->id) }}" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                    @can('menu-interferance-edit')
                                    <a href="{{ route('interference-ratio.edit', $firstItem->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                    @endcan
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data.</td>
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

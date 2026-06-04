@extends('layouts.app')
@push('pg_btn')
@can('create-perangkat')
    <a href="{{ route('perangkat.create') }}" class="btn btn-sm btn-neutral">Tambah Data Perangkat</a>
    <a href="{{ route('pergantian.index') }}" class="btn btn-sm btn-neutral">Data Pergantian Perangkat</a>
@endcan
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent pb-2">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Data Semua Perangkat</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body border-top">
                    <form action="{{ route('perangkat.index') }}" method="GET" class="perangkat-filter mb-4" id="filterForm">
                        @php
                            $selectedProjects = (array) request('id_project', []);
                        @endphp
                        <div class="row align-items-end">
                            <div class="col-xl-5 col-lg-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="id_project" class="form-control-label">Filter Project</label>
                                    <select name="id_project[]" id="id_project" class="form-control select2" multiple data-placeholder="-- Semua Project --">
                                        @foreach($projectes as $id => $nama)
                                            <option value="{{ $id }}" {{ in_array($id, $selectedProjects) ? 'selected' : '' }}>{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="search_input" class="form-control-label">Search Serial / Merk / Type</label>
                                    <input type="text" name="search" id="search_input" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-12 mb-3">
                                <div class="perangkat-filter-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> Cari
                                    </button>
                                    <a href="{{ route('perangkat.export', request()->all()) }}" class="btn btn-success">
                                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                                    </a>
                                    <a href="{{ route('perangkat.index') }}" class="btn btn-secondary text-dark">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <div>
                            <table class="table table-hover align-items-center">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col">Nama Project / Perangkat</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Warranty</th>
                                    <th scope="col" class="text-center">Aksi</th>
                                </tr>
                                </thead>

                                <tbody class="list">
                                @foreach($perangkats as $perangkat)
                                <tr>
                                    <th scope="row">
                                        <div class="mx-w-440">
                                            <span class="font-weight-bold">{{ $perangkat->project->nama_project ?? '-' }}</span><br>
                                            <small class="text-muted">{{ $perangkat->merk }} {{ $perangkat->type }}</small>
                                            @if($perangkat->type_lisensi)
                                                <br>
                                                <small class="text-dark font-weight-bold">
                                                    @if(isset($perangkat->jenisPerangkat) && strpos(strtolower($perangkat->jenisPerangkat->jns_perangkat), 'virtualisasi') !== false)
                                                        <i class="fas fa-microchip mr-1"></i> {{ $perangkat->type_lisensi }} Cores
                                                    @else
                                                        <i class="fas fa-id-card mr-1"></i> {{ $perangkat->type_lisensi }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                    </th>

                                    <td><code>{{ $perangkat->serialnumber }}</code></td>
                                    <td>
                                        <small>
                                            <span class="font-weight-bold">{{ $perangkat->site }}</span> / {{ $perangkat->lokasi->nama_lokasi ?? '-' }}
                                            @if($perangkat->lokasi && $perangkat->lokasi->alamat)
                                                <br><span class="text-muted text-xs"><i class="fas fa-map-marker-alt"></i> {{ $perangkat->lokasi->alamat }}</span>
                                            @endif
                                            <br>
                                            {{ $perangkat->koordinat }} {{ $perangkat->lantai }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-primary">{{ $perangkat->status_server }}</span><br>
                                        @if($perangkat->foto_1 || $perangkat->foto_2 || $perangkat->foto_3)
                                            <span class="text-xs text-success">
                                                <i class="fas fa-camera"></i> Foto
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ $perangkat->start_warranty ? \Carbon\Carbon::parse($perangkat->start_warranty)->format('d/m/y') : '-' }} - 
                                            {{ $perangkat->end_warranty ? \Carbon\Carbon::parse($perangkat->end_warranty)->format('d/m/y') : '-' }}
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('perangkat.show', $perangkat->id_perangkat) }}"
                                            class="btn btn-outline-info" title="View Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pergantian.create', $perangkat->id_perangkat) }}"
                                            class="btn btn-outline-warning" title="Pergantian Perangkat">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
    
                                <tfoot >
                                <tr>
                                    <td colspan="6">
                                        {{$perangkats->links()}}
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .perangkat-filter {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: 1.25rem 1.25rem .5rem;
    }

    .perangkat-filter .form-control-label {
        color: #172b4d;
        font-size: .78rem;
        font-weight: 600;
        margin-bottom: .45rem;
    }

    .perangkat-filter .form-control,
    .perangkat-filter .select2-container--bootstrap4 .select2-selection {
        border-color: #d9e2ec;
        min-height: 44px;
    }

    .perangkat-filter .select2-container--bootstrap4 .select2-selection--multiple {
        max-height: 88px;
        overflow-y: auto;
        padding: .25rem .4rem;
    }

    .perangkat-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        max-width: 100%;
        margin: .12rem .2rem .12rem 0;
        border: 1px solid #d9e2ec;
        border-radius: .25rem;
        background: #f8fafc;
        color: #334e68;
        font-size: .76rem;
        line-height: 1.4;
        padding: .18rem .45rem .18rem 1.15rem;
        white-space: normal;
    }

    .perangkat-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-search--inline {
        display: inline-block;
        width: auto;
    }

    .perangkat-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
        min-width: 180px;
        width: auto !important;
        margin-top: .18rem;
    }

    .perangkat-filter .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #829ab1;
        left: .35rem;
        margin-right: .25rem;
    }

    .perangkat-filter-actions {
        display: flex;
        gap: .5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .perangkat-filter-actions .btn {
        min-height: 44px;
        padding-left: 1rem;
        padding-right: 1rem;
    }
</style>
@endpush



@push('scripts')

<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        closeOnSelect: false
    });

    $('#search_input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // hentikan default
            $('#filterForm').submit();
        }
    });

});
</script>

    <script>
        jQuery(document).ready(function(){
            $('.delete').on('click', function(e){
                e.preventDefault();
                let that = jQuery(this);
                jQuery.confirm({
                    icon: 'fas fa-wind-warning',
                    closeIcon: true,
                    title: 'Are you sure!',
                    content: 'You can not undo this action.!',
                    type: 'red',
                    typeAnimated: true,
                    buttons: {
                        confirm: function () {
                            that.parent('form').submit();
                            //$.alert('Confirmed!');
                        },
                        cancel: function () {
                            //$.alert('Canceled!');
                        }
                    }
                });
            })
        })

    </script>
@endpush

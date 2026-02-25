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
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-0">Data Semua Perangkat</h3>
                        </div>
                        <div class="col-lg-4">
{!! Form::open(['route' => 'perangkat.index', 'method'=>'get', 'id'=>'filterForm']) !!}
<div class="row g-2 align-items-center">

    <div class="col-md-5">
        {{ Form::select(
            'id_project',
            $projectes,
            request('id_project'),
            [
                'class'=> 'selectpicker form-control form-control-sm',
                'data-live-search' => 'true',
                'data-width' => '100%',
                'title' => 'Select Project...',
                'id' => 'id_project'
            ]
        ) }}
    </div>

    <div class="col-md-5">
        {{ Form::text(
            'search',
            request('search'),
            [
                'class' => 'form-control form-control-sm',
                'placeholder'=>'Search serial / merk / type',
                'id' => 'search_input'
            ]
        ) }}
    </div>

    <div class="col-md-2 text-end">
        <a href="{{ route('perangkat.index') }}"
           class="btn btn-sm btn-outline-secondary w-100">
            Reset
        </a>
    </div>

</div>
{!! Form::close() !!}


                </div>
                    </div>
                </div>
                <div class="card-body p-0">
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
                                        @if($perangkat->upload_do)
                                            <a href="{{ asset('storage/'.$perangkat->upload_do) }}" target="_blank" class="text-xs">
                                                <i class="fas fa-file-pdf"></i> DO
                                            </a>
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



@push('scripts')

<script>
$(document).ready(function () {

    $('#search_input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // hentikan default
            $('#filterForm').submit();
        }
    });

});
</script>


<script>
$(document).ready(function () {

    $('#id_project').on('changed.bs.select', function () {
        $('#filterForm').submit();
    });

});
</script>



<script>
$(document).ready(function() {
    $('#project_id').select2({
        placeholder: 'Pilih Project',
        allowClear: true,
        width: '100%'
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

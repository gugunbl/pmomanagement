@extends('layouts.app')
@push('pg_btn')
@can('view-perangkat')
    <a href="{{ route('perangkat.index') }}" class="btn btn-sm btn-neutral">All Perangakt</a>
@endcan
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row">
                        <div class="col-lg-4">
                            <h3 class="mb-0">Summary Perangkat</h3>
                        </div>
                        <div class="col-lg-8 text-right">
                            {!! Form::open(['route' => 'perangkat.summary', 'method'=>'get', 'class' => 'form-inline justify-content-end']) !!}
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            
                            @if($activeTab == 'project')
                            <div class="form-group mb-0 mr-2">
                                {!! Form::select('customer', ['' => 'All Customers'] + $customers->toArray(), $customerFilter, ['class' => 'form-control form-control-sm', 'onchange' => 'this.form.submit()']) !!}
                            </div>
                            @endif

                            <div class="form-group mb-0">
                                {{ Form::text('search', $search, ['class' => 'form-control form-control-sm', 'placeholder'=>'Search by nama project...']) }}
                            </div>
                            <div class="form-group mb-0 ml-2">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs border-0 mt-3 ml-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'category' ? 'active font-weight-bold text-primary' : 'text-muted' }}" href="{{ route('perangkat.summary', ['tab' => 'category']) }}">
                                By Category
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'project' ? 'active font-weight-bold text-primary' : 'text-muted' }}" href="{{ route('perangkat.summary', ['tab' => 'project']) }}">
                                By Project
                            </a>
                        </li>
                    </ul>

                    <div class="table-responsive mt-3">
                        @if($activeTab == 'category')
                        <!-- Table By Category -->
                            <table class="table table-hover align-items-center">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="5%">No</th>
                                    <th scope="col">Nama Project</th>
                                    <th scope="col">Jenis Perangkat</th>
                                    <th scope="col">Merk </th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Jumlah</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @foreach($summaryperangkats as $key => $perangkat)
                                    <tr>
                                        <td>{{ ($summaryperangkats->currentPage() - 1) * $summaryperangkats->perPage() + $key + 1 }}</td>
                                        <th scope="row">
                                            <div class="mx-w-440 d-flex flex-wrap">
                                                <a href="{{ route('perangkat.view-detail-summary', ['id_project' => $perangkat->id_project, 'merk' => $perangkat->merk, 'type' => $perangkat->type]) }}" class="font-weight-bold text-primary" title="{{ $perangkat->project->nama_project ?? '' }}" data-toggle="tooltip">
                                                    {{ \Illuminate\Support\Str::limit($perangkat->project->nama_project ?? '-', 90) }}
                                                </a>
                                            </div>
                                        </th>
                                        <td>
                                            {{ $perangkat->jenisPerangkat->jns_perangkat ?? '-' }}
                                        </td>
                                        <td class="budget">
                                            {{$perangkat->merk}}
                                        </td>
                                        <td>
                                            {{$perangkat->type}}
                                        </td>
                                        <td>
                                            {{$perangkat->jml}}
                                        </td>

                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot >
                                <tr>
                                    <td colspan="6">
                                        {{$summaryperangkats->links()}}
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        @else
                        <!-- Table By Project -->
                            <table class="table table-hover align-items-center">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="5%">No</th>
                                    <th scope="col">Nama Project</th>
                                    <th scope="col">End Maintenance</th>
                                    <th scope="col">PM Maintenance</th>
                                    <th scope="col">Jumlah Perangkat</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @foreach($summaryperangkats as $key => $project)
                                    <tr class="{{ $project->status_maintenance != '1' ? 'bg-danger text-white' : '' }}">
                                        <td>{{ ($summaryperangkats->currentPage() - 1) * $summaryperangkats->perPage() + $key + 1 }}</td>
                                        <th scope="row">
                                            <div class="mx-w-440 d-flex flex-wrap">
                                                <a href="{{ route('perangkat.view-detail-summary', ['id_project' => $project->id_project]) }}" class="font-weight-bold {{ $project->status_maintenance != '1' ? 'text-white' : 'text-primary' }}" title="{{ $project->nama_project }}" data-toggle="tooltip">
                                                    {{ \Illuminate\Support\Str::limit($project->nama_project, 90) }}
                                                </a>
                                            </div>
                                        </th>
                                        <td>
                                            {{ $project->tanggal_akhir_maintenance ? strtoupper(\Carbon\Carbon::parse($project->tanggal_akhir_maintenance)->format('d-M-Y')) : '-' }}
                                        </td>
                                        <td>
                                            {{$project->mPMMaintenance->nama_pm ?? '-'}}
                                        </td>
                                        <td>
                                            {{$project->jml}}
                                        </td>

                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot >
                                <tr>
                                    <td colspan="5">
                                        {{$summaryperangkats->links()}}
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
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

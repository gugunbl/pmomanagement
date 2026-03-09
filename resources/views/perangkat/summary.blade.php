@extends('layouts.app')
@push('pg_btn')
@can('summary-perangkat')
    <a href="{{ route('perangkat.index') }}" class="btn btn-sm btn-neutral">All Perangakt</a>
@endcan
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-0">Summary Perangkat</h3>
                        </div>
                        <div class="col-lg-4">
                    {!! Form::open(['route' => 'perangkat.index', 'method'=>'get']) !!}
                        <div class="form-group mb-0">
                        {{ Form::text('search', request()->query('search'), ['class' => 'form-control form-control-sm', 'placeholder'=>'Search post']) }}
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
                                    <th scope="col">Nama Project</th>
                                    <th scope="col">Merk </th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Jumlah</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @foreach($summaryperangkats as $perangkat)
                                    <tr>
                                        <th scope="row">
                                            <div class="mx-w-440 d-flex flex-wrap">
                                                <a href="{{ route('perangkat.index', ['id_project' => $perangkat->id_project]) }}" class="font-weight-bold text-primary">
                                                    {{$perangkat->project->nama_project }}
                                                </a>
                                            </div>
                                        </th>
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
                        </div>
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

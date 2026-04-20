@extends('layouts.app')
@push('pg_btn')
    <a href="{{ route('sales.create') }}" class="btn btn-sm btn-neutral">Create New Sales</a>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-0">All Sales</h3>
                        </div>
                        <div class="col-lg-4">
                            {!! Form::open(['route' => 'sales.index', 'method'=>'get']) !!}
                                <div class="form-group mb-0">
                                    {{ Form::text('search', request()->query('search'), ['class' => 'form-control form-control-sm', 'placeholder'=>'Search Sales']) }}
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
                                    <th scope="col">Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Created at</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @foreach($saless as $sales)
                                    <tr>
                                        <th scope="row">
                                            {{$sales->name}}
                                        </th>
                                        <td>
                                            @if($sales->status)
                                                <span class="badge badge-pill badge-lg badge-success">Active</span>
                                            @else
                                                <span class="badge badge-pill badge-lg badge-danger">Disabled</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $sales->created_at ? $sales->created_at->diffForHumans() : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {!! Form::open(['route' => ['sales.destroy', $sales->id],'method' => 'delete',  'class'=>'d-inline-block dform']) !!}
                                                <a class="btn btn-info btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Edit Sales" href="{{route('sales.edit', $sales->id)}}">
                                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                                </a>
                                                <button type="submit" class="btn delete btn-danger btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Delete Sales">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4">
                                        {{$saless->links()}}
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
                    icon: 'fas fa-exclamation-triangle',
                    closeIcon: true,
                    title: 'Are you sure!',
                    content: 'You can not undo this action.!',
                    type: 'red',
                    typeAnimated: true,
                    buttons: {
                        confirm: function () {
                            that.parent('form').submit();
                        },
                        cancel: function () {
                        }
                    }
                });
            })
        })
    </script>
@endpush

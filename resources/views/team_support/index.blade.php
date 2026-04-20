@extends('layouts.app')
@push('pg_btn')
    <a href="{{ route('team-support.create') }}" class="btn btn-sm btn-neutral">
        <i class="fas fa-plus mr-1"></i> Tambah Team Support
    </a>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <h3 class="mb-0">Master Data Team Support</h3>
                        </div>
                        <div class="col-lg-6">
                            {!! Form::open(['route' => 'team-support.index', 'method' => 'get']) !!}
                                <div class="input-group input-group-sm">
                                    {{ Form::text('search', $search ?? '', ['class' => 'form-control', 'placeholder' => 'Cari nama team...']) }}
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Nama Team</th>
                                    <th>Jumlah Project</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teams as $team)
                                    <tr>
                                        <td>{{ $teams->firstItem() + $loop->index }}</td>
                                        <td><strong>{{ $team->name }}</strong></td>
                                        <td>
                                            <span class="badge badge-light">{{ $team->projects->count() }} project</span>
                                        </td>
                                        <td>
                                            @if($team->status)
                                                <span class="badge badge-pill badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-pill badge-danger">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>{{ $team->created_at ? $team->created_at->diffForHumans() : '-' }}</td>
                                        <td class="text-center">
                                            {!! Form::open(['route' => ['team-support.destroy', $team->id], 'method' => 'delete', 'class' => 'd-inline-block dform']) !!}
                                                <a class="btn btn-info btn-sm m-1" href="{{ route('team-support.edit', $team->id) }}"
                                                   data-toggle="tooltip" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button type="submit" class="btn btn-danger btn-sm m-1 delete"
                                                        data-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                            Belum ada data team support.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($teams->hasPages())
                    <div class="card-footer">
                        {{ $teams->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function(){
            $('.delete').on('click', function(e){
                e.preventDefault();
                let that = $(this);
                $.confirm({
                    icon: 'fas fa-exclamation-triangle',
                    closeIcon: true,
                    title: 'Hapus Team Support?',
                    content: 'Team support ini akan dihapus dari semua project yang menggunakannya. Lanjutkan?',
                    type: 'red',
                    typeAnimated: true,
                    buttons: {
                        confirm: function () {
                            that.closest('form').submit();
                        },
                        cancel: function () {}
                    }
                });
            });
        });
    </script>
@endpush

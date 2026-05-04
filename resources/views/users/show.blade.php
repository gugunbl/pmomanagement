@extends('layouts.app')
@push('pg_btn')
    @can('update-user')
        <a class="btn btn-info btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Edit user details" href="{{route('users.edit',$user)}}">
            <i class="fa fa-edit" aria-hidden="true"></i> Edit User
        </a>
        <form id="send-credentials-form" action="{{ route('users.send-credentials', $user) }}" method="POST" style="display: inline;">
            @csrf
            <button type="button" class="btn btn-warning btn-sm m-1" onclick="handleEmailAkses(this)">
                <i class="fa fa-envelope" aria-hidden="true"></i> Email Akses
            </button>
        </form>
    @endcan
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-1">
                            Name
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $user->name }}</strong>
                        </div>
                        <div class="col-sm-8 text-right">
                            @if ($user->profile_photo)
                                <a href="{{ asset($user->profile_photo) }}" target="_blank">
                                    <img width="100" height="100" class="img-fluid rounded-pill" src="{{ asset($user->profile_photo) }}" alt="">
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Email
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $user->email }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Phone
                        </div>
                        <div class="col-sm-3">
                            <strong>{{ $user->phone_number }}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Role
                        </div>
                        <div class="col-sm-3">
                            @foreach ($user->roles as $role)
                                <strong>{{ $role->name }}</strong>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                            Status
                        </div>
                        <div class="col-sm-3">
                            {{ $user->status ? 'Active' : 'Disable'}}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-12">
                            <h6 class="heading-small text-muted mb-3">Project & Team Support</h6>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-control-label">Projects</label>
                            @if($user->projects->count() > 0)
                                <ul class="list-group list-group-flush border rounded">
                                    @foreach($user->projects as $project)
                                        <li class="list-group-item py-2">{{ $project->nama_project }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted small">All Projects (Admin Mode)</div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <label class="form-control-label">Team Supports</label>
                            @if($user->teamSupports->count() > 0)
                                <ul class="list-group list-group-flush border rounded">
                                    @foreach($user->teamSupports as $team)
                                        <li class="list-group-item py-2">{{ $team->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted small">Belum ada team support yang ditugaskan.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function handleEmailAkses(btn) {
        if (typeof Swal === 'undefined') {
            if (confirm("Kirim email akses ke {{ $user->email }}?")) {
                document.getElementById('send-credentials-form').submit();
            }
            return;
        }

        Swal.fire({
            title: 'Kirim Email Akses?',
            text: "Apakah Anda yakin ingin mengirim email akses ke {{ $user->email }}?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Tidak',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const form = document.getElementById('send-credentials-form');
                const url = form.action;
                const token = form.querySelector('input[name="_token"]').value;

                return $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: token
                    },
                    dataType: 'json'
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Gagal mengirim email');
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Gagal: ${error.message || error.statusText || 'Terjadi kesalahan'}`
                    );
                    return false;
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.value.message,
                    icon: 'success'
                });
            }
        });
    }
</script>
@endpush

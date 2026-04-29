@extends('layouts.app')
@push('pg_btn')
    @can('update-user')
        <a class="btn btn-info btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Edit user details" href="{{route('users.edit',$user)}}">
            <i class="fa fa-edit" aria-hidden="true"></i> Edit User
        </a>
        <button class="btn btn-warning btn-sm m-1" onclick="handleEmailAkses(event)">
            <i class="fa fa-envelope" aria-hidden="true"></i> Email Akses
        </button>
        <form id="send-credentials-form" action="{{ route('users.send-credentials', $user) }}" method="POST" style="display: none;">
            @csrf
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function handleEmailAkses(e) {
        e.preventDefault();
        console.log('Tombol Email diklik');
        
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
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Form disubmit...');
                document.getElementById('send-credentials-form').submit();
            }
        });
    }
</script>
@endpush

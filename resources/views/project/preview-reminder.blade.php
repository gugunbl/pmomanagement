@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Preview Email Reminder</h3>
                <div>
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">Batal</a>
                    {!! Form::open(['route' => 'project.maintenance.send-reminder', 'style' => 'display:inline']) !!}
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Kirim email ke {{ $recipientEmails }}?')">
                            <i class="fas fa-paper-plane"></i> Kirim Sekarang
                        </button>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Penerima:</strong> {{ $recipientEmails }} <br>
                    <strong>Subjek:</strong> {{ $subject }}
                </div>
                <hr>
                <div style="border: 1px solid #ddd; padding: 20px; background: #fff;">
                    @include('emails.maintenance-reminder', [
                        'bodyMessage' => $bodyMessage,
                        'expiredProjects' => $expiredProjects,
                        'endingSoonProjects' => $endingSoonProjects
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@component('mail::message')
Dear {{ explode(' ', $user->name)[0] }},

Untuk Akses System Ticketing

@php
    $ticketingUrl = rtrim(setting('ticketing_url') ?: config('settings.defaults.ticketing_url', config('app.url')), '/');
@endphp

url : [{{ $ticketingUrl }}]({{ $ticketingUrl }})

username : {{ $user->email }}
password : {{ $defaultPassword }}

Terima kasih,
@endcomponent

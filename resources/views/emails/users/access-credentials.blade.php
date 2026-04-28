@component('mail::message')
Dear {{ explode(' ', $user->name)[0] }},

Untuk Akses System Ticketing

url : [{{ setting('ticketing_url', config('app.url')) }}]({{ setting('ticketing_url', config('app.url')) }})

username : {{ $user->email }}
password : {{ $defaultPassword }}

Terima kasih,
@endcomponent

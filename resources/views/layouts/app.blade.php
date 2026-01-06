<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS (WAJIB & HARUS DI HEAD) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/dist/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{asset('assets/fonts/stylesheet.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/vendor/fullcalendar/dist/fullcalendar.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/sweetalert2/dist/sweetalert2.min.css')}}">
<!--    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-select.min.css')}}">
-->
    <link rel="stylesheet" href="{{asset('assets/css/jquery-confirm.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet"/>






<style>
/* ===== Bootstrap Select Custom Fix ===== */
.bootstrap-select .dropdown-menu {
    max-width: 100%;
    width: 100%;
}

.bootstrap-select .dropdown-menu li a span.text {
    white-space: normal;       /* boleh turun baris */
    word-break: break-word;    /* potong kata panjang */
}
</style>

    
    @stack('styles')
</head>

<body>

@include('includes.navbar')
<div class="main-content" id="panel">
    @include('includes.header')
    @include('includes.page-header')
    <div class="container-fluid mt--6">
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @yield('content')
    </div>

<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{asset('assets/vendor/js-cookie/js.cookie.js')}}"></script>
<script src="{{asset('assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js')}}"></script>
<script src="{{asset('assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<!-- <script src="{{asset('assets/js/bootstrap-select.min.js')}}"></script>
-->
<script src="{{asset('assets/js/jquery-confirm.min.js')}}"></script>
<script src="{{asset('assets/js/dashboard.js')}}"></script>

@stack('scripts')

</body>
</html>

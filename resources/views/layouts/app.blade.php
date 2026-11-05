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
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-select.min.css')}}">
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

.import-status-stack {
    position: fixed;
    right: 16px;
    bottom: 16px;
    z-index: 1080;
    width: min(390px, calc(100vw - 32px));
    max-height: 45vh;
    overflow-y: auto;
}

.import-status-notice {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-top: 8px;
    padding: 14px 16px;
    border-left: 4px solid #168a70;
    border-radius: 6px;
    background: #fff;
    box-shadow: 0 4px 18px rgba(0, 0, 0, .18);
}

.import-status-notice.is-failed {
    border-left-color: #c62828;
}

.import-status-notice.is-active {
    border-left-color: #1976a5;
}

.import-status-notice .import-status-message {
    flex: 1;
    margin: 0;
}

.import-status-notice .import-status-close {
    border: 0;
    padding: 0 0 0 8px;
    background: transparent;
    color: #64707b;
    cursor: pointer;
}

@media (max-width: 576px) {
    .import-status-stack {
        right: 10px;
        bottom: 10px;
        width: calc(100vw - 20px);
    }
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

    @auth
        <div id="importStatusStack" class="import-status-stack" aria-live="polite" aria-atomic="false"></div>
    @endauth

<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{asset('assets/vendor/js-cookie/js.cookie.js')}}"></script>
<script src="{{asset('assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js')}}"></script>
<script src="{{asset('assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script src="{{asset('assets/js/bootstrap-select.min.js')}}"></script>
<script src="{{asset('assets/vendor/sweetalert2/dist/sweetalert2.min.js')}}"></script>
<script src="{{asset('assets/js/dashboard.js')}}"></script>

@auth
<script>
$(function () {
    var statusUrl = @json(route('dashboard.utilisasi.import-status'));
    var readUrl = @json(route('dashboard.utilisasi.import-status.read', ['id' => 'IMPORT_ID']));
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var $stack = $('#importStatusStack');

    function renderImportStatus(item) {
        var id = String(item.id);
        var $notice = $stack.find('[data-import-id="' + id + '"]');
        var active = item.status === 'queued' || item.status === 'processing';
        var failed = item.status === 'failed';

        if (!$notice.length) {
            $notice = $('<div>').attr('data-import-id', id).addClass('import-status-notice');
            $notice.append($('<span>').addClass('import-status-icon'));
            $notice.append($('<p>').addClass('import-status-message'));
            $stack.prepend($notice);
        }

        $notice.toggleClass('is-active', active).toggleClass('is-failed', failed);
        $notice.find('.import-status-icon')
            .html(active ? '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i>' : (failed ? '<i class="fas fa-exclamation-circle" aria-hidden="true"></i>' : '<i class="fas fa-check-circle" aria-hidden="true"></i>'));
        $notice.find('.import-status-message').text(item.message || 'Status import diperbarui.');

        if (!active && !$notice.find('.import-status-close').length) {
            var $close = $('<button type="button" class="import-status-close" aria-label="Tutup notifikasi"><i class="fas fa-times" aria-hidden="true"></i></button>');
            $close.on('click', function () {
                $.ajax({
                    url: readUrl.replace('IMPORT_ID', encodeURIComponent(id)),
                    method: 'POST',
                    data: { _token: csrfToken }
                }).always(function () {
                    $notice.remove();
                });
            });
            $notice.append($close);
        }
    }

    function refreshImportStatuses() {
        $.getJSON(statusUrl).done(function (response) {
            var visibleIds = [];

            $.each(response.imports || [], function (_, item) {
                visibleIds.push(String(item.id));
                renderImportStatus(item);
            });

            $stack.children('[data-import-id]').each(function () {
                if (visibleIds.indexOf(String($(this).attr('data-import-id'))) === -1) {
                    $(this).remove();
                }
            });
        });
    }

    refreshImportStatuses();
    window.setInterval(refreshImportStatuses, 10000);
});
</script>
@endauth

@stack('scripts')

</body>
</html>

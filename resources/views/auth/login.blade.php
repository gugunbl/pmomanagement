@extends('layouts.auth')

@section('content')
    <style>
        .login-idsolusi-page {
            --idsolusi-red: #da251d;
            --idsolusi-red-dark: #b51f18;
            --idsolusi-charcoal: #333333;
            --idsolusi-panel: #070b11;
            --idsolusi-panel-soft: #0f1a24;
            --idsolusi-line: rgba(255, 255, 255, .62);
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(420px, .88fr);
            background: var(--idsolusi-panel);
            color: #ffffff;
            overflow: hidden;
        }

        .login-visual {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 6rem 7.5vw;
            background-image:
                linear-gradient(90deg, rgba(5, 9, 15, .2) 0%, rgba(7, 11, 17, .08) 50%, rgba(7, 11, 17, .85) 100%),
                linear-gradient(180deg, rgba(7, 11, 17, .35) 0%, rgba(7, 11, 17, .18) 46%, rgba(7, 11, 17, .78) 100%),
                url("{{ asset('assets/img/idsolusi/login-smart-city.png') }}");
            background-size: cover;
            background-position: center;
        }

        .login-visual:before,
        .login-visual:after {
            content: "";
            position: absolute;
            pointer-events: none;
        }

        .login-visual:before {
            inset: 0;
            background:
                radial-gradient(circle at 18% 24%, rgba(218, 37, 29, .18), transparent 24%),
                radial-gradient(circle at 58% 74%, rgba(218, 37, 29, .12), transparent 22%),
                linear-gradient(90deg, rgba(0, 0, 0, .25), rgba(0, 0, 0, .04));
        }

        .login-visual:after {
            top: 0;
            right: -1px;
            bottom: 0;
            width: 22%;
            background: linear-gradient(90deg, rgba(7, 11, 17, 0), var(--idsolusi-panel));
        }

        .login-visual-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
            margin-top: 9rem;
        }

        .login-brand-logo {
            width: 190px;
            max-width: 55vw;
            height: auto;
            display: block;
            margin-bottom: 1.5rem;
            background: rgba(255, 255, 255, .92);
            border-radius: 8px;
            padding: 12px 18px;
            box-shadow: 0 22px 60px rgba(0, 0, 0, .28);
        }

        .login-visual-copy {
            max-width: 720px;
            margin: 0;
            font-size: 1.08rem;
            line-height: 1.42;
            color: rgba(255, 255, 255, .94);
            text-shadow: 0 2px 18px rgba(0, 0, 0, .52);
        }

        .login-visual-copy strong {
            font-weight: 800;
        }

        .login-panel {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 4.8vw;
            background:
                radial-gradient(circle at 12% 68%, rgba(218, 37, 29, .12), transparent 30%),
                linear-gradient(145deg, var(--idsolusi-panel-soft) 0%, var(--idsolusi-panel) 54%, #030507 100%);
            box-shadow: -32px 0 90px rgba(0, 0, 0, .35);
        }

        .login-panel-inner {
            width: 100%;
            max-width: 420px;
        }

        .login-title {
            margin-bottom: .35rem;
            color: #ffffff;
            font-size: 1.38rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .login-subtitle {
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, .82);
            font-size: 1rem;
        }

        .login-panel label {
            color: #ffffff;
            font-size: .9rem;
            font-weight: 600;
        }

        .login-field {
            position: relative;
        }

        .login-field-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: rgba(255, 255, 255, .88);
            transform: translateY(-50%);
        }

        .login-field .form-control {
            height: 42px;
            padding-left: 42px;
            color: #ffffff;
            background: rgba(16, 27, 39, .88);
            border: 1px solid rgba(255, 255, 255, .62);
            border-radius: 4px;
            box-shadow: none;
        }

        .login-field .form-control::placeholder {
            color: rgba(255, 255, 255, .66);
        }

        .login-field .form-control:focus {
            border-color: #ffffff;
            box-shadow: 0 0 0 .16rem rgba(218, 37, 29, .28);
        }

        .login-password-toggle {
            position: absolute;
            top: 50%;
            right: 11px;
            z-index: 3;
            padding: 0;
            color: rgba(255, 255, 255, .82);
            background: transparent;
            border: 0;
            transform: translateY(-50%);
        }

        .login-password-toggle:hover,
        .login-password-toggle:focus {
            color: #ffffff;
            outline: 0;
        }

        .login-field-password .form-control {
            padding-right: 42px;
        }

        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: .35rem;
        }

        .login-panel .custom-control-label {
            color: rgba(255, 255, 255, .88);
            font-weight: 500;
        }

        .login-panel .custom-control-label:before {
            border-color: rgba(255, 255, 255, .82);
            background: transparent;
        }

        .login-panel .custom-control-input:checked ~ .custom-control-label:before {
            border-color: var(--idsolusi-red);
            background-color: var(--idsolusi-red);
        }

        .login-forgot-link {
            color: rgba(255, 255, 255, .78);
            font-weight: 600;
        }

        .login-forgot-link:hover,
        .login-forgot-link:focus {
            color: #ffffff;
        }

        .btn-idsolusi {
            width: 100%;
            height: 42px;
            margin-top: 1.55rem;
            background: linear-gradient(90deg, var(--idsolusi-red) 0%, var(--idsolusi-red-dark) 100%);
            border: 0;
            border-radius: 4px;
            box-shadow: 0 12px 24px rgba(218, 37, 29, .26);
            font-weight: 800;
        }

        .btn-idsolusi:hover,
        .btn-idsolusi:focus {
            background: linear-gradient(90deg, #ef3027 0%, var(--idsolusi-red) 100%);
            box-shadow: 0 14px 28px rgba(218, 37, 29, .34);
        }

        .login-panel .invalid-feedback {
            display: block;
            color: #ffb7b3;
        }

        .login-panel .alert {
            border-radius: 4px;
        }

        @media (max-width: 991.98px) {
            .login-idsolusi-page {
                grid-template-columns: 1fr;
                overflow: auto;
            }

            .login-visual {
                min-height: 42vh;
                padding: 3rem 1.5rem;
                align-items: flex-end;
            }

            .login-visual:after {
                display: none;
            }

            .login-visual-content {
                margin-top: 0;
            }

            .login-panel {
                min-height: auto;
                padding: 2.4rem 1.5rem 3rem;
                box-shadow: none;
            }
        }

        @media (max-width: 575.98px) {
            .login-brand-logo {
                width: 155px;
                margin-bottom: 1rem;
            }

            .login-visual-copy {
                font-size: .95rem;
            }

            .login-options {
                align-items: flex-start;
                flex-direction: column;
                gap: .65rem;
            }
        }
    </style>

    <div class="login-idsolusi-page">
        <section class="login-visual" aria-label="Info Solusindo Data Utama">
            <div class="login-visual-content">
                <img src="{{ asset('assets/img/idsolusi/logo.png') }}" alt="PT Info Solusindo Data Utama" class="login-brand-logo">
                <p class="login-visual-copy">
                    <strong>Monitoring System PMO Maintenance</strong> adalah platform operasional untuk memantau aset, laporan, dan aktivitas maintenance secara terpusat.
                </p>
            </div>
        </section>

        <section class="login-panel" aria-label="Login">
            <div class="login-panel-inner">
                <h1 class="login-title">Selamat Datang</h1>
                <p class="login-subtitle">Silahkan Login dengan akun anda.</p>

                @include('flash::message')

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Username</label>
                        <div class="login-field">
                            <span class="login-field-icon"><i class="far fa-user"></i></span>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Input username..." required autocomplete="email" autofocus>
                        </div>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="login-field login-field-password">
                            <span class="login-field-icon"><i class="fas fa-lock"></i></span>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password" required autocomplete="current-password">
                            <button type="button" class="login-password-toggle" aria-label="Show password" data-password-toggle>
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="login-options">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" id="customCheckLogin" name="remember" {{ old('remember') ? 'checked' : '' }} type="checkbox">
                            <label class="custom-control-label" for="customCheckLogin">Remember me</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="login-forgot-link" href="{{ route('password.request') }}">Forgot Password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-idsolusi">Login</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('[data-password-toggle]');
            var password = document.getElementById('password');

            if (!toggle || !password) {
                return;
            }

            toggle.addEventListener('click', function () {
                var isHidden = password.getAttribute('type') === 'password';
                password.setAttribute('type', isHidden ? 'text' : 'password');
                toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                toggle.innerHTML = isHidden ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
            });
        });
    </script>
@endsection

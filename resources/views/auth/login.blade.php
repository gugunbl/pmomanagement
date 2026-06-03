@extends('layouts.auth')

@section('content')
    <style>
        .login-idsolusi-page {
            min-height: 100vh;
            background: #f7f9fc;
        }

        .login-idsolusi-hero {
            background: linear-gradient(135deg, #da251d 0%, #b51f18 48%, #333333 100%);
            position: relative;
            overflow: hidden;
        }

        .login-idsolusi-hero:before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(255, 255, 255, .16) 0, rgba(255, 255, 255, 0) 34%),
                radial-gradient(circle at 85% 18%, rgba(255, 255, 255, .18), rgba(255, 255, 255, 0) 32%);
        }

        .login-idsolusi-hero .container {
            position: relative;
            z-index: 1;
        }

        .login-brandbar {
            position: relative;
            z-index: 2;
            padding: 22px 24px 0;
        }

        .login-brand-logo {
            max-width: 185px;
            width: 100%;
            height: auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 18px 45px rgba(51, 51, 51, .18);
        }

        .login-title {
            font-weight: 700;
            line-height: 1.2;
        }

        .login-subtitle {
            color: #6b6b6b;
            font-size: 1rem;
            line-height: 1.5;
        }

        .login-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(51, 51, 51, .12);
        }

        .login-card .card-header {
            border-bottom: 1px solid #f0d6d4;
        }

        .login-card-title {
            color: #333333;
            font-weight: 700;
            line-height: 1.35;
        }

        .login-card-title:after {
            content: "";
            display: block;
            width: 48px;
            height: 3px;
            margin: 10px auto 0;
            border-radius: 999px;
            background: #da251d;
        }

        .login-card .input-group-text {
            color: #da251d;
        }

        .login-card .form-control:focus {
            border-color: #da251d;
            box-shadow: 0 0 0 .15rem rgba(218, 37, 29, .18);
        }

        .login-card .custom-control-input:checked ~ .custom-control-label:before {
            border-color: #da251d;
            background-color: #da251d;
        }

        .btn-idsolusi {
            background: #da251d;
            border-color: #da251d;
            box-shadow: 0 4px 12px rgba(218, 37, 29, .32);
        }

        .btn-idsolusi:hover,
        .btn-idsolusi:focus {
            background: #b51f18;
            border-color: #b51f18;
            box-shadow: 0 6px 16px rgba(181, 31, 24, .38);
        }

        .login-forgot-link {
            color: #6b6b6b;
        }

        .login-forgot-link:hover {
            color: #da251d;
        }

        .login-card-wrap {
            margin-top: -11rem;
        }

        @media (max-width: 767.98px) {
            .login-brandbar {
                padding: 18px 18px 0;
                text-align: left;
            }

            .login-brand-logo {
                max-width: 155px;
            }

            .login-card-wrap {
                margin-top: -9rem;
            }
        }
    </style>

<div class="login-idsolusi-page">
    <div class="header login-idsolusi-hero pb-7 pb-lg-8">
        <div class="login-brandbar">
            <img src="{{ asset('assets/img/idsolusi/logo.png') }}" alt="PT Info Solusindo Data Utama" class="login-brand-logo">
        </div>
        <div class="container">
            <div class="header-body text-center mb-7">
                <div class="row justify-content-center">
                    <div class="col-xl-6 col-lg-7 col-md-9 px-5 pt-6 pt-lg-7">
                        <h1 class="text-white login-title">Selamat Datang di Monitoring System PMO Maintenance</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="separator separator-bottom separator-skew zindex-100">
            <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
                <polygon class="fill-white" points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </div>
    <div class="container login-card-wrap pb-5">
        <!-- Table -->
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card bg-white login-card">
                    <div class="card-header px-lg-5 bg-transparent">
                        <h3 class="login-card-title text-center my-2">Silakan masuk menggunakan email dan password akun Anda.</h3>
                        @include('flash::message')
                    </div>
                    <div class="card-body px-lg-5 py-lg-5 pt-2">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                                    </div>
<!--                                    <input id="email" type="email" value="admin@email.com" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
-->
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group input-group-merge input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                                    </div>
<!--                                    <input id="password" type="password" value="secret" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
-->
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror                                </div>
                            </div>
                            <div class="custom-control custom-control-alternative custom-checkbox">
                                <input class="custom-control-input" id=" customCheckLogin"  name="remember" {{ old('remember') ? 'checked' : '' }} type="checkbox">
                                <label class="custom-control-label" for=" customCheckLogin">
                                    <span>Remember me</span>
                                </label>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-idsolusi mt-4">Login</button>
                            </div>
<!--
                            <div class="form-group mt-4 mb-0">
                                <div class="alert alert-info">
                                   Admin Email : admin@email.com , Password: secret!!!
                                </div>
                                <div class="alert alert-info">
                                    User Email : user@email.com , Password: secret
                                 </div>
                            </div>
-->
                        </form>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        @if (Route::has('password.request'))
                            <a class="login-forgot-link" href="{{ route('password.request') }}">
                                Forgot Your Password?
                            </a>
                        @endif
                    </div>
                    {{-- <div class="col-6 text-right">
                        @if (Route::has('register'))
                            <a class="text-gray" href="{{ route('register') }}">
                                Create new account
                            </a>
                        @endif
                    </div> --}}
                </div>


            </div>
        </div>
    </div>
</div>

@endsection

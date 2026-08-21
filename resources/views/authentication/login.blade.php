<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Mailer — Sign in</title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ asset('assets/images/app-logo.svg') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    </head>

    <body>

        <div class="login-card">
            <div class="login-brand">
                <img src="{{ asset('assets/images/app-logo.svg') }}" alt="Mailer logo">
                <span>Mailer</span>
            </div>

            <h3 class="login-title">Welcome back</h3>
            <p class="login-sub">Sign in to open your mailbox</p>

            @if ($errors->any())
                <div class="login-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="login-label" for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control"
                           placeholder="your.username" value="{{ old('username') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="login-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                           placeholder="••••••••" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button class="btn-login w-100">
                    Sign in <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <div class="login-foot">Built by Rohan Singh</div>
        </div>

        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/fontawesome/js/all.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

        @include("partials.sweetalert")
    </body>

</html>

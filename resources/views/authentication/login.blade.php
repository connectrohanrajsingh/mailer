<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

    <head>
        <title>Light Login</title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ asset('assets/images/app-logo.svg')}}">

        <link id="theme-style" rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
        <link id="theme-style" rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    </head>

    <body>

        <div class="login-card">
            <h3 class="login-title">Welcome Back</h3>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Email address">
                </div>

                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label">Remember Me</label>
                    </div>
                </div>

                <button class="btn btn-login w-100">Login</button>
            </form>
        </div>
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js')}}"></script>

        @include("partials.sweetalert")
    </body>

</html>
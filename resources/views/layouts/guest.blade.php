<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GhuriBD') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
        }

        .auth-card {
            width: min(100%, 960px);
            border: 0;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 1.5rem 4rem rgba(15, 23, 42, 0.12);
        }

        .auth-aside {
            background: linear-gradient(160deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card card">
            <div class="row g-0">
                <div class="col-lg-5 auth-aside p-5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-uppercase small text-info fw-semibold mb-3">GhuriBD</div>
                        <h1 class="display-6 fw-bold mb-3">Smart tourism bookings, built for smooth access.</h1>
                        <p class="mb-0 text-white-50">Secure login, registration, password recovery, and logout are ready for the platform.</p>
                    </div>
                    <div class="small text-white-50 mt-5">Laravel authentication powered by the framework's built-in features.</div>
                </div>
                <div class="col-lg-7 p-4 p-md-5 bg-white">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
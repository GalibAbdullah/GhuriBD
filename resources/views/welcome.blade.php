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
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.14), transparent 26%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.12), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
        }

        .hero-card {
            border: 0;
            border-radius: 1.75rem;
            overflow: hidden;
            box-shadow: 0 1.5rem 4rem rgba(15, 23, 42, 0.12);
        }

        .hero-panel {
            background: linear-gradient(160deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="hero-card card">
            <div class="row g-0">
                <div class="col-lg-7 p-5 bg-white">
                    <div class="badge text-bg-primary mb-3">GhuriBD</div>
                    <h1 class="display-5 fw-bold mb-3">Smart tourism management for resorts, tours, and guides.</h1>
                    <p class="text-muted lead mb-4">Authentication is ready for guests and future members of the platform. No dashboard or roles are included yet.</p>

                    <div class="d-flex flex-wrap gap-2">
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">Register</a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-5 hero-panel p-5 d-flex flex-column justify-content-between">
                    <div>
                        <h2 class="h3 fw-bold mb-3">Included authentication</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">Register</li>
                            <li class="mb-2">Login</li>
                            <li class="mb-2">Logout</li>
                            <li class="mb-2">Forgot Password</li>
                            <li class="mb-2">Password Reset</li>
                            <li class="mb-2">Remember Me</li>
                            <li class="mb-2">Password Hashing</li>
                            <li>CSRF Protection</li>
                        </ul>
                    </div>
                    <p class="mb-0 text-white-50">Bootstrap-compatible Blade templates are used so the UI can be styled consistently later.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

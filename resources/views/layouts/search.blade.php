<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Search') — {{ config('app.name', 'GhuriBD') }}</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="min-vh-100 bg-body-tertiary">
    <!-- Public nav -->
    <nav class="sticky-top border-bottom bg-white px-3 px-md-4 py-3" style="z-index: 1020;">
        <div class="mx-auto d-flex align-items-center justify-content-between gap-3" style="max-width: 1180px;">
            @include('partials.logo', ['href' => route('home')])

            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">My Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto px-3 px-md-4 py-4" style="max-width: 1180px;">
        @if (session('status'))
            <div class="alert alert-success mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

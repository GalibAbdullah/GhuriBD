<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'GhuriBD')) — {{ config('app.name', 'GhuriBD') }}</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="min-vh-100">
    <!-- Public nav -->
    <nav class="sticky-top border-bottom bg-white px-3 px-md-4 py-3" style="z-index: 1020;">
        <div class="mx-auto d-flex align-items-center justify-content-between gap-3" style="max-width: 1180px;">
            @include('partials.logo', ['href' => route('home')])

            <div class="d-flex align-items-center gap-4 small fw-medium text-secondary">
                <a href="{{ route('home') }}" class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover">Explore</a>
                <a href="{{ route('home') }}" class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover">Guides</a>
                <a href="{{ route('home') }}" class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover">AI Planner</a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto px-3 py-5" style="max-width: 1180px;">
        @if (session('status'))
            <div class="mx-auto mb-4 alert alert-success" style="max-width: 32rem;" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

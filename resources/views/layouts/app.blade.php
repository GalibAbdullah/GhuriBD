<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'GhuriBD')) — {{ config('app.name', 'GhuriBD') }}</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app" class="min-vh-100">
        <div class="d-flex min-vh-100">
            <!-- Sidebar -->
            <div class="offcanvas-md offcanvas-start border-end bg-white" tabindex="-1" id="sidebar" style="width: 224px;">
                <div class="offcanvas-header d-md-none">
                    @include('partials.logo', ['href' => route('dashboard')])
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
                </div>

                <div class="offcanvas-body d-flex flex-column p-3">
                    <div class="mb-4 d-none d-md-block">
                        @include('partials.logo', ['href' => route('dashboard')])
                    </div>

                    <nav class="list-group list-group-flush">
                        @yield('sidebar')
                    </nav>

                    <div class="small fw-bold text-uppercase text-body-tertiary px-2 pt-4 pb-2" style="letter-spacing: .04em; font-size: .68rem;">Account</div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="flex-shrink-0" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="flex-shrink-0" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
                            My Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded w-100 text-start">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" class="flex-shrink-0" width="16" height="16"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main -->
            <div class="flex-fill min-w-0">
                <!-- Topbar -->
                <header class="sticky-top d-flex align-items-center justify-content-between gap-3 border-bottom bg-white px-3 px-md-4 py-3" style="z-index: 1020;">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm d-md-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Toggle sidebar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                        </button>
                        <h2 class="h5 mb-0">@yield('page-title', config('app.name', 'GhuriBD'))</h2>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">My Dashboard</a>
                            <a href="{{ route('profile.show') }}" class="d-flex align-items-center justify-content-center overflow-hidden rounded-circle border bg-primary-subtle text-primary-emphasis fw-bold" style="width: 34px; height: 34px; font-size: .75rem;">
                                @if (auth()->user()->profile_photo)
                                    <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </a>
                        @endauth
                    </div>
                </header>

                <!-- Content -->
                <main class="px-3 px-md-4 py-4" style="max-width: 1180px;">
                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </div>
</body>
</html>

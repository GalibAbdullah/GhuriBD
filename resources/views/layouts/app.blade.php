<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'GhuriBD')) — {{ config('app.name', 'GhuriBD') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg text-ink">
    <div id="app" class="min-h-screen">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="hidden w-[224px] shrink-0 border-r border-line-soft bg-surface p-3 sm:block lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
                <div class="mb-5">
                    @include('partials.logo', ['href' => route('dashboard')])
                </div>

                <nav class="space-y-0.5">
                    @yield('sidebar')
                </nav>

                <div class="sidebar-section-label mt-4">Account</div>
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('profile.show') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
                    My Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item w-full">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" class="h-4 w-4"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                        Log out
                    </button>
                </form>
            </aside>

            <!-- Main -->
            <div class="min-w-0 flex-1">
                <!-- Topbar -->
                <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-line-soft bg-surface px-7 py-3.5">
                    <h2 class="text-[19px]">@yield('page-title', config('app.name', 'GhuriBD'))</h2>
                    <div class="flex items-center gap-2.5">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">My Dashboard</a>
                            <a href="{{ route('profile.show') }}" class="grid h-[34px] w-[34px] place-items-center overflow-hidden rounded-full border border-line bg-primary-tint text-xs font-bold text-primary-dark">
                                @if (auth()->user()->profile_photo)
                                    <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </a>
                        @endauth
                    </div>
                </header>

                <!-- Content -->
                <main class="max-w-[1180px] px-7 py-7">
                    @if (session('status'))
                        <div class="mb-5 rounded-lg border border-success-tint bg-success-tint px-4 py-3 text-[13px] font-medium text-success" role="alert">
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
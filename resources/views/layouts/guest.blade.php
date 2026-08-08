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
    <!-- Public nav -->
    <nav class="sticky top-0 z-30 border-b border-line-soft bg-surface px-7 py-3.5">
        <div class="mx-auto flex max-w-[1180px] items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="grid h-6 w-6 place-items-center text-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2l7 8-3 11-4-6-4 6-3-11 7-8z"/><path d="M12 2v19"/></svg>
                </span>
                <span class="font-display text-[17px] font-bold text-secondary">Ghuri<span class="text-primary">BD</span></span>
            </a>

            <div class="flex items-center gap-6 text-[14px] font-medium text-ink-muted">
                <a href="{{ route('home') }}" class="hover:text-primary">Explore</a>
                <a href="{{ route('home') }}" class="hover:text-primary">Guides</a>
                <a href="{{ route('home') }}" class="hover:text-primary">AI Planner</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-[1180px] px-4 py-8">
        @if (session('status'))
            <div class="mx-auto mb-6 max-w-lg rounded-lg border border-success-tint bg-success-tint px-4 py-3 text-[13px] font-medium text-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
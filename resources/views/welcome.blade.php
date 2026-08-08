<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GhuriBD') }} — Smart Tourism Management</title>

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

            <div class="hidden items-center gap-6 text-[14px] font-medium text-ink-muted md:flex">
                <a href="#explore" class="hover:text-primary">Explore</a>
                <a href="#guides" class="hover:text-primary">Guides</a>
                <a href="#ai" class="hover:text-primary">AI Planner</a>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('profile.show') }}" class="btn btn-ghost btn-sm">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="relative overflow-hidden bg-gradient-to-b from-secondary to-[#1E3055] text-white">
        <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-accent/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-[1180px] px-7 pb-20 pt-16">
            <h1 class="max-w-[640px] font-display text-4xl font-bold leading-tight text-white">
                Plan Bangladesh,<br>beautifully.
            </h1>
            <p class="mt-3 max-w-[520px] text-[16px] leading-relaxed text-[#B9C2D4]">
                Resorts, tour packages, and local guides across Cox's Bazar, Sajek, Bandarban and beyond — booked in one trip.
            </p>

            <div class="mt-7 max-w-[640px]">
                <div class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="input-label text-ink-muted">Destination</label>
                        <input class="input" placeholder="Cox's Bazar" readonly>
                    </div>
                    <div class="flex-1">
                        <label class="input-label text-ink-muted">Dates</label>
                        <input class="input" placeholder="12 – 15 Sep" readonly>
                    </div>
                    <div class="flex-1">
                        <label class="input-label text-ink-muted">Guests</label>
                        <input class="input" placeholder="2 adults" readonly>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-accent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        Search
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Popular destinations -->
    <section class="mx-auto max-w-[1180px] px-7 py-10" id="explore">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-[16px]">Popular destinations</h3>
        </div>
        <div class="mb-11 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (["Cox's Bazar", 'Sajek Valley', 'Bandarban', 'Sylhet'] as $dest)
                <div class="card hoverable cursor-pointer overflow-hidden" onclick="document.querySelector('#register')?.scrollIntoView({behavior:'smooth'})">
                    <div class="grid aspect-square place-items-center bg-gradient-to-br from-primary-tint to-accent-tint text-4xl">🏝️</div>
                    <div class="px-3 py-2.5 text-[13px] font-bold">{{ $dest }}</div>
                </div>
            @endforeach
        </div>

        <!-- Featured resorts / packages empty -->
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-[16px]">Featured resorts & tours</h3>
        </div>
        <div class="card card-pad mb-11">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                <h3>Resorts and packages are coming soon</h3>
                <p>Browse handpicked stays and itineraries once the marketplace is live.</p>
                <div class="mt-4">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get notified</a>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Planner CTA -->
    <section class="mx-auto max-w-[1180px] px-7 pb-12" id="ai">
        <div class="card flex flex-wrap items-center justify-between gap-5 bg-secondary p-7 !text-white">
            <div>
                <h3 class="!text-white text-[18px]">Not sure where to start?</h3>
                <p class="mt-1 text-[13px] text-[#B9C2D4]">Let the AI Tour Planner build a day-by-day itinerary around your budget.</p>
            </div>
            <a href="{{ route('register') }}" class="btn btn-accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg>
                Try AI Planner
            </a>
        </div>
    </section>

    <footer class="border-t border-line-soft bg-surface py-4">
        <div class="mx-auto max-w-[1180px] px-7 text-center text-[12.5px] text-ink-faint">
            &copy; {{ date('Y') }} {{ config('app.name', 'GhuriBD') }}. Built for smart tourism.
        </div>
    </footer>
</body>
</html>
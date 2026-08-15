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
            @include('partials.logo', ['href' => route('home')])

            <div class="hidden items-center gap-6 text-[14px] font-medium text-ink-muted md:flex">
                <a href="#explore" class="hover:text-primary">Explore</a>
                <a href="#guides" class="hover:text-primary">Guides</a>
                <a href="#ai" class="hover:text-primary">AI Planner</a>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">My Dashboard</a>
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
        {{--
            Bangladesh drone-shot slideshow background.
            Each video is a raw aerial clip of a famous Bangladeshi destination,
            displayed at LOW opacity (~50%) so the text stays readable.
            To use your own footage, just replace the src URLs below with your
            own .mp4 files (e.g. put them in /public/videos/).
        --}}
        <div class="hero-videos absolute inset-0" aria-hidden="true">
            <video class="hero-video hero-video-active" data-location="Cox's Bazar — World's longest sea beach" muted loop playsinline preload="metadata">
                <source src="https://videos.pexels.com/video-files/857195/857195-hd_1920_1080_25fps.mp4" type="video/mp4">
            </video>
            <video class="hero-video" data-location="Sajek Valley — The roof of Bangladesh" muted loop playsinline preload="metadata">
                <source src="https://videos.pexels.com/video-files/1768757/1768757-hd_1920_1080_25fps.mp4" type="video/mp4">
            </video>
            <video class="hero-video" data-location="Sundarbans — Mangrove forest" muted loop playsinline preload="metadata">
                <source src="https://videos.pexels.com/video-files/3054705/3054705-hd_1920_1080_25fps.mp4" type="video/mp4">
            </video>
            <video class="hero-video" data-location="Bandarban — Green hills of Chattogram" muted loop playsinline preload="metadata">
                <source src="https://videos.pexels.com/video-files/1093662/1093662-hd_1920_1080_25fps.mp4" type="video/mp4">
            </video>
            <video class="hero-video" data-location="Bay of Bengal — Aerial coastline" muted loop playsinline preload="metadata">
                <source src="https://videos.pexels.com/video-files/1409899/1409899-hd_1920_1080_30fps.mp4" type="video/mp4">
            </video>
        </div>

        {{-- Ken Burns animated fallback (shows if video can't load) --}}
        <div class="hero-kenburns absolute inset-0" aria-hidden="true"></div>

        {{-- Location caption (bottom-left) --}}
        <div class="hero-location absolute bottom-6 left-7 z-10 flex items-center gap-2 text-[12.5px] font-semibold tracking-wide text-white/80" aria-hidden="true">
            <span class="hero-location-dot h-1.5 w-1.5 rounded-full bg-accent"></span>
            <span id="hero-location-label">Cox's Bazar — World's longest sea beach</span>
        </div>

        {{-- Light readability overlay (video is already at low opacity) --}}
        <div class="absolute inset-0 bg-gradient-to-b from-secondary/60 via-secondary/30 to-[#1E3055]/70" aria-hidden="true"></div>

        {{-- Floating clouds --}}
        <div class="hero-cloud hero-cloud-1" aria-hidden="true">
            <svg viewBox="0 0 200 60" fill="none" class="h-10 w-32 text-white/20"><path d="M20 45a18 18 0 0 1 0-36 22 22 0 0 1 42-4 16 16 0 0 1 2 32H20z" fill="currentColor"/></svg>
        </div>
        <div class="hero-cloud hero-cloud-2" aria-hidden="true">
            <svg viewBox="0 0 200 60" fill="none" class="h-8 w-24 text-white/15"><path d="M20 45a18 18 0 0 1 0-36 22 22 0 0 1 42-4 16 16 0 0 1 2 32H20z" fill="currentColor"/></svg>
        </div>
        <div class="hero-cloud hero-cloud-3" aria-hidden="true">
            <svg viewBox="0 0 200 60" fill="none" class="h-12 w-36 text-white/10"><path d="M20 45a18 18 0 0 1 0-36 22 22 0 0 1 42-4 16 16 0 0 1 2 32H20z" fill="currentColor"/></svg>
        </div>

        {{-- Floating birds --}}
        <div class="hero-bird hero-bird-1" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-6 text-white/40"><path d="M2 12c3-2 5-2 8 0s5 2 8 0M4 16c2.5-1.5 4.5-1.5 7 0s4.5 1.5 7 0"/></svg>
        </div>
        <div class="hero-bird hero-bird-2" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-3 w-5 text-white/30"><path d="M2 12c3-2 5-2 8 0s5 2 8 0M4 16c2.5-1.5 4.5-1.5 7 0s4.5 1.5 7 0"/></svg>
        </div>

        {{-- Floating particles --}}
        <div class="hero-particles" aria-hidden="true">
            <span class="hero-particle" style="left:12%;top:30%;animation-delay:0s"></span>
            <span class="hero-particle" style="left:28%;top:65%;animation-delay:1.2s"></span>
            <span class="hero-particle" style="left:45%;top:22%;animation-delay:2.1s"></span>
            <span class="hero-particle" style="left:62%;top:70%;animation-delay:0.6s"></span>
            <span class="hero-particle" style="left:78%;top:35%;animation-delay:1.8s"></span>
            <span class="hero-particle" style="left:88%;top:60%;animation-delay:2.6s"></span>
            <span class="hero-particle" style="left:55%;top:45%;animation-delay:3.2s"></span>
            <span class="hero-particle" style="left:20%;top:80%;animation-delay:0.9s"></span>
        </div>

        {{-- Soft glow accents --}}
        <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-accent/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-20 bottom-0 h-72 w-72 rounded-full bg-primary/20 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-[1180px] px-7 pb-20 pt-16">
            <h1 class="hero-title max-w-[640px] font-display text-4xl font-bold leading-tight text-white">
                Plan Bangladesh,<br>beautifully.
            </h1>
            <p class="hero-subtitle mt-3 max-w-[520px] text-[16px] leading-relaxed text-[#B9C2D4]">
                Resorts, tour packages, and local guides across Cox's Bazar, Sajek, Bandarban and beyond — booked in one trip.
            </p>

            <div class="hero-search mt-7 max-w-[640px]">
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
                <div class="card hoverable cursor-pointer overflow-hidden" onclick="document.querySelector('#explore')?.scrollIntoView({behavior:'smooth'})">
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
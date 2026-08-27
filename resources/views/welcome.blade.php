<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GhuriBD') }} — Smart Tourism Management</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="min-vh-100">
    <!-- Public nav -->
    <nav class="sticky-top border-bottom bg-white px-3 px-md-4 py-3" style="z-index: 1020;">
        <div class="mx-auto d-flex align-items-center justify-content-between gap-3" style="max-width: 1180px;">
            @include('partials.logo', ['href' => route('home')])

            <div class="d-none d-md-flex align-items-center gap-4 small fw-medium text-secondary">
                <a href="#explore" class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover">Explore</a>
                <a href="#guides" class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover">Guides</a>
                <a href="#ai" class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover">AI Planner</a>
            </div>

            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">My Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="position-relative overflow-hidden bg-hero-gradient text-white">
        {{--
            Bangladesh drone-shot slideshow background.
            Each video is a raw aerial clip of a famous Bangladeshi destination,
            displayed at LOW opacity (~50%) so the text stays readable.
            To use your own footage, just replace the src URLs below with your
            own .mp4 files (e.g. put them in /public/videos/).
        --}}
        <div class="hero-videos position-absolute top-0 start-0 w-100 h-100" aria-hidden="true">
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
        <div class="hero-kenburns position-absolute top-0 start-0 w-100 h-100" aria-hidden="true"></div>

        {{-- Location caption (bottom-left) --}}
        <div class="hero-location position-absolute bottom-0 start-0 z-1 d-flex align-items-center gap-2 small fw-semibold text-white-50 mb-4 ms-4" aria-hidden="true">
            <span class="hero-location-dot rounded-circle bg-warning" style="width: .375rem; height: .375rem;"></span>
            <span id="hero-location-label">Cox's Bazar — World's longest sea beach</span>
        </div>

        {{-- Light readability overlay (video is already at low opacity) --}}
        <div class="bg-hero-overlay position-absolute top-0 start-0 w-100 h-100" aria-hidden="true"></div>

        {{-- Floating clouds --}}
        <div class="hero-cloud hero-cloud-1" aria-hidden="true">
            <svg viewBox="0 0 200 60" fill="none" class="text-white-50" width="128" height="40"><path d="M20 45a18 18 0 0 1 0-36 22 22 0 0 1 42-4 16 16 0 0 1 2 32H20z" fill="currentColor"/></svg>
        </div>
        <div class="hero-cloud hero-cloud-2" aria-hidden="true">
            <svg viewBox="0 0 200 60" fill="none" class="text-white-50" width="96" height="32"><path d="M20 45a18 18 0 0 1 0-36 22 22 0 0 1 42-4 16 16 0 0 1 2 32H20z" fill="currentColor"/></svg>
        </div>
        <div class="hero-cloud hero-cloud-3" aria-hidden="true">
            <svg viewBox="0 0 200 60" fill="none" class="text-white-50" width="144" height="48"><path d="M20 45a18 18 0 0 1 0-36 22 22 0 0 1 42-4 16 16 0 0 1 2 32H20z" fill="currentColor"/></svg>
        </div>

        {{-- Floating birds --}}
        <div class="hero-bird hero-bird-1" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="text-white-50" width="24" height="16"><path d="M2 12c3-2 5-2 8 0s5 2 8 0M4 16c2.5-1.5 4.5-1.5 7 0s4.5 1.5 7 0"/></svg>
        </div>
        <div class="hero-bird hero-bird-2" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="text-white-50" width="20" height="12"><path d="M2 12c3-2 5-2 8 0s5 2 8 0M4 16c2.5-1.5 4.5-1.5 7 0s4.5 1.5 7 0"/></svg>
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
        <div class="blur-glow pointer-events-none position-absolute rounded-circle bg-warning bg-opacity-10" style="right: -4rem; top: -4rem; width: 20rem; height: 20rem;" aria-hidden="true"></div>
        <div class="blur-glow pointer-events-none position-absolute rounded-circle bg-success bg-opacity-25" style="left: -5rem; bottom: 0; width: 18rem; height: 18rem;" aria-hidden="true"></div>

        <div class="position-relative mx-auto px-3 px-md-4 pb-5 pt-5" style="max-width: 1180px;">
            <h1 class="hero-title fw-bold lh-tight text-white" style="max-width: 640px; font-size: 2.25rem;">
                Plan Bangladesh,<br>beautifully.
            </h1>
            <p class="hero-subtitle mt-3 text-white-50" style="max-width: 520px; font-size: 1rem;">
                Resorts, tour packages, and local guides across Cox's Bazar, Sajek, Bandarban and beyond — booked in one trip.
            </p>

            <div class="hero-search mt-4" style="max-width: 640px;">
                <div class="card p-3">
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-end">
                        <div class="flex-fill">
                            <label class="form-label text-secondary">Destination</label>
                            <input class="form-control" placeholder="Cox's Bazar" readonly>
                        </div>
                        <div class="flex-fill">
                            <label class="form-label text-secondary">Dates</label>
                            <input class="form-control" placeholder="12 – 15 Sep" readonly>
                        </div>
                        <div class="flex-fill">
                            <label class="form-label text-secondary">Guests</label>
                            <input class="form-control" placeholder="2 adults" readonly>
                        </div>
                        <a href="{{ route('register') }}" class="btn btn-warning d-flex align-items-center gap-2 justify-content-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                            Search
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Popular destinations -->
    <section class="mx-auto px-3 px-md-4 py-5" id="explore" style="max-width: 1180px;">
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Popular destinations</h3>
        </div>
        <div class="row row-cols-2 row-cols-lg-4 g-4 mb-5">
            @foreach (["Cox's Bazar", 'Sajek Valley', 'Bandarban', 'Sylhet'] as $dest)
                <div class="col">
                    <div class="card overflow-hidden h-100" role="button" onclick="document.querySelector('#explore')?.scrollIntoView({behavior:'smooth'})">
                        <div class="d-flex align-items-center justify-content-center bg-destination-gradient fs-1 ratio ratio-1x1">🏝️</div>
                        <div class="px-3 py-2 small fw-bold">{{ $dest }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Featured resorts / packages empty -->
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Featured resorts &amp; tours</h3>
        </div>
        <div class="card mb-5">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                    <h3>Resorts and packages are coming soon</h3>
                    <p>Browse handpicked stays and itineraries once the marketplace is live.</p>
                    <div class="mt-3">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get notified</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Planner CTA -->
    <section class="mx-auto px-3 px-md-4 pb-5" id="ai" style="max-width: 1180px;">
        <div class="card flex-row flex-wrap align-items-center justify-content-between gap-4 bg-dark text-white p-4 border-0">
            <div>
                <h3 class="text-white h5">Not sure where to start?</h3>
                <p class="mt-1 small text-white-50">Let the AI Tour Planner build a day-by-day itinerary around your budget.</p>
            </div>
            <a href="{{ route('register') }}" class="btn btn-warning d-flex align-items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg>
                Try AI Planner
            </a>
        </div>
    </section>

    <footer class="border-top bg-white py-3">
        <div class="mx-auto px-3 px-md-4 text-center small text-body-tertiary" style="max-width: 1180px;">
            &copy; {{ date('Y') }} {{ config('app.name', 'GhuriBD') }}. Built for smart tourism.
        </div>
    </footer>
</body>
</html>

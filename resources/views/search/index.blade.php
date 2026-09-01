@extends('layouts.search')

@section('title', 'Search')

@section('content')
    <div class="mx-auto text-center py-4 py-md-5" style="max-width: 640px;">
        <h1 class="h3 fw-bold mb-2">Where do you want to travel?</h1>
        <p class="text-secondary mb-4">Search resorts and tour packages across Bangladesh in one place.</p>

        <form method="GET" action="{{ route('search.results') }}">
            <div class="card p-2 shadow-sm">
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <div class="input-group flex-fill">
                        <span class="input-group-text bg-white border-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        </span>
                        <input type="search" name="q" class="form-control border-0" placeholder="Where do you want to travel?" autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                </div>
            </div>
        </form>

        <div class="d-flex align-items-center justify-content-center gap-3 mt-3">
            <a href="{{ route('search.results', ['tab' => 'resorts']) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                Resorts
            </a>
            <a href="{{ route('search.results', ['tab' => 'packages']) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                Tour Packages
            </a>
        </div>
    </div>

    @if (! empty($recentSearches))
        <div class="mx-auto mb-4" style="max-width: 720px;">
            <h3 class="h6 mb-3">Recent Searches</h3>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($recentSearches as $term)
                    <a href="{{ route('search.results', ['q' => $term]) }}" class="badge rounded-pill text-bg-light border text-decoration-none px-3 py-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="12" height="12" class="me-1"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        {{ $term }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mx-auto mb-5" style="max-width: 720px;">
        <h3 class="h6 mb-3">Popular Destinations</h3>
        <div class="row row-cols-2 row-cols-sm-3 g-3">
            @foreach ($popularDestinations as $destination)
                <div class="col">
                    <a href="{{ route('search.results', ['q' => $destination]) }}" class="card h-100 text-decoration-none text-reset shadow-sm">
                        <div class="card-body d-flex align-items-center gap-2">
                            <span class="fs-5">🏝️</span>
                            <span class="small fw-semibold">{{ $destination }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Traveler Dashboard')
@section('page-title')
    Welcome back, {{ auth()->user()->name }}
@endsection

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    @php
        $upcomingBookingsQuery = auth()->user()->bookings()->upcoming();
        $upcomingCount = (clone $upcomingBookingsQuery)->count();
        $nextTrip = (clone $upcomingBookingsQuery)->with(['resort', 'tourPackage'])->orderByRaw('COALESCE(travel_date, check_in_date) asc')->first();
    @endphp

    <div class="mb-3 small text-secondary">
        Welcome back! Explore the best of Bangladesh.
    </div>

    <!-- Stats -->
    <div class="row row-cols-1 row-cols-sm-3 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Upcoming trips</div><div class="fs-3 fw-bold mt-2">{{ $upcomingCount }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Wishlist items</div><div class="fs-3 fw-bold mt-2">0</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Reviews written</div><div class="fs-3 fw-bold mt-2">0</div></div></div></div>
    </div>

    <!-- Next trip -->
    <div class="mb-4">
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Next trip</h3>
            @if ($nextTrip)
                <a href="{{ route('traveler.bookings.index') }}" class="small fw-semibold link-primary link-underline-opacity-0">View all</a>
            @endif
        </div>
        @if ($nextTrip)
            <a href="{{ route('traveler.bookings.show', $nextTrip) }}" class="card text-decoration-none text-reset">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="small text-secondary font-monospace">{{ $nextTrip->booking_reference }}</div>
                        <h4 class="h6 fw-semibold mb-0 mt-1">
                            {{ $nextTrip->resort?->name }}
                            @if ($nextTrip->resort && $nextTrip->tourPackage) + @endif
                            {{ $nextTrip->tourPackage?->title }}
                        </h4>
                        <div class="small text-secondary mt-1">
                            {{ ($nextTrip->travel_date ?? $nextTrip->check_in_date)?->format('M d, Y') }}
                        </div>
                    </div>
                    <span class="badge text-bg-warning">{{ $nextTrip->booking_status }}</span>
                </div>
            </a>
        @else
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                        <h3>No upcoming trips yet</h3>
                        <p>Once you book a resort or tour, it'll show up here.</p>
                        <div class="mt-3">
                            <a href="{{ route('traveler.resorts.index') }}" class="btn btn-primary btn-sm">Start exploring</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div>
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Recommended for you</h3>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><circle cx="12" cy="12" r="9"/><path d="M15 9l-2 6-6 2 2-6 6-2z"/></svg>
                    <h3>Discover resorts &amp; tours</h3>
                    <p>Personalized recommendations will appear as you explore the platform.</p>
                    <div class="mt-3">
                        <a href="{{ route('traveler.resorts.index') }}" class="btn btn-outline-secondary btn-sm">Browse Resorts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

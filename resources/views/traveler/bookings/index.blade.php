@extends('layouts.app')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@php
    $statusColors = ['Pending' => 'warning', 'Confirmed' => 'success', 'Cancelled' => 'secondary', 'Completed' => 'primary'];
@endphp

@section('content')
    <div class="mb-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">My Bookings</h3>
            <p class="mb-0 small text-secondary">Track your upcoming trips and booking history.</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
                New Booking
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('traveler.resorts.index') }}">Book a Resort</a></li>
                <li><a class="dropdown-item" href="{{ route('traveler.packages.index') }}">Book a Tour Package</a></li>
                <li><a class="dropdown-item" href="{{ route('bookings.combined.create') }}">Combined Booking</a></li>
            </ul>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $scope === 'upcoming' ? 'active' : '' }}" href="{{ route('traveler.bookings.index', ['scope' => 'upcoming']) }}">Upcoming</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $scope === 'history' ? 'active' : '' }}" href="{{ route('traveler.bookings.index', ['scope' => 'history']) }}">History</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $scope === 'all' ? 'active' : '' }}" href="{{ route('traveler.bookings.index', ['scope' => 'all']) }}">All Bookings</a>
        </li>
    </ul>

    @if ($bookings->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                    <h3>{{ $scope === 'upcoming' ? 'No upcoming bookings.' : 'No bookings found.' }}</h3>
                    <p>Once you book a resort or tour package, it'll show up here.</p>
                    <a href="{{ route('traveler.resorts.index') }}" class="btn btn-primary btn-sm mt-2">Start Exploring</a>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-lg-2 g-4">
            @foreach ($bookings as $booking)
                <div class="col">
                    <a href="{{ route('traveler.bookings.show', $booking) }}" class="card h-100 text-decoration-none text-reset">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge text-bg-light border text-capitalize">{{ $booking->booking_type }}</span>
                                <span class="badge text-bg-{{ $statusColors[$booking->booking_status] ?? 'secondary' }}">{{ $booking->booking_status }}</span>
                            </div>

                            <h4 class="h6 fw-semibold mb-1">
                                {{ $booking->resort?->name }}
                                @if ($booking->resort && $booking->tourPackage) + @endif
                                {{ $booking->tourPackage?->title }}
                            </h4>

                            <div class="small text-secondary mb-2">
                                @if ($booking->check_in_date)
                                    {{ $booking->check_in_date->format('M d') }} – {{ $booking->check_out_date?->format('M d, Y') }}
                                @endif
                                @if ($booking->check_in_date && $booking->travel_date) &middot; @endif
                                @if ($booking->travel_date)
                                    Travel: {{ $booking->travel_date->format('M d, Y') }}
                                @endif
                            </div>

                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small font-monospace text-secondary">{{ $booking->booking_reference }}</span>
                                <span class="fw-semibold font-monospace">৳{{ number_format($booking->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if ($bookings->hasPages())
            <div class="mt-4">{{ $bookings->links() }}</div>
        @endif
    @endif
@endsection

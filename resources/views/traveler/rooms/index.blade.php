@extends('layouts.app')

@section('title', 'Rooms')
@section('page-title', 'Rooms')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.resorts.show', $resort) }}" class="small fw-semibold link-secondary link-underline-opacity-0">
            &larr; Back to {{ $resort->name }}
        </a>
    </div>

    <div class="mb-4">
        <h3 class="h5 mb-1">{{ $resort->name }}</h3>
        <p class="mb-0 small text-secondary">Available room types</p>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('traveler.resorts.rooms.index', $resort) }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by room name">
        </div>
    </form>

    @if ($rooms->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    <h3>{{ $search ? 'No rooms match your search.' : 'No rooms added yet.' }}</h3>
                    <p>Check back soon for available rooms.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach ($rooms as $room)
                <div class="col">
                    <a href="{{ route('traveler.resorts.rooms.show', [$resort, $room]) }}" class="card h-100 text-decoration-none text-reset">
                        <img src="{{ $room->cover_image_url }}" alt="{{ $room->room_name }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h4 class="h6 fw-semibold mb-0">{{ $room->room_name }}</h4>
                                @if ($room->isAvailable())
                                    <span class="badge text-bg-success">Available</span>
                                @else
                                    <span class="badge text-bg-secondary">Unavailable</span>
                                @endif
                            </div>
                            <div class="small text-secondary mb-2">{{ $room->room_type }} &middot; Fits {{ $room->capacity }}</div>
                            <div class="fw-semibold font-monospace">৳{{ number_format($room->price_per_night, 2) }} <span class="fw-normal small text-secondary">/ night</span></div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if ($rooms->hasPages())
            <div class="mt-4">
                {{ $rooms->links() }}
            </div>
        @endif
    @endif
@endsection

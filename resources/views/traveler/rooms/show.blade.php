@extends('layouts.app')

@section('title', $room->room_name)
@section('page-title', $room->room_name)

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.resorts.rooms.index', $resort) }}" class="small fw-semibold link-secondary link-underline-opacity-0">
            &larr; Back to Rooms
        </a>
    </div>

    <!-- Hero -->
    <div class="rounded-4 overflow-hidden mb-4" style="height: 280px;">
        <img src="{{ $room->cover_image_url }}" alt="{{ $room->room_name }}" class="w-100 h-100" style="object-fit: cover;">
    </div>

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h4 mb-1">{{ $room->room_name }}</h3>
            <div class="small text-secondary">{{ $resort->name }} &middot; {{ $room->room_type }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($room->isAvailable())
                <span class="badge text-bg-success">Available</span>
            @else
                <span class="badge text-bg-secondary">Unavailable</span>
            @endif
            <a href="{{ route('bookings.resorts.create', [$resort, $room]) }}" class="btn btn-primary btn-sm">Book Now</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Description</h4>
                    <p class="mb-0 text-body">{{ $room->description }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Amenities</h4>
                    @if (! empty($room->amenities))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($room->amenities as $amenity)
                                <span class="badge text-bg-light border">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 small text-secondary">No amenities listed.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Gallery</h4>
                    @if ($room->images->isNotEmpty())
                        <div class="row row-cols-2 row-cols-sm-3 g-2">
                            @foreach ($room->images as $image)
                                <div class="col">
                                    <img src="{{ $image->image_url }}" alt="" class="rounded border w-100" style="aspect-ratio: 1 / 1; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-4">
                            <p class="mb-0">No gallery images added yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="small text-secondary fw-semibold">Price Per Night</div>
                    <div class="fs-3 fw-bold mt-1 font-monospace">৳{{ number_format($room->price_per_night, 2) }}</div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Room Details</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Capacity</span>
                            <span class="text-body fw-semibold">{{ $room->capacity }} guests</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Bed Type</span>
                            <span class="text-body fw-semibold">{{ $room->bed_type }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Room Size</span>
                            <span class="text-body fw-semibold">{{ $room->room_size }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

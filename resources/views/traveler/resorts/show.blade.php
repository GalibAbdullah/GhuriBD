@extends('layouts.app')

@section('title', $resort->name)
@section('page-title', $resort->name)

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.resorts.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">
            &larr; Back to Resorts
        </a>
    </div>

    <!-- Hero -->
    <div class="position-relative rounded-4 overflow-hidden mb-4" style="height: 280px;">
        <img src="{{ $resort->cover_image_url }}" alt="{{ $resort->name }}" class="w-100 h-100" style="object-fit: cover;">
        @include('wishlist.partials.heart-button', ['type' => 'resort', 'model' => $resort, 'wishlisted' => $isWishlisted])
    </div>

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h4 mb-1">{{ $resort->name }}</h3>
            <div class="small text-secondary">{{ $resort->district }}, {{ $resort->division }}</div>
        </div>
        <a href="{{ route('traveler.resorts.rooms.index', $resort) }}" class="btn btn-primary btn-sm">View Rooms ({{ $resort->rooms_count }})</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Description</h4>
                    <p class="mb-0 text-body">{{ $resort->description }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Amenities</h4>
                    @if (! empty($resort->amenities))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($resort->amenities as $amenity)
                                <span class="badge text-bg-light border">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 small text-secondary">No amenities listed.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Gallery</h4>
                    @if ($resort->images->isNotEmpty())
                        <div class="row row-cols-2 row-cols-sm-3 g-2">
                            @foreach ($resort->images as $image)
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

            @include('reviews.partials._summary', ['reviews' => $resort->reviews, 'ratingCounts' => $ratingCounts])

            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">What Travelers Say</h4>
                    @forelse ($resort->reviews as $review)
                        @include('reviews.partials._review-card', ['review' => $review])
                    @empty
                        <div class="empty-state py-4">
                            <p class="mb-0">No reviews yet. Be the first to share your experience.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Location</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Division</span>
                            <span class="text-body fw-semibold">{{ $resort->division }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">District</span>
                            <span class="text-body fw-semibold">{{ $resort->district }}</span>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <span class="text-secondary d-block mb-1">Address</span>
                            <span class="text-body">{{ $resort->address }}</span>
                        </div>
                    </div>

                    <hr>
                    @include('maps.partials._view-map', ['model' => $resort, 'title' => $resort->name])
                </div>
            </div>

            @include('weather.partials._card', ['forecast' => $forecast])

            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Contact</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Phone</span>
                            <span class="font-monospace fw-semibold">{{ $resort->contact_phone }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Price Range</span>
                            <span class="text-body fw-semibold">{{ $resort->price_range }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<div class="col">
    <div class="position-relative h-100">
        @include('wishlist.partials.heart-button', ['type' => 'resort', 'model' => $resort, 'wishlisted' => ($wishlistedResortIds ?? collect())->contains($resort->id)])
        @if ($resort->hasCoordinates())
            <a href="{{ $resort->googleMapsUrl() }}" target="_blank" rel="noopener" class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 text-decoration-none" style="z-index: 2;" onclick="event.stopPropagation()">View on Map</a>
        @endif
        <a href="{{ route('traveler.resorts.show', $resort) }}" class="card h-100 text-decoration-none text-reset shadow-sm">
            <img src="{{ $resort->cover_image_url }}" alt="{{ $resort->name }}" class="card-img-top" style="height: 160px; object-fit: cover;">
            <div class="card-body">
                <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle mb-2">Resort</span>
                <h4 class="h6 fw-semibold mb-1">{{ $resort->name }}</h4>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="small text-secondary">{{ $resort->district }}, {{ $resort->division }}</span>
                    @include('weather.partials._badge', ['model' => $resort])
                </div>

                @if (! empty($resort->amenities))
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach (array_slice($resort->amenities, 0, 3) as $amenity)
                            <span class="badge text-bg-light border">{{ $amenity }}</span>
                        @endforeach
                        @if (count($resort->amenities) > 3)
                            <span class="badge text-bg-light border">+{{ count($resort->amenities) - 3 }}</span>
                        @endif
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="small fw-semibold">{{ $resort->price_range }}</span>
                    <span class="small fw-semibold link-primary">View Details</span>
                </div>
            </div>
        </a>
    </div>
</div>

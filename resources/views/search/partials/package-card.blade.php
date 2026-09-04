<div class="col">
    <div class="position-relative h-100">
        @include('wishlist.partials.heart-button', ['type' => 'package', 'model' => $package, 'wishlisted' => ($wishlistedPackageIds ?? collect())->contains($package->id)])
        <a href="{{ route('traveler.packages.show', $package) }}" class="card h-100 text-decoration-none text-reset shadow-sm">
            <img src="{{ $package->cover_image_url }}" alt="{{ $package->title }}" class="card-img-top" style="height: 160px; object-fit: cover;">
            <div class="card-body">
                <span class="badge text-bg-primary-subtle text-primary-emphasis border border-primary-subtle mb-2">Tour Package</span>
                <h4 class="h6 fw-semibold mb-1">{{ $package->title }}</h4>
                <div class="small text-secondary mb-2">{{ $package->destination }} &middot; {{ $package->duration_days }}D / {{ $package->duration_nights }}N</div>

                <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold font-monospace">৳{{ number_format($package->price, 2) }}</span>
                    <span class="badge text-bg-light border">Up to {{ $package->max_travelers }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-end mt-2">
                    <span class="small fw-semibold link-primary">View Details</span>
                </div>
            </div>
        </a>
    </div>
</div>

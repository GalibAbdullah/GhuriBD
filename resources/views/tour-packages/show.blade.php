@extends('layouts.app')

@section('title', $tourPackage->title)
@section('page-title', $tourPackage->title)

@section('sidebar')
    @include('tour-packages.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route($isAdmin ? 'admin.packages.index' : 'partner.packages.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">
            &larr; Back to {{ $isAdmin ? 'All Tour Packages' : 'My Tour Packages' }}
        </a>
    </div>

    <!-- Hero -->
    <div class="rounded-4 overflow-hidden mb-4" style="height: 280px;">
        <img src="{{ $tourPackage->cover_image_url }}" alt="{{ $tourPackage->title }}" class="w-100 h-100" style="object-fit: cover;">
    </div>

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h4 mb-1">{{ $tourPackage->title }}</h3>
            <div class="small text-secondary">{{ $tourPackage->destination }} &middot; {{ $tourPackage->district }}, {{ $tourPackage->division }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($tourPackage->isActive())
                <span class="badge text-bg-success">Active</span>
            @else
                <span class="badge text-bg-secondary">Inactive</span>
            @endif
            <span class="badge text-bg-light border">{{ $tourPackage->duration_days }}D / {{ $tourPackage->duration_nights }}N</span>

            @unless ($isAdmin)
                <a href="{{ route('partner.packages.edit', $tourPackage) }}" class="btn btn-outline-secondary btn-sm">Edit Package</a>
            @endunless
        </div>
    </div>

    @if ($isAdmin)
        <div class="alert alert-secondary small mb-4">
            Listed by <strong>{{ $tourPackage->user->name }}</strong> ({{ $tourPackage->user->email }})
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Description</h4>
                    <p class="mb-0 text-body">{{ $tourPackage->description }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Itinerary</h4>
                    <p class="mb-0 text-body" style="white-space: pre-line;">{{ $tourPackage->itinerary }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <h4 class="h6 fw-semibold mb-3">Included Services</h4>
                            @if (! empty($tourPackage->included_services))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($tourPackage->included_services as $service)
                                        <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">{{ $service }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mb-0 small text-secondary">Nothing listed.</p>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <h4 class="h6 fw-semibold mb-3">Excluded Services</h4>
                            @if (! empty($tourPackage->excluded_services))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($tourPackage->excluded_services as $service)
                                        <span class="badge text-bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ $service }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mb-0 small text-secondary">Nothing listed.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Gallery</h4>
                    @if ($tourPackage->images->isNotEmpty())
                        <div class="row row-cols-2 row-cols-sm-3 g-2">
                            @foreach ($tourPackage->images as $image)
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
                <div class="card-body text-center">
                    <div class="small text-secondary fw-semibold">Price per traveler</div>
                    <div class="fs-2 fw-bold text-success font-monospace mt-1">৳{{ number_format($tourPackage->price, 2) }}</div>
                    <div class="small text-secondary mt-1">Up to {{ $tourPackage->max_travelers }} {{ Str::plural('traveler', $tourPackage->max_travelers) }}</div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Trip Details</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Duration</span>
                            <span class="text-body fw-semibold">{{ $tourPackage->duration_days }} Days / {{ $tourPackage->duration_nights }} Nights</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Division</span>
                            <span class="text-body fw-semibold">{{ $tourPackage->division }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">District</span>
                            <span class="text-body fw-semibold">{{ $tourPackage->district }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Meeting Information</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-2">
                            <span class="text-secondary d-block mb-1">Meeting Point</span>
                            <span class="text-body">{{ $tourPackage->meeting_point }}</span>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <span class="text-secondary d-block mb-1">Start Location</span>
                            <span class="text-body">{{ $tourPackage->start_location }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

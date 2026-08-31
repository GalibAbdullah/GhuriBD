@extends('layouts.app')

@section('title', 'Tour Packages')
@section('page-title', 'Tour Packages')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">Tour Packages</h3>
        <p class="mb-0 small text-secondary">Discover tour packages across Bangladesh.</p>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('traveler.packages.index') }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by package title">
        </div>
    </form>

    @if ($tourPackages->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                    <h3>{{ $search ? 'No tour packages match your search.' : 'No tour packages available yet.' }}</h3>
                    <p>Check back soon as more tour packages join GhuriBD.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach ($tourPackages as $tourPackage)
                <div class="col">
                    <a href="{{ route('traveler.packages.show', $tourPackage) }}" class="card h-100 text-decoration-none text-reset">
                        <img src="{{ $tourPackage->cover_image_url }}" alt="{{ $tourPackage->title }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                        <div class="card-body">
                            <h4 class="h6 fw-semibold mb-1">{{ $tourPackage->title }}</h4>
                            <div class="small text-secondary mb-2">{{ $tourPackage->destination }} &middot; {{ $tourPackage->duration_days }}D / {{ $tourPackage->duration_nights }}N</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small fw-semibold font-monospace">৳{{ number_format($tourPackage->price, 2) }}</span>
                                <span class="badge text-bg-light border">Up to {{ $tourPackage->max_travelers }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if ($tourPackages->hasPages())
            <div class="mt-4">
                {{ $tourPackages->links() }}
            </div>
        @endif
    @endif
@endsection

@extends('layouts.app')

@section('title', 'My Wishlist')
@section('page-title', 'My Wishlist')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">My Wishlist</h3>
        <p class="mb-0 small text-secondary">Resorts and tour packages you've saved for later.</p>
    </div>

    <div class="btn-group mb-4" role="group">
        <a href="{{ route('traveler.wishlist.index') }}" class="btn btn-sm {{ $type === null ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        <a href="{{ route('traveler.wishlist.index', ['type' => 'resorts']) }}" class="btn btn-sm {{ $type === 'resorts' ? 'btn-primary' : 'btn-outline-primary' }}">Resorts</a>
        <a href="{{ route('traveler.wishlist.index', ['type' => 'packages']) }}" class="btn btn-sm {{ $type === 'packages' ? 'btn-primary' : 'btn-outline-primary' }}">Tour Packages</a>
    </div>

    @if ($resortWishlist->isEmpty() && $packageWishlist->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 20s-7-4.4-9.4-8.8C1 8 2.4 5 5.6 5c1.8 0 3.2 1 4.4 2.6C11.2 6 12.6 5 14.4 5c3.2 0 4.6 3 3 6.2C19 15.6 12 20 12 20z"/></svg>
                    <h3>You haven't added anything to your wishlist yet.</h3>
                    <p>Tap the heart icon on any resort or tour package to save it here.</p>
                </div>
            </div>
        </div>
    @else
        @if ($resortWishlist->isNotEmpty())
            <h4 class="h6 fw-semibold mb-3">Resorts</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4 mb-4">
                @foreach ($resortWishlist as $item)
                    @php $resort = $item->resort @endphp
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="{{ $resort->cover_image_url }}" alt="{{ $resort->name }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h4 class="h6 fw-semibold mb-1">{{ $resort->name }}</h4>
                                <div class="small text-secondary mb-2">{{ $resort->district }}, {{ $resort->division }}</div>
                                <div class="small fw-semibold mb-3">{{ $resort->price_range }}</div>
                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('traveler.resorts.show', $resort) }}" class="btn btn-primary btn-sm flex-fill">View Details</a>
                                    <form method="POST" action="{{ route('wishlist.resorts.toggle', $resort) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($packageWishlist->isNotEmpty())
            <h4 class="h6 fw-semibold mb-3">Tour Packages</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                @foreach ($packageWishlist as $item)
                    @php $package = $item->tourPackage @endphp
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="{{ $package->cover_image_url }}" alt="{{ $package->title }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h4 class="h6 fw-semibold mb-1">{{ $package->title }}</h4>
                                <div class="small text-secondary mb-2">{{ $package->destination }}</div>
                                <div class="small fw-semibold font-monospace mb-3">৳{{ number_format($package->price, 2) }}</div>
                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('traveler.packages.show', $package) }}" class="btn btn-primary btn-sm flex-fill">View Details</a>
                                    <form method="POST" action="{{ route('wishlist.packages.toggle', $package) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
@endsection

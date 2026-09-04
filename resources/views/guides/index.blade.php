@extends('layouts.app')

@section('title', 'Guides')
@section('page-title', 'Guides')

@section('sidebar')
    @if (auth()->user()->hasRole(\App\Enums\UserRole::TRAVEL_PARTNER->value))
        @include('partials.partner-sidebar')
    @else
        @include('traveler.partials.sidebar')
    @endif
@endsection

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">Guides</h3>
        <p class="mb-0 small text-secondary">Verified Tour Guides you can message and book directly.</p>
    </div>

    <form method="GET" action="{{ route('guides.index') }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name or location">
        </div>
    </form>

    @if ($guides->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
                    <h3>{{ $search ? 'No guides match your search.' : 'No verified guides yet.' }}</h3>
                    <p>Check back soon as more Tour Guides get verified on GhuriBD.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach ($guides as $guide)
                @php $verification = $guide->providerVerifications->first(); @endphp
                <div class="col">
                    <a href="{{ route('guides.show', $guide) }}" class="card h-100 text-decoration-none text-reset">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $guide->profile_photo_url }}" alt="{{ $guide->name }}" class="rounded-circle" width="48" height="48" style="object-fit: cover;">
                                <div>
                                    <h4 class="h6 fw-semibold mb-0">{{ $verification?->provider_name ?? $guide->name }}</h4>
                                    <div class="small text-secondary">{{ $guide->name }}</div>
                                </div>
                            </div>
                            <div class="small text-secondary mb-2">{{ $verification?->business_address }}</div>
                            <span class="badge {{ $guide->upcoming_slots_count > 0 ? 'text-bg-success' : 'text-bg-light border' }}">
                                {{ $guide->upcoming_slots_count }} upcoming {{ Str::plural('slot', $guide->upcoming_slots_count) }}
                            </span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if ($guides->hasPages())
            <div class="mt-4">
                {{ $guides->links() }}
            </div>
        @endif
    @endif
@endsection

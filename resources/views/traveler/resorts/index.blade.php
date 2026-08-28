@extends('layouts.app')

@section('title', 'Resorts')
@section('page-title', 'Resorts')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">Resorts</h3>
        <p class="mb-0 small text-secondary">Discover resorts across Bangladesh.</p>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('traveler.resorts.index') }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, district, or division">
        </div>
    </form>

    @if ($resorts->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                    <h3>{{ $search ? 'No resorts match your search.' : 'No resorts available yet.' }}</h3>
                    <p>Check back soon as more resorts join GhuriBD.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach ($resorts as $resort)
                <div class="col">
                    <a href="{{ route('traveler.resorts.show', $resort) }}" class="card h-100 text-decoration-none text-reset">
                        <img src="{{ $resort->cover_image_url }}" alt="{{ $resort->name }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                        <div class="card-body">
                            <h4 class="h6 fw-semibold mb-1">{{ $resort->name }}</h4>
                            <div class="small text-secondary mb-2">{{ $resort->district }}, {{ $resort->division }}</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small fw-semibold">{{ $resort->price_range }}</span>
                                <span class="badge text-bg-light border">{{ $resort->rooms_count }} {{ Str::plural('Room', $resort->rooms_count) }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if ($resorts->hasPages())
            <div class="mt-4">
                {{ $resorts->links() }}
            </div>
        @endif
    @endif
@endsection

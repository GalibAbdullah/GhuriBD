@extends('layouts.app')

@section('title', ($verification->provider_name ?? $guide->name).' — Guide')
@section('page-title', 'Guide Profile')

@section('sidebar')
    @if (auth()->user()->hasRole(\App\Enums\UserRole::TRAVEL_PARTNER->value))
        @include('partials.partner-sidebar')
    @else
        @include('traveler.partials.sidebar')
    @endif
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('guides.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Guides</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $guide->profile_photo_url }}" alt="{{ $guide->name }}" class="rounded-circle" width="64" height="64" style="object-fit: cover;">
                        <div>
                            <h3 class="h5 mb-1">{{ $verification->provider_name }}</h3>
                            <div class="small text-secondary">{{ $guide->name }}</div>
                        </div>
                        <span class="badge text-bg-success ms-auto">Verified Tour Guide</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="small text-secondary">Location</div>
                            <div class="fw-semibold">{{ $verification->business_address }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-secondary">Phone</div>
                            <div class="fw-semibold">{{ $verification->phone }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-3">Upcoming Availability</h4>

                    @if ($availabilities->isEmpty())
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                            <h3>No open slots right now</h3>
                            <p>Message this guide to ask about custom availability.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Seats left</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($availabilities as $slot)
                                        <tr>
                                            <td class="fw-semibold">{{ $slot->available_date->format('D, M j, Y') }}</td>
                                            <td>{{ $slot->time_range }}</td>
                                            <td>{{ $slot->remainingCapacity() }} / {{ $slot->capacity }}</td>
                                            <td class="font-monospace">৳{{ number_format((float) $slot->price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if (auth()->user()->hasRole(\App\Enums\UserRole::TRAVELER->value))
                <div class="card">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Message {{ $guide->name }}</h4>
                        <form method="POST" action="{{ route('messages.store') }}">
                            @csrf
                            <input type="hidden" name="recipient_id" value="{{ $guide->id }}">
                            <textarea name="body" class="form-control mb-3" rows="4" placeholder="Ask about a slot, a custom tour, or anything else..." required maxlength="5000"></textarea>
                            <button type="submit" class="btn btn-primary w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

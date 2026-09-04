@extends('layouts.app')

@section('title', $plan->destination.' — Tour Plan')
@section('page-title', 'Your Itinerary')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('tour-plans.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Tour Plans</a>
    </div>

    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">{{ $plan->destination }}</h3>
            <div class="small text-secondary">
                {{ $plan->duration_days }} days &middot; ৳{{ number_format((float) $plan->budget, 2) }} budget
                @if ($plan->start_date)
                    &middot; starting {{ $plan->start_date->format('M j, Y') }}
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('tour-plans.regenerate', $plan) }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Regenerate</button>
            </form>
            <form method="POST" action="{{ route('tour-plans.destroy', $plan) }}" onsubmit="return confirm('Delete this tour plan?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>

    <div class="mb-4 d-flex flex-wrap gap-2">
        @foreach ($plan->interestLabels() as $interest)
            <span class="badge text-bg-light border">{{ $interest }}</span>
        @endforeach
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="d-flex flex-column gap-3">
        @foreach ($plan->days as $day)
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="h6 fw-semibold mb-0">{{ $day->title }}</h4>
                        <span class="small font-monospace text-secondary">Budget ৳{{ number_format((float) $day->budget_allocated, 2) }}</span>
                    </div>
                    <p class="small text-body mb-0">{{ $day->description }}</p>

                    @if ($day->suggestionIsStillBookable())
                        <form method="POST" action="{{ route('messages.store') }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="recipient_id" value="{{ $day->suggestedAvailability->guide->id }}">
                            <input type="hidden" name="body" value="Hi! I'm planning a trip to {{ $plan->destination }} and your slot on {{ $day->suggestedAvailability->available_date->format('M j, Y') }} looks like a great fit — is it still open?">
                            <button type="submit" class="btn btn-primary btn-sm">Message {{ $day->suggestedAvailability->guide->name }}</button>
                        </form>
                    @elseif ($day->hasSuggestion())
                        <p class="mt-2 small text-body-tertiary mb-0">This suggested slot is no longer available — try regenerating your plan.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection

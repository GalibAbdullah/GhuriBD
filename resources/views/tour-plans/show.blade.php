@extends('layouts.app')

@section('title', $plan->destination . ' — Tour Plan')
@section('page-title', 'Your Itinerary')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
    <a href="{{ route('tour-plans.index') }}" class="nav-item active">AI Planner</a>
    <a href="{{ route('bookings.index') }}" class="nav-item">My Bookings</a>
@endsection

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-[16px] font-semibold">{{ $plan->destination }}</h3>
            <div class="text-[12.5px] text-ink-faint">
                {{ $plan->duration_days }} days · ৳{{ number_format((float) $plan->budget, 2) }} budget
                @if ($plan->start_date)
                    · starting {{ $plan->start_date->format('M j, Y') }}
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('tour-plans.regenerate', $plan) }}">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Regenerate</button>
            </form>
            <form method="POST" action="{{ route('tour-plans.destroy', $plan) }}" onsubmit="return confirm('Delete this tour plan?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline btn-sm">Delete</button>
            </form>
        </div>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ($plan->interestLabels() as $interest)
            <span class="chip">{{ $interest }}</span>
        @endforeach
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-line bg-primary-tint px-4 py-3 text-[13px] font-medium text-primary-dark">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-4">
        @foreach ($plan->days as $day)
            <div class="card card-pad">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-[16px] font-semibold">{{ $day->title }}</h3>
                    <span class="font-mono text-[12.5px] text-ink-faint">Budget ৳{{ number_format((float) $day->budget_allocated, 2) }}</span>
                </div>
                <p class="text-[13px] text-ink-muted">{{ $day->description }}</p>

                @if ($day->suggestionIsStillBookable())
                    <div class="mt-3">
                        <a href="{{ route('bookings.create', $day->suggestedAvailability) }}" class="btn btn-primary btn-sm">
                            Book {{ $day->suggestedAvailability->guide->name }}
                        </a>
                    </div>
                @elseif ($day->hasSuggestion())
                    <p class="mt-2 text-[11.5px] text-ink-faint">This suggested slot is no longer available — try regenerating your plan.</p>
                @endif
            </div>
        @endforeach
    </div>
@endsection

@extends('layouts.app')

@section('title', 'AI Tour Planner')
@section('page-title', 'Plan a Trip')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
    <a href="{{ route('tour-plans.index') }}" class="nav-item active">AI Planner</a>
    <a href="{{ route('bookings.index') }}" class="nav-item">My Bookings</a>
@endsection

@section('content')
    @php $rules = config('ghuribd.tour_planner'); @endphp

    <div class="mx-auto" style="max-width: 640px">
        <div class="card card-pad">
            <p class="mb-5 text-[12.5px] text-ink-muted">
                Tell us your destination, budget, and interests — we'll build a
                day-by-day plan and match real, bookable guides where they fit
                your budget.
            </p>

            <form method="POST" action="{{ route('tour-plans.store') }}">
                @csrf

                <div class="input-group">
                    <label for="destination" class="input-label">Destination</label>
                    <input id="destination" type="text" name="destination" value="{{ old('destination') }}"
                           placeholder="e.g. Cox's Bazar, Sylhet, Bandarban"
                           class="input @error('destination') !border-error @enderror" required maxlength="120">
                    @error('destination')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="input-group">
                        <label for="days" class="input-label">Trip length (days)</label>
                        <input id="days" type="number" name="days" value="{{ old('days', 3) }}"
                               min="{{ $rules['min_days'] }}" max="{{ $rules['max_days'] }}"
                               class="input @error('days') !border-error @enderror" required>
                        @error('days')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="input-group">
                        <label for="budget" class="input-label">Total budget (৳)</label>
                        <input id="budget" type="number" name="budget" value="{{ old('budget') }}"
                               min="{{ $rules['min_budget'] }}" max="{{ $rules['max_budget'] }}" step="0.01"
                               class="input @error('budget') !border-error @enderror" required>
                        @error('budget')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="input-group">
                    <label for="start_date" class="input-label">Start date (optional)</label>
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}"
                           class="input @error('start_date') !border-error @enderror">
                    <p class="input-hint">Leave blank if you're just exploring ideas — we'll still suggest the soonest matching guides.</p>
                    @error('start_date')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <span class="input-label">Interests</span>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($interests as $interest)
                            <label class="chip flex cursor-pointer items-center gap-2">
                                <input type="checkbox" name="interests[]" value="{{ $interest->value }}"
                                       @checked(in_array($interest->value, old('interests', [])))>
                                {{ $interest->value }}
                            </label>
                        @endforeach
                    </div>
                    @error('interests')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-4">Generate my itinerary</button>
            </form>
        </div>
    </div>
@endsection

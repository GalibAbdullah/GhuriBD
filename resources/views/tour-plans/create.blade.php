@extends('layouts.app')

@section('title', 'AI Tour Planner')
@section('page-title', 'Plan a Trip')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    @php $rules = config('ghuribd.tour_planner'); @endphp

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <p class="small text-secondary mb-4">
                Tell us your destination, budget, and interests — we'll build a day-by-day
                plan and match real, bookable guides where they fit your budget.
            </p>

            <form method="POST" action="{{ route('tour-plans.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="destination" class="form-label small fw-semibold">Destination</label>
                    <input id="destination" type="text" name="destination" value="{{ old('destination') }}"
                           placeholder="e.g. Cox's Bazar, Sylhet, Bandarban"
                           class="form-control @error('destination') is-invalid @enderror" required maxlength="120">
                    @error('destination')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="days" class="form-label small fw-semibold">Trip length (days)</label>
                        <input id="days" type="number" name="days" value="{{ old('days', 3) }}"
                               min="{{ $rules['min_days'] }}" max="{{ $rules['max_days'] }}"
                               class="form-control @error('days') is-invalid @enderror" required>
                        @error('days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="budget" class="form-label small fw-semibold">Total budget (৳)</label>
                        <input id="budget" type="number" name="budget" value="{{ old('budget') }}"
                               min="{{ $rules['min_budget'] }}" max="{{ $rules['max_budget'] }}" step="0.01"
                               class="form-control @error('budget') is-invalid @enderror" required>
                        @error('budget')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label small fw-semibold">Start date <span class="text-secondary fw-normal">(optional)</span></label>
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}"
                           class="form-control @error('start_date') is-invalid @enderror">
                    <div class="form-text">Leave blank if you're just exploring ideas — we'll still suggest the soonest matching guides.</div>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <span class="form-label small fw-semibold d-block">Interests</span>
                    <div class="row row-cols-2 row-cols-sm-3 g-2">
                        @foreach ($interests as $interest)
                            <div class="col">
                                <label class="d-flex align-items-center gap-2 border rounded px-2 py-2 small" style="cursor: pointer;">
                                    <input type="checkbox" class="form-check-input mt-0" name="interests[]" value="{{ $interest->value }}"
                                           @checked(in_array($interest->value, old('interests', [])))>
                                    {{ $interest->value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('interests')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Generate my itinerary</button>
            </form>
        </div>
    </div>
@endsection

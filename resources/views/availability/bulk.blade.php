@extends('layouts.app')

@section('title', 'Bulk Publish Availability')
@section('page-title', 'Bulk Publish Availability')

@section('sidebar')
    @include('availability.partials.sidebar')
@endsection

@section('content')
    @php
        $rules = config('ghuribd.availability');
        $today = \App\Models\GuideAvailability::today();
        $horizon = $today->copy()->addDays($rules['max_advance_days'])->toDateString();
        $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $selectedDays = array_map('intval', (array) old('weekdays', [0, 1, 2, 3, 4]));
    @endphp

    <div class="mx-auto" style="max-width: 620px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">Publish a repeating schedule</h3>
                    <a href="{{ route('partner.availability.index') }}" class="small fw-semibold link-primary link-underline-opacity-0">Back to calendar</a>
                </div>

                <p class="mb-4 small text-secondary">
                    Creates one slot per matching date. Dates that already have an overlapping
                    slot are skipped, so you can safely top up a partly filled month.
                </p>

                <form method="POST" action="{{ route('partner.availability.bulk.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="start_date" class="form-label">From date</label>
                            <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}"
                                   min="{{ $today->toDateString() }}" max="{{ $horizon }}"
                                   class="form-control @error('start_date') is-invalid @enderror" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="end_date" class="form-label">To date</label>
                            <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}"
                                   min="{{ $today->toDateString() }}" max="{{ $horizon }}"
                                   class="form-control @error('end_date') is-invalid @enderror" required>
                            <div class="form-text">Up to {{ $rules['max_bulk_range_days'] }} days per publish.</div>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <span class="form-label">Days of the week</span>
                        <div class="d-flex flex-wrap gap-3 pt-1">
                            @foreach ($weekdays as $index => $day)
                                <div class="form-check">
                                    <input type="checkbox" name="weekdays[]" value="{{ $index }}" id="weekday_{{ $index }}" class="form-check-input"
                                           @checked(in_array($index, $selectedDays, true))>
                                    <label for="weekday_{{ $index }}" class="form-check-label small">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('weekdays')<div class="text-danger small fw-medium mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="start_time" class="form-label">Start time</label>
                            <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}"
                                   class="form-control @error('start_time') is-invalid @enderror" required>
                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="end_time" class="form-label">End time</label>
                            <input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}"
                                   class="form-control @error('end_time') is-invalid @enderror" required>
                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-sm-6">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input id="capacity" type="number" name="capacity" value="{{ old('capacity', 1) }}"
                                   min="{{ $rules['min_capacity'] }}" max="{{ $rules['max_capacity'] }}" step="1"
                                   class="form-control @error('capacity') is-invalid @enderror" required>
                            @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="price" class="form-label">Price (BDT)</label>
                            <input id="price" type="number" name="price" value="{{ old('price') }}"
                                   min="0" max="{{ $rules['max_price'] }}" step="0.01"
                                   class="form-control @error('price') is-invalid @enderror" placeholder="e.g. 3500" required>
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach ($statuses as $case)
                                <option value="{{ $case->value }}" @selected(old('status', \App\Enums\AvailabilityStatus::AVAILABLE->value) === $case->value)>
                                    {{ $case->value }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes <span class="fw-normal text-body-tertiary">(optional)</span></label>
                        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Publish schedule</button>
                </form>
            </div>
        </div>
    </div>
@endsection

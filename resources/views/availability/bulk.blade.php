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

    <div class="mx-auto max-w-[620px]">
        <div class="card card-pad">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-[16px] font-semibold">Publish a repeating schedule</h3>
                <a href="{{ route('partner.availability.index') }}" class="text-[12.5px] font-semibold text-primary">Back to calendar</a>
            </div>

            <p class="mb-5 text-[13px] text-ink-muted">
                Creates one slot per matching date. Dates that already have an overlapping
                slot are skipped, so you can safely top up a partly filled month.
            </p>

            <form method="POST" action="{{ route('partner.availability.bulk.store') }}">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="input-group">
                        <label for="start_date" class="input-label">From date</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}"
                               min="{{ $today->toDateString() }}" max="{{ $horizon }}"
                               class="input @error('start_date') !border-error @enderror" required>
                        @error('start_date')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="input-group">
                        <label for="end_date" class="input-label">To date</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}"
                               min="{{ $today->toDateString() }}" max="{{ $horizon }}"
                               class="input @error('end_date') !border-error @enderror" required>
                        <p class="input-hint">Up to {{ $rules['max_bulk_range_days'] }} days per publish.</p>
                        @error('end_date')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="input-group">
                    <span class="input-label">Days of the week</span>
                    <div class="flex flex-wrap gap-3 pt-1">
                        @foreach ($weekdays as $index => $day)
                            <label class="flex items-center gap-1.5 text-[13px]">
                                <input type="checkbox" name="weekdays[]" value="{{ $index }}"
                                       @checked(in_array($index, $selectedDays, true))>
                                {{ $day }}
                            </label>
                        @endforeach
                    </div>
                    @error('weekdays')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="input-group">
                        <label for="start_time" class="input-label">Start time</label>
                        <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}"
                               class="input @error('start_time') !border-error @enderror" required>
                        @error('start_time')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="input-group">
                        <label for="end_time" class="input-label">End time</label>
                        <input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}"
                               class="input @error('end_time') !border-error @enderror" required>
                        @error('end_time')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="input-group">
                        <label for="capacity" class="input-label">Capacity</label>
                        <input id="capacity" type="number" name="capacity" value="{{ old('capacity', 1) }}"
                               min="{{ $rules['min_capacity'] }}" max="{{ $rules['max_capacity'] }}" step="1"
                               class="input @error('capacity') !border-error @enderror" required>
                        @error('capacity')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="input-group">
                        <label for="price" class="input-label">Price (BDT)</label>
                        <input id="price" type="number" name="price" value="{{ old('price') }}"
                               min="0" max="{{ $rules['max_price'] }}" step="0.01"
                               class="input @error('price') !border-error @enderror" placeholder="e.g. 3500" required>
                        @error('price')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="input-group">
                    <label for="status" class="input-label">Status</label>
                    <select id="status" name="status" class="input @error('status') !border-error @enderror" required>
                        @foreach ($statuses as $case)
                            <option value="{{ $case->value }}" @selected(old('status', \App\Enums\AvailabilityStatus::AVAILABLE->value) === $case->value)>
                                {{ $case->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="notes" class="input-label">Notes <span class="font-normal text-ink-faint">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="3" class="input @error('notes') !border-error @enderror">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Publish schedule</button>
            </form>
        </div>
    </div>
@endsection

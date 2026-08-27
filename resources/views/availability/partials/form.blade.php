@php
    $rules = config('ghuribd.availability');
    $today = \App\Models\GuideAvailability::today();
@endphp

<div class="mb-3">
    <label for="available_date" class="form-label">Date</label>
    <input id="available_date" type="date" name="available_date"
           value="{{ old('available_date', $slot?->available_date?->toDateString()) }}"
           min="{{ $today->toDateString() }}"
           max="{{ $today->copy()->addDays($rules['max_advance_days'])->toDateString() }}"
           class="form-control @error('available_date') is-invalid @enderror" required>
    @error('available_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3">
    <div class="col-sm-6">
        <label for="start_time" class="form-label">Start time</label>
        <input id="start_time" type="time" name="start_time"
               value="{{ old('start_time', $slot ? substr($slot->start_time, 0, 5) : '') }}"
               class="form-control @error('start_time') is-invalid @enderror" required>
        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label for="end_time" class="form-label">End time</label>
        <input id="end_time" type="time" name="end_time"
               value="{{ old('end_time', $slot ? substr($slot->end_time, 0, 5) : '') }}"
               class="form-control @error('end_time') is-invalid @enderror" required>
        <div class="form-text">Minimum {{ $rules['min_duration_minutes'] }} minutes.</div>
        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-sm-6">
        <label for="capacity" class="form-label">Capacity</label>
        <input id="capacity" type="number" name="capacity"
               value="{{ old('capacity', $slot->capacity ?? 1) }}"
               min="{{ $rules['min_capacity'] }}" max="{{ $rules['max_capacity'] }}" step="1"
               class="form-control @error('capacity') is-invalid @enderror" required>
        <div class="form-text">Travelers who can book this slot.</div>
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label for="price" class="form-label">Price (BDT)</label>
        <input id="price" type="number" name="price"
               value="{{ old('price', $slot ? number_format((float) $slot->price, 2, '.', '') : '') }}"
               min="0" max="{{ $rules['max_price'] }}" step="0.01"
               class="form-control @error('price') is-invalid @enderror" placeholder="e.g. 3500" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3 mt-3">
    <label for="status" class="form-label">Status</label>
    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
        @foreach ($statuses as $case)
            <option value="{{ $case->value }}" @selected(old('status', $slot?->status?->value ?? \App\Enums\AvailabilityStatus::AVAILABLE->value) === $case->value)>
                {{ $case->value }}
            </option>
        @endforeach
    </select>
    <div class="form-text">Blocked slots stay on your calendar but cannot be booked.</div>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="notes" class="form-label">Notes <span class="fw-normal text-body-tertiary">(optional)</span></label>
    <textarea id="notes" name="notes" rows="3"
              class="form-control @error('notes') is-invalid @enderror"
              placeholder="e.g. Sundarbans day trip, boat included">{{ old('notes', $slot->notes ?? '') }}</textarea>
    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

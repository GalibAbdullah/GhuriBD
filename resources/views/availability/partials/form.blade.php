@php
    $rules = config('ghuribd.availability');
    $today = \App\Models\GuideAvailability::today();
@endphp

<div class="input-group">
    <label for="available_date" class="input-label">Date</label>
    <input id="available_date" type="date" name="available_date"
           value="{{ old('available_date', $slot?->available_date?->toDateString()) }}"
           min="{{ $today->toDateString() }}"
           max="{{ $today->copy()->addDays($rules['max_advance_days'])->toDateString() }}"
           class="input @error('available_date') !border-error @enderror" required>
    @error('available_date')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="input-group">
        <label for="start_time" class="input-label">Start time</label>
        <input id="start_time" type="time" name="start_time"
               value="{{ old('start_time', $slot ? substr($slot->start_time, 0, 5) : '') }}"
               class="input @error('start_time') !border-error @enderror" required>
        @error('start_time')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
    </div>

    <div class="input-group">
        <label for="end_time" class="input-label">End time</label>
        <input id="end_time" type="time" name="end_time"
               value="{{ old('end_time', $slot ? substr($slot->end_time, 0, 5) : '') }}"
               class="input @error('end_time') !border-error @enderror" required>
        <p class="input-hint">Minimum {{ $rules['min_duration_minutes'] }} minutes.</p>
        @error('end_time')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="input-group">
        <label for="capacity" class="input-label">Capacity</label>
        <input id="capacity" type="number" name="capacity"
               value="{{ old('capacity', $slot->capacity ?? 1) }}"
               min="{{ $rules['min_capacity'] }}" max="{{ $rules['max_capacity'] }}" step="1"
               class="input @error('capacity') !border-error @enderror" required>
        <p class="input-hint">Travelers who can book this slot.</p>
        @error('capacity')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
    </div>

    <div class="input-group">
        <label for="price" class="input-label">Price (BDT)</label>
        <input id="price" type="number" name="price"
               value="{{ old('price', $slot ? number_format((float) $slot->price, 2, '.', '') : '') }}"
               min="0" max="{{ $rules['max_price'] }}" step="0.01"
               class="input @error('price') !border-error @enderror" placeholder="e.g. 3500" required>
        @error('price')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
    </div>
</div>

<div class="input-group">
    <label for="status" class="input-label">Status</label>
    <select id="status" name="status" class="input @error('status') !border-error @enderror" required>
        @foreach ($statuses as $case)
            <option value="{{ $case->value }}" @selected(old('status', $slot?->status?->value ?? \App\Enums\AvailabilityStatus::AVAILABLE->value) === $case->value)>
                {{ $case->value }}
            </option>
        @endforeach
    </select>
    <p class="input-hint">Blocked slots stay on your calendar but cannot be booked.</p>
    @error('status')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
</div>

<div class="input-group">
    <label for="notes" class="input-label">Notes <span class="font-normal text-ink-faint">(optional)</span></label>
    <textarea id="notes" name="notes" rows="3"
              class="input @error('notes') !border-error @enderror"
              placeholder="e.g. Sundarbans day trip, boat included">{{ old('notes', $slot->notes ?? '') }}</textarea>
    @error('notes')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
</div>

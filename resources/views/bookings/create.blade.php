@extends('layouts.app')

@section('title', 'Book a Guide')
@section('page-title', 'Confirm Booking')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
    <a href="{{ route('bookings.index') }}" class="nav-item active">My Bookings</a>
@endsection

@section('content')
    @php
        $rules = config('ghuribd.booking');
        $max = min($rules['max_party_size'], $availability->remainingCapacity());
    @endphp

    <div class="mx-auto max-w-[560px]">
        <div class="card card-pad">
            <h3 class="mb-1 text-[16px] font-semibold">{{ $availability->guide->name }}</h3>
            <div class="mb-5 text-[13px] text-ink-muted">
                {{ $availability->available_date->format('D, M j, Y') }} · {{ $availability->time_range }}
            </div>

            <form method="POST" action="{{ route('bookings.store') }}">
                @csrf
                <input type="hidden" name="availability_id" value="{{ $availability->id }}">

                <div class="input-group">
                    <label for="party_size" class="input-label">Number of travelers</label>
                    <input id="party_size" type="number" name="party_size"
                           value="{{ old('party_size', 1) }}"
                           min="{{ $rules['min_party_size'] }}" max="{{ $max }}" step="1"
                           class="input @error('party_size') !border-error @enderror" required>
                    <p class="input-hint">{{ $availability->remainingCapacity() }} spot(s) remaining in this slot.</p>
                    @error('party_size')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                    @error('availability_id')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="kv-row">
                    <span class="kv-label">Price per traveler</span>
                    <span class="kv-value font-mono">৳{{ number_format((float) $availability->price, 2) }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-label">Total</span>
                    <span id="total-price" class="kv-value font-mono">৳{{ number_format((float) $availability->price, 2) }}</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-4">Continue to payment</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const unitPrice = {{ (float) $availability->price }};
            const partyInput = document.getElementById('party_size');
            const totalEl = document.getElementById('total-price');

            partyInput.addEventListener('input', function () {
                const size = parseInt(partyInput.value, 10) || 0;
                totalEl.textContent = '৳' + (unitPrice * size).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            });
        })();
    </script>
@endsection

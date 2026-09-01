@extends('layouts.app')

@section('title', 'Book '.$room->room_name)
@section('page-title', 'Resort Booking')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.resorts.rooms.show', [$resort, $room]) }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Room</a>
    </div>

    <h3 class="h5 mb-4">Book Your Stay</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach (collect($errors->all())->unique() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bookings.store') }}" id="bookingForm">
        @csrf
        <input type="hidden" name="booking_type" value="resort">
        <input type="hidden" name="resort_id" value="{{ $resort->id }}">
        <input type="hidden" name="room_id" value="{{ $room->id }}">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body d-flex gap-3">
                        <img src="{{ $room->cover_image_url }}" alt="{{ $room->room_name }}" class="rounded" style="width: 96px; height: 96px; object-fit: cover;">
                        <div>
                            <div class="small text-secondary">{{ $resort->name }}</div>
                            <h4 class="h6 fw-semibold mb-1">{{ $room->room_name }}</h4>
                            <div class="small text-secondary">{{ $room->room_type }} &middot; Fits {{ $room->capacity }} guests</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Trip Details</h4>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="check_in_date" class="form-label">Check-in</label>
                                <input type="date" id="check_in_date" name="check_in_date" value="{{ old('check_in_date') }}" min="{{ now()->toDateString() }}" class="form-control @error('check_in_date') is-invalid @enderror" required>
                                @error('check_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="check_out_date" class="form-label">Check-out</label>
                                <input type="date" id="check_out_date" name="check_out_date" value="{{ old('check_out_date') }}" min="{{ now()->addDay()->toDateString() }}" class="form-control @error('check_out_date') is-invalid @enderror" required>
                                @error('check_out_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="guests" class="form-label">Guests</label>
                            <input type="number" id="guests" name="guests" min="1" max="{{ $room->capacity }}" value="{{ old('guests', 1) }}" class="form-control @error('guests') is-invalid @enderror" required style="max-width: 160px;">
                            <div class="form-text">This room fits up to {{ $room->capacity }} guests.</div>
                            @error('guests')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mt-3">
                            <label for="special_request" class="form-label">Special Request (optional)</label>
                            <textarea id="special_request" name="special_request" rows="3" class="form-control @error('special_request') is-invalid @enderror" placeholder="Any preferences the resort should know about?">{{ old('special_request') }}</textarea>
                            @error('special_request')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card" style="position: sticky; top: 84px;">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Price Summary</h4>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-secondary">৳{{ number_format($room->price_per_night, 2) }} &times; <span id="nightsLabel">0</span> night(s)</span>
                            <span id="subtotalLabel" class="fw-semibold font-monospace">৳0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span id="totalLabel" class="font-monospace text-success">৳0.00</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-4" id="submitBtn">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pricePerNight = {{ (float) $room->price_per_night }};
            const checkIn = document.getElementById('check_in_date');
            const checkOut = document.getElementById('check_out_date');
            const nightsLabel = document.getElementById('nightsLabel');
            const subtotalLabel = document.getElementById('subtotalLabel');
            const totalLabel = document.getElementById('totalLabel');

            const format = (n) => '৳' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const recalc = () => {
                const inDate = new Date(checkIn.value);
                const outDate = new Date(checkOut.value);
                let nights = 0;

                if (checkIn.value && checkOut.value && outDate > inDate) {
                    nights = Math.round((outDate - inDate) / (1000 * 60 * 60 * 24));
                }

                const total = nights * pricePerNight;
                nightsLabel.textContent = nights;
                subtotalLabel.textContent = format(total);
                totalLabel.textContent = format(total);
            };

            checkIn.addEventListener('change', () => {
                checkOut.min = checkIn.value;
                recalc();
            });
            checkOut.addEventListener('change', recalc);

            const form = document.getElementById('bookingForm');
            const submitBtn = document.getElementById('submitBtn');
            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking…';
            });
        });
    </script>
@endsection

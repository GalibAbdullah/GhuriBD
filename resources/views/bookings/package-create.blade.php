@extends('layouts.app')

@section('title', 'Book '.$package->title)
@section('page-title', 'Tour Package Booking')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.packages.show', $package) }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Package</a>
    </div>

    <h3 class="h5 mb-4">Book This Tour</h3>

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
        <input type="hidden" name="booking_type" value="package">
        <input type="hidden" name="tour_package_id" value="{{ $package->id }}">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body d-flex gap-3">
                        <img src="{{ $package->cover_image_url }}" alt="{{ $package->title }}" class="rounded" style="width: 96px; height: 96px; object-fit: cover;">
                        <div>
                            <div class="small text-secondary">{{ $package->destination }}</div>
                            <h4 class="h6 fw-semibold mb-1">{{ $package->title }}</h4>
                            <div class="small text-secondary">{{ $package->duration_days }}D / {{ $package->duration_nights }}N &middot; Up to {{ $package->max_travelers }} travelers</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Trip Details</h4>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="travel_date" class="form-label">Travel Date</label>
                                <input type="date" id="travel_date" name="travel_date" value="{{ old('travel_date') }}" min="{{ now()->toDateString() }}" class="form-control @error('travel_date') is-invalid @enderror" required>
                                @error('travel_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="guests" class="form-label">Number of Travelers</label>
                                <input type="number" id="guests" name="guests" min="1" max="{{ $package->max_travelers }}" value="{{ old('guests', 1) }}" class="form-control @error('guests') is-invalid @enderror" required>
                                @error('guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-text mt-1">This package fits up to {{ $package->max_travelers }} travelers.</div>

                        <div class="mt-3">
                            <label for="special_request" class="form-label">Special Request (optional)</label>
                            <textarea id="special_request" name="special_request" rows="3" class="form-control @error('special_request') is-invalid @enderror" placeholder="Dietary needs, accessibility, etc.">{{ old('special_request') }}</textarea>
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
                            <span class="text-secondary">৳{{ number_format($package->price, 2) }} &times; <span id="guestsLabel">1</span> traveler(s)</span>
                            <span id="subtotalLabel" class="fw-semibold font-monospace">৳{{ number_format($package->price, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span id="totalLabel" class="font-monospace text-success">৳{{ number_format($package->price, 2) }}</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-4" id="submitBtn">Confirm Booking</button>
                    </div>
                </div>

                @include('weather.partials._card', ['forecast' => $forecast])
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pricePerTraveler = {{ (float) $package->price }};
            const guests = document.getElementById('guests');
            const guestsLabel = document.getElementById('guestsLabel');
            const subtotalLabel = document.getElementById('subtotalLabel');
            const totalLabel = document.getElementById('totalLabel');

            const format = (n) => '৳' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const recalc = () => {
                const count = Math.max(parseInt(guests.value || '0', 10), 0);
                const total = count * pricePerTraveler;
                guestsLabel.textContent = count;
                subtotalLabel.textContent = format(total);
                totalLabel.textContent = format(total);
            };

            guests.addEventListener('input', recalc);
            recalc();

            const form = document.getElementById('bookingForm');
            const submitBtn = document.getElementById('submitBtn');
            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking…';
            });
        });
    </script>
@endsection

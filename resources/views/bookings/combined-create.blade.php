@extends('layouts.app')

@section('title', 'Combined Booking')
@section('page-title', 'Combined Booking')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.bookings.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to My Bookings</a>
    </div>

    <h3 class="h5 mb-1">Book a Resort + Tour Package</h3>
    <p class="text-secondary small mb-4">Combine a stay and a tour into a single checkout.</p>

    @if ($resorts->isEmpty() || $packages->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                    <h3>Not enough listings yet</h3>
                    <p>Combined booking needs at least one active resort with rooms and one active tour package.</p>
                </div>
            </div>
        </div>
    @else
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
            <input type="hidden" name="booking_type" value="combined">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="h6 fw-semibold mb-3">1. Choose Your Tour Package</h4>
                            <select id="tour_package_id" name="tour_package_id" class="form-select @error('tour_package_id') is-invalid @enderror" required>
                                <option value="">Select a tour package</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" data-price="{{ $package->price }}" data-max="{{ $package->max_travelers }}" @selected((int) old('tour_package_id', $selectedPackageId) === $package->id)>
                                        {{ $package->title }} — ৳{{ number_format($package->price, 2) }} / traveler
                                    </option>
                                @endforeach
                            </select>
                            @error('tour_package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            <div class="mt-3">
                                <label for="travel_date" class="form-label">Travel Date</label>
                                <input type="date" id="travel_date" name="travel_date" value="{{ old('travel_date') }}" min="{{ now()->toDateString() }}" class="form-control @error('travel_date') is-invalid @enderror" required>
                                @error('travel_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="h6 fw-semibold mb-3">2. Choose Your Resort &amp; Room</h4>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="resort_id" class="form-label">Resort</label>
                                    <select id="resort_id" name="resort_id" class="form-select @error('resort_id') is-invalid @enderror" data-rooms="{{ $resorts->mapWithKeys(fn ($resort) => [$resort->id => $resort->rooms->map(fn ($room) => ['id' => $room->id, 'label' => $room->room_name.' — ৳'.number_format($room->price_per_night, 2).'/night', 'price' => (float) $room->price_per_night, 'capacity' => $room->capacity])])->toJson() }}" required>
                                        <option value="">Select a resort</option>
                                        @foreach ($resorts as $resort)
                                            <option value="{{ $resort->id }}" @selected((int) old('resort_id', $selectedResortId) === $resort->id)>{{ $resort->name }} ({{ $resort->district }})</option>
                                        @endforeach
                                    </select>
                                    @error('resort_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="room_id" class="form-label">Room</label>
                                    <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                                        <option value="">Select a resort first</option>
                                    </select>
                                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
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
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="h6 fw-semibold mb-3">3. Travelers</h4>
                            <label for="guests" class="form-label">Number of Guests / Travelers</label>
                            <input type="number" id="guests" name="guests" min="1" value="{{ old('guests', 1) }}" class="form-control @error('guests') is-invalid @enderror" style="max-width: 160px;" required>
                            <div class="form-text">Used for both the room capacity and the tour package traveler count.</div>
                            @error('guests')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                            <div class="mt-3">
                                <label for="special_request" class="form-label">Special Request (optional)</label>
                                <textarea id="special_request" name="special_request" rows="3" class="form-control" placeholder="Anything the resort or tour operator should know?">{{ old('special_request') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card" style="position: sticky; top: 84px;">
                        <div class="card-body">
                            <h4 class="h6 fw-semibold mb-3">Price Summary</h4>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-secondary">Resort (<span id="nightsLabel">0</span> night(s))</span>
                                <span id="resortSubtotal" class="fw-semibold font-monospace">৳0.00</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-secondary">Tour Package (<span id="guestsSummaryLabel">1</span> traveler(s))</span>
                                <span id="packageSubtotal" class="fw-semibold font-monospace">৳0.00</span>
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
                const resortSelect = document.getElementById('resort_id');
                const roomSelect = document.getElementById('room_id');
                const packageSelect = document.getElementById('tour_package_id');
                const guests = document.getElementById('guests');
                const checkIn = document.getElementById('check_in_date');
                const checkOut = document.getElementById('check_out_date');

                const nightsLabel = document.getElementById('nightsLabel');
                const guestsSummaryLabel = document.getElementById('guestsSummaryLabel');
                const resortSubtotal = document.getElementById('resortSubtotal');
                const packageSubtotal = document.getElementById('packageSubtotal');
                const totalLabel = document.getElementById('totalLabel');

                const format = (n) => '৳' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const roomsByResort = JSON.parse(resortSelect.dataset.rooms);
                const preselectedRoom = @json(old('room_id', $selectedRoomId));

                const populateRooms = (resortId, selected) => {
                    const rooms = roomsByResort[resortId] || [];
                    if (!rooms.length) {
                        roomSelect.innerHTML = '<option value="">No available rooms in this resort</option>';
                        return;
                    }
                    roomSelect.innerHTML = '<option value="">Select a room</option>' +
                        rooms.map((room) => `<option value="${room.id}" data-price="${room.price}" data-capacity="${room.capacity}"${String(room.id) === String(selected) ? ' selected' : ''}>${room.label}</option>`).join('');
                };

                if (resortSelect.value) {
                    populateRooms(resortSelect.value, preselectedRoom);
                }
                resortSelect.addEventListener('change', () => populateRooms(resortSelect.value, ''));

                const recalc = () => {
                    const guestCount = Math.max(parseInt(guests.value || '0', 10), 0);
                    guestsSummaryLabel.textContent = guestCount;

                    let nights = 0;
                    if (checkIn.value && checkOut.value) {
                        const diff = (new Date(checkOut.value) - new Date(checkIn.value)) / (1000 * 60 * 60 * 24);
                        nights = diff > 0 ? Math.round(diff) : 0;
                    }
                    nightsLabel.textContent = nights;

                    const selectedRoomOption = roomSelect.selectedOptions[0];
                    const roomPrice = selectedRoomOption ? parseFloat(selectedRoomOption.dataset.price || '0') : 0;
                    const roomTotal = nights * roomPrice;
                    resortSubtotal.textContent = format(roomTotal);

                    const selectedPackageOption = packageSelect.selectedOptions[0];
                    const packagePrice = selectedPackageOption ? parseFloat(selectedPackageOption.dataset.price || '0') : 0;
                    const packageTotal = guestCount * packagePrice;
                    packageSubtotal.textContent = format(packageTotal);

                    totalLabel.textContent = format(roomTotal + packageTotal);
                };

                [resortSelect, roomSelect, packageSelect, guests, checkIn, checkOut].forEach((el) => {
                    el.addEventListener('change', recalc);
                    el.addEventListener('input', recalc);
                });
                checkIn.addEventListener('change', () => { checkOut.min = checkIn.value; });

                recalc();

                const form = document.getElementById('bookingForm');
                const submitBtn = document.getElementById('submitBtn');
                form.addEventListener('submit', () => {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking…';
                });
            });
        </script>
    @endif
@endsection

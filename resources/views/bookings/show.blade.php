@extends('layouts.app')

@section('title', 'Booking '.$booking->booking_reference)
@section('page-title', 'Booking Details')

@section('sidebar')
    @if ($audience === 'traveler')
        @include('traveler.partials.sidebar')
    @elseif ($audience === 'partner')
        @include('partials.partner-sidebar')
    @else
        @include('partials.admin-sidebar')
    @endif
@endsection

@php
    $backRoute = match ($audience) {
        'traveler' => 'traveler.bookings.index',
        'partner' => 'partner.bookings.index',
        default => 'admin.bookings.index',
    };

    $statusColors = [
        'Pending' => 'warning',
        'Confirmed' => 'success',
        'Cancelled' => 'secondary',
        'Completed' => 'primary',
    ];
    $paymentColors = [
        'Pending' => 'warning',
        'Paid' => 'success',
        'Refunded' => 'secondary',
    ];
@endphp

@section('content')
    <div class="mb-3">
        <a href="{{ route($backRoute) }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Bookings</a>
    </div>

    @if ($audience === 'traveler' && $errors->has('payment'))
        <div class="alert alert-danger">{{ $errors->first('payment') }}</div>
    @endif

    @if ($audience === 'traveler' && $booking->isAwaitingPayment())
        <div class="card border-warning mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="h6 fw-bold mb-1 text-warning-emphasis">Payment Pending</h4>
                    <p class="mb-0 small text-secondary">Your reservation is held but not yet confirmed. Complete payment of <strong class="font-monospace">৳{{ number_format($booking->total_amount, 2) }}</strong> to confirm it.</p>
                </div>
                <form method="POST" action="{{ route('payments.checkout', $booking) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Complete Payment</button>
                </form>
            </div>
        </div>
    @endif

    @if ($audience === 'traveler' && session('status') && str_contains(session('status'), 'confirmed'))
        <div class="card border-success mb-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
                <div>
                    <h4 class="h6 fw-bold mb-1 text-success">Booking Confirmed!</h4>
                    <p class="mb-0 small text-secondary">Your booking reference is <strong class="font-monospace">{{ $booking->booking_reference }}</strong>. We've notified the provider.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h4 mb-1 font-monospace">{{ $booking->booking_reference }}</h3>
            <div class="small text-secondary">Booked on {{ $booking->created_at->format('M d, Y') }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-{{ $statusColors[$booking->booking_status] ?? 'secondary' }}">{{ $booking->booking_status }}</span>
            <span class="badge text-bg-{{ $paymentColors[$booking->payment_status] ?? 'secondary' }} border">Payment: {{ $booking->payment_status }}</span>
            <span class="badge text-bg-light border text-capitalize">{{ $booking->booking_type }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            @if ($booking->resort)
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Resort Stay</h4>
                        <div class="d-flex gap-3">
                            <img src="{{ $booking->resort->cover_image_url }}" alt="{{ $booking->resort->name }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-fill">
                                <div class="fw-semibold">{{ $booking->resort->name }}</div>
                                <div class="small text-secondary">{{ $booking->room?->room_name }} &middot; {{ $booking->resort->district }}, {{ $booking->resort->division }}</div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-4">
                                <div class="small text-secondary">Check-in</div>
                                <div class="fw-semibold">{{ $booking->check_in_date?->format('M d, Y') }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="small text-secondary">Check-out</div>
                                <div class="fw-semibold">{{ $booking->check_out_date?->format('M d, Y') }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="small text-secondary">Nights</div>
                                <div class="fw-semibold">{{ $booking->nights() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($booking->tourPackage)
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Tour Package</h4>
                        <div class="d-flex gap-3">
                            <img src="{{ $booking->tourPackage->cover_image_url }}" alt="{{ $booking->tourPackage->title }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-fill">
                                <div class="fw-semibold">{{ $booking->tourPackage->title }}</div>
                                <div class="small text-secondary">{{ $booking->tourPackage->destination }} &middot; {{ $booking->tourPackage->duration_days }}D / {{ $booking->tourPackage->duration_nights }}N</div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <div class="small text-secondary">Travel Date</div>
                                <div class="fw-semibold">{{ $booking->travel_date?->format('M d, Y') }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-secondary">Meeting Point</div>
                                <div class="fw-semibold">{{ $booking->tourPackage->meeting_point }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($booking->special_request)
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-2">Special Request</h4>
                        <p class="mb-0 text-body small">{{ $booking->special_request }}</p>
                    </div>
                </div>
            @endif

            @if (in_array($audience, ['partner', 'admin']))
                <div class="card">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Traveler</h4>
                        <div class="small text-secondary">{{ $booking->user->name }} &middot; {{ $booking->user->email }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="small text-secondary fw-semibold">Guests</div>
                    <div class="fs-4 fw-bold mt-1">{{ $booking->guests }}</div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="small text-secondary fw-semibold">Total Amount</div>
                    <div class="fs-3 fw-bold mt-1 font-monospace text-success">৳{{ number_format($booking->total_amount, 2) }}</div>
                </div>
            </div>

            @if ($audience === 'traveler' && $booking->isReviewable())
                <a href="{{ route('traveler.reviews.create', $booking) }}" class="btn btn-primary w-100 mb-3">Write a Review</a>
            @endif

            @can('complete', $booking)
                <form method="POST" action="{{ route('partner.bookings.complete', $booking) }}" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success w-100">Mark as Completed</button>
                </form>
            @endcan

            @if ($audience === 'traveler' && $booking->isCancellable())
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">Cancel Booking</button>

                <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancel this booking?</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to cancel booking <strong class="font-monospace">{{ $booking->booking_reference }}</strong>? This cannot be undone.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Booking</button>
                                <form method="POST" action="{{ route('traveler.bookings.cancel', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

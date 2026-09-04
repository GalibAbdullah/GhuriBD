@extends('layouts.app')

@section('title', 'Payment '.$payment->booking->booking_reference)
@section('page-title', 'Secure Payment')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('traveler.bookings.show', $payment->booking) }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Booking</a>
    </div>

    <div class="mx-auto" style="max-width: 480px;">
        @if ($errors->has('payment'))
            <div class="alert alert-danger">{{ $errors->first('payment') }}</div>
        @endif

        <div class="card">
            <div class="card-body text-center">
                <div class="small fw-semibold text-uppercase text-secondary mb-1">Mock Gateway &middot; Sandbox</div>
                <h3 class="h5 fw-bold mb-4">Complete Payment</h3>

                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <span class="text-secondary">Booking</span>
                    <span class="font-monospace">{{ $payment->booking->booking_reference }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <span class="text-secondary">Amount</span>
                    <span class="font-monospace fw-bold">৳{{ number_format((float) $payment->amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <span class="text-secondary">Reference</span>
                    <span class="font-monospace">{{ $payment->gateway_reference }}</span>
                </div>

                <p class="mt-3 small text-secondary">
                    This simulates a real gateway's redirect. No money moves and no card
                    details are collected — choose an outcome to continue.
                </p>

                <div class="d-grid gap-2 mt-4">
                    <form method="POST" action="{{ route('payments.callback', $payment) }}">
                        @csrf
                        <input type="hidden" name="decision" value="approve">
                        <button type="submit" class="btn btn-success w-100">Approve Payment</button>
                    </form>
                    <form method="POST" action="{{ route('payments.callback', $payment) }}">
                        @csrf
                        <input type="hidden" name="decision" value="decline">
                        <button type="submit" class="btn btn-outline-secondary w-100">Decline Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

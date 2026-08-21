@extends('layouts.app')

@section('title', 'Mock Payment Gateway')
@section('page-title', 'Payment')

@section('sidebar')
    <a href="{{ route('bookings.show', $payment->booking) }}" class="nav-item">Back to booking</a>
@endsection

@section('content')
    <div class="mx-auto max-w-[480px]">
        <div class="card card-pad text-center">
            <div class="mb-1 text-[12.5px] font-semibold uppercase tracking-wide text-ink-faint">Mock Gateway — Sandbox</div>
            <h3 class="mb-5 text-[16px] font-semibold">Complete Payment</h3>

            <div class="kv-row">
                <span class="kv-label">Booking</span>
                <span class="kv-value font-mono">{{ $payment->booking->reference }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Amount</span>
                <span class="kv-value font-mono">৳{{ number_format((float) $payment->amount, 2) }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Reference</span>
                <span class="kv-value font-mono">{{ $payment->gateway_reference }}</span>
            </div>

            <p class="mt-4 text-[12.5px] text-ink-muted">
                This simulates a real gateway's redirect. No money moves and no card
                details are collected — choose an outcome to continue.
            </p>

            <div class="mt-5 flex flex-col gap-2">
                <form method="POST" action="{{ route('payments.mock.callback', $payment) }}">
                    @csrf
                    <input type="hidden" name="decision" value="approve">
                    <button type="submit" class="btn btn-primary btn-block">Approve Payment</button>
                </form>
                <form method="POST" action="{{ route('payments.mock.callback', $payment) }}">
                    @csrf
                    <input type="hidden" name="decision" value="decline">
                    <button type="submit" class="btn btn-outline btn-block">Decline Payment</button>
                </form>
            </div>
        </div>
    </div>
@endsection

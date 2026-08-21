@extends('layouts.app')

@section('title', 'Booking ' . $booking->reference)
@section('page-title', 'Booking Details')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
    <a href="{{ route('bookings.index') }}" class="nav-item active">My Bookings</a>
@endsection

@section('content')
    <div class="mx-auto max-w-[620px]">
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-error bg-error-tint px-4 py-3 text-[13px] font-medium text-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card card-pad">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold">Booking {{ $booking->reference }}</h3>
                    <div class="text-[12.5px] text-ink-faint">Placed {{ $booking->created_at->format('M d, Y g:i A') }}</div>
                </div>
                <span class="{{ $booking->status->badgeClass() }}">{{ $booking->status->value }}</span>
            </div>

            @if ($booking->bookable)
                <div class="kv-row">
                    <span class="kv-label">Guide</span>
                    <span class="kv-value">{{ $booking->bookable->guide->name }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-label">Date &amp; time</span>
                    <span class="kv-value">{{ $booking->bookable->available_date->format('D, M j, Y') }} · {{ $booking->bookable->time_range }}</span>
                </div>
            @else
                <div class="kv-row">
                    <span class="kv-label">Slot</span>
                    <span class="kv-value text-ink-faint">No longer available</span>
                </div>
            @endif

            <div class="kv-row">
                <span class="kv-label">Travelers</span>
                <span class="kv-value">{{ $booking->party_size }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Price per traveler</span>
                <span class="kv-value font-mono">৳{{ number_format((float) $booking->unit_price, 2) }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Total</span>
                <span class="kv-value font-mono">৳{{ number_format((float) $booking->total_price, 2) }}</span>
            </div>

            @if ($booking->isCancelled() && $booking->cancellation_reason)
                <div class="kv-row">
                    <span class="kv-label">Cancellation reason</span>
                    <span class="kv-value">{{ $booking->cancellation_reason }}</span>
                </div>
            @endif

            @if ($booking->payments->isNotEmpty())
                <div class="mt-5">
                    <h3 class="mb-2 text-[13px] font-semibold">Payment history</h3>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->payments->sortByDesc('id') as $payment)
                                    <tr>
                                        <td class="font-mono">{{ $payment->gateway_reference }}</td>
                                        <td class="font-mono">৳{{ number_format((float) $payment->amount, 2) }}</td>
                                        <td><span class="{{ $payment->status->badgeClass() }}">{{ $payment->status->value }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="mt-5 flex gap-2">
                @if ($booking->canBePaid())
                    <a href="{{ route('bookings.checkout', $booking) }}" class="btn btn-primary btn-sm">
                        {{ $booking->payments->isEmpty() ? 'Pay now' : 'Try payment again' }}
                    </a>
                @endif

                @if ($booking->canBeCancelled())
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                          onsubmit="return confirm('Cancel this booking?');">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Cancel booking</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

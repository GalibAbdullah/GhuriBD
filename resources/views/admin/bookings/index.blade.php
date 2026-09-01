@extends('layouts.app')

@section('title', 'All Bookings')
@section('page-title', 'All Bookings')

@section('sidebar')
    @include('partials.admin-sidebar')
@endsection

@php
    $statusColors = ['Pending' => 'warning', 'Confirmed' => 'success', 'Cancelled' => 'secondary', 'Completed' => 'primary'];
@endphp

@section('content')
    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">All Bookings</h3>
            <p class="mb-0 small text-secondary">Every booking placed on the platform.</p>
        </div>
        <form method="GET">
            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($bookings->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                    <h3>No bookings match this filter.</h3>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Traveler</th>
                                <th>Type</th>
                                <th>Service</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="font-monospace small">{{ $booking->booking_reference }}</td>
                                    <td>{{ $booking->user->name }}</td>
                                    <td class="text-capitalize">{{ $booking->booking_type }}</td>
                                    <td class="small">{{ $booking->resort?->name }}{{ $booking->resort && $booking->tourPackage ? ' + ' : '' }}{{ $booking->tourPackage?->title }}</td>
                                    <td class="font-monospace">৳{{ number_format($booking->total_amount, 2) }}</td>
                                    <td><span class="badge text-bg-{{ $statusColors[$booking->booking_status] ?? 'secondary' }}">{{ $booking->booking_status }}</span></td>
                                    <td><span class="badge text-bg-light border">{{ $booking->payment_status }}</span></td>
                                    <td class="text-end"><a href="{{ route('admin.bookings.show', $booking) }}" class="small fw-semibold link-primary link-underline-opacity-0">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($bookings->hasPages())
            <div class="mt-4">{{ $bookings->links() }}</div>
        @endif
    @endif
@endsection

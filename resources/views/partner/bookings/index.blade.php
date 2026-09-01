@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('sidebar')
    @include('partials.partner-sidebar')
@endsection

@php
    $statusColors = ['Pending' => 'warning', 'Confirmed' => 'success', 'Cancelled' => 'secondary', 'Completed' => 'primary'];
@endphp

@section('content')
    <div class="mb-4">
        <h3 class="h5 mb-1">Bookings</h3>
        <p class="mb-0 small text-secondary">Bookings placed against your resorts and tour packages.</p>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a class="nav-link {{ ! $type ? 'active' : '' }}" href="{{ route('partner.bookings.index', ['status' => $status]) }}">All</a></li>
        <li class="nav-item"><a class="nav-link {{ $type === 'resort' ? 'active' : '' }}" href="{{ route('partner.bookings.index', ['type' => 'resort', 'status' => $status]) }}">Resort Bookings</a></li>
        <li class="nav-item"><a class="nav-link {{ $type === 'package' ? 'active' : '' }}" href="{{ route('partner.bookings.index', ['type' => 'package', 'status' => $status]) }}">Tour Package Bookings</a></li>
    </ul>

    <form method="GET" class="mb-4">
        <input type="hidden" name="type" value="{{ $type }}">
        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach ($statuses as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </form>

    @if ($bookings->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                    <h3>No bookings yet</h3>
                    <p>Once travelers book your listings, they'll appear here.</p>
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
                                <th>Dates</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="font-monospace small">{{ $booking->booking_reference }}</td>
                                    <td>{{ $booking->user->name }}</td>
                                    <td class="text-capitalize">{{ $booking->booking_type }}</td>
                                    <td class="small">
                                        @if ($booking->check_in_date){{ $booking->check_in_date->format('M d') }}–{{ $booking->check_out_date?->format('M d') }}@endif
                                        @if ($booking->check_in_date && $booking->travel_date)<br>@endif
                                        @if ($booking->travel_date)Travel: {{ $booking->travel_date->format('M d, Y') }}@endif
                                    </td>
                                    <td>{{ $booking->guests }}</td>
                                    <td class="font-monospace">৳{{ number_format($booking->total_amount, 2) }}</td>
                                    <td><span class="badge text-bg-{{ $statusColors[$booking->booking_status] ?? 'secondary' }}">{{ $booking->booking_status }}</span></td>
                                    <td class="text-end"><a href="{{ route('partner.bookings.show', $booking) }}" class="small fw-semibold link-primary link-underline-opacity-0">View</a></td>
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

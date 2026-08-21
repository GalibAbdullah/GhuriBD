@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('sidebar')
    @include('availability.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4 text-sm text-ink-muted">
        Travelers who have booked your availability slots.
    </div>

    <div class="card card-pad">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Traveler</th>
                        <th>Date</th>
                        <th>Travelers</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="font-mono">{{ $booking->reference }}</td>
                            <td>
                                {{ $booking->traveler->name }}
                                <div class="text-[11.5px] text-ink-faint">{{ $booking->traveler->email }}</div>
                            </td>
                            <td>{{ $booking->bookable?->available_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $booking->party_size }}</td>
                            <td class="font-mono">৳{{ number_format((float) $booking->total_price, 2) }}</td>
                            <td><span class="{{ $booking->status->badgeClass() }}">{{ $booking->status->value }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                                    <h3>No bookings yet</h3>
                                    <p>Once travelers book your availability, they'll appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($bookings->hasPages())
        <div class="mt-5">
            {{ $bookings->links() }}
        </div>
    @endif
@endsection

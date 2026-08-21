@extends('layouts.app')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
    <a href="{{ route('bookings.index') }}" class="nav-item active">My Bookings</a>
    <a href="{{ route('profile.show') }}" class="nav-item">My Profile</a>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        @foreach (['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $value => $label)
            <a href="{{ route('bookings.index', ['scope' => $value]) }}"
               class="chip {{ $scope === $value ? 'bg-primary-tint text-primary-dark' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card card-pad">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Guide</th>
                        <th>Date</th>
                        <th>Travelers</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="font-mono">{{ $booking->reference }}</td>
                            <td>{{ $booking->bookable?->guide?->name ?? '—' }}</td>
                            <td>{{ $booking->bookable?->available_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $booking->party_size }}</td>
                            <td class="font-mono">৳{{ number_format((float) $booking->total_price, 2) }}</td>
                            <td><span class="{{ $booking->status->badgeClass() }}">{{ $booking->status->value }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-[12.5px] font-semibold text-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                                    <h3>No bookings yet</h3>
                                    <p>Once you book a guide, it'll show up here.</p>
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

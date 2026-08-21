@extends('layouts.app')

@section('title', $guide->name)
@section('page-title', $guide->name)

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('bookings.index') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
        My Bookings
    </a>
@endsection

@section('content')
    <div class="mb-5 flex items-center gap-3">
        <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-full border border-line bg-primary-tint text-base font-bold text-primary-dark">
            {{ strtoupper(substr($guide->name, 0, 1)) }}
        </div>
        <div>
            <h3 class="text-[16px] font-semibold">{{ $guide->name }}</h3>
            <div class="text-[12.5px] text-ink-faint">Verified Tour Guide</div>
        </div>
    </div>

    <div class="card card-pad">
        <h3 class="mb-4 text-[16px] font-semibold">Available slots</h3>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Spots left</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slots as $slot)
                        <tr>
                            <td class="font-semibold">{{ $slot->available_date->format('D, M j, Y') }}</td>
                            <td>{{ $slot->time_range }}</td>
                            <td>{{ $slot->remainingCapacity() }} / {{ $slot->capacity }}</td>
                            <td class="font-mono">৳{{ number_format((float) $slot->price, 2) }}</td>
                            <td class="text-right">
                                @if ($slot->isBookable())
                                    <a href="{{ route('bookings.create', $slot) }}" class="btn btn-primary btn-sm">Book</a>
                                @else
                                    <span class="text-[11.5px] text-ink-faint">Unavailable</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                    <h3>No upcoming availability</h3>
                                    <p>This guide hasn't published any bookable slots yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($slots->hasPages())
        <div class="mt-5">
            {{ $slots->links() }}
        </div>
    @endif
@endsection

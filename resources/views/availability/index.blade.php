@extends('layouts.app')

@section('title', 'Availability')
@section('page-title', 'Guide Availability')

@section('sidebar')
    @include('availability.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-ink-muted">
            Publish the dates and times travelers can book you as a guide.
        </div>
        <div class="flex gap-2">
            <a href="{{ route('partner.availability.bulk') }}" class="btn btn-outline btn-sm">Bulk publish</a>
            <a href="{{ route('partner.availability.create') }}" class="btn btn-primary btn-sm">Add slot</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-error bg-error-tint px-4 py-3 text-[13px] font-medium text-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card"><div class="stat-label">Upcoming slots</div><div class="stat-value">{{ $summary['upcoming'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Open for booking</div><div class="stat-value">{{ $summary['available'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Blocked</div><div class="stat-value">{{ $summary['blocked'] }}</div></div>
        <div class="stat-card"><div class="stat-label">With bookings</div><div class="stat-value">{{ $summary['booked'] }}</div></div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        @foreach (['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $value => $label)
            <a href="{{ route('partner.availability.index', ['scope' => $value, 'status' => $status]) }}"
               class="chip {{ $scope === $value ? 'bg-primary-tint text-primary-dark' : '' }}">{{ $label }}</a>
        @endforeach

        <span class="mx-1 text-ink-faint">|</span>

        <a href="{{ route('partner.availability.index', ['scope' => $scope]) }}"
           class="chip {{ $status === null ? 'bg-primary-tint text-primary-dark' : '' }}">Any status</a>
        @foreach ($statuses as $case)
            <a href="{{ route('partner.availability.index', ['scope' => $scope, 'status' => $case->value]) }}"
               class="chip {{ $status === $case->value ? 'bg-primary-tint text-primary-dark' : '' }}">{{ $case->value }}</a>
        @endforeach
    </div>

    <div class="card card-pad">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slots as $slot)
                        <tr>
                            <td class="font-semibold">
                                {{ $slot->available_date->format('D, M j, Y') }}
                                @if ($slot->hasEnded())
                                    <div class="text-[11.5px] text-ink-faint">Finished</div>
                                @endif
                            </td>
                            <td>{{ $slot->time_range }}</td>
                            <td>
                                {{ $slot->booked_count }} / {{ $slot->capacity }}
                                @if ($slot->isFullyBooked())
                                    <div class="text-[11.5px] text-ink-faint">Full</div>
                                @endif
                            </td>
                            <td class="font-mono">৳{{ number_format((float) $slot->price, 2) }}</td>
                            <td><span class="{{ $slot->status->badgeClass() }}">{{ $slot->status->value }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                @if ($slot->canBeModified())
                                    <form method="POST" action="{{ route('partner.availability.toggle', $slot) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-[12.5px] font-semibold text-primary">
                                            {{ $slot->status->isAvailable() ? 'Block' : 'Reopen' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('partner.availability.edit', $slot) }}" class="ml-3 text-[12.5px] font-semibold text-primary">Edit</a>
                                    <form method="POST" action="{{ route('partner.availability.destroy', $slot) }}" class="ml-3 inline"
                                          onsubmit="return confirm('Remove this availability slot?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[12.5px] font-semibold text-error">Delete</button>
                                    </form>
                                @else
                                    <span class="text-[11.5px] text-ink-faint">
                                        {{ $slot->hasBookings() ? 'Locked — has bookings' : 'Locked — past' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                    <h3>No availability yet</h3>
                                    <p>Add the dates and times you're free, and travelers can start booking you.</p>
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

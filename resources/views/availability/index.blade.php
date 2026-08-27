@extends('layouts.app')

@section('title', 'Availability')
@section('page-title', 'Guide Availability')

@section('sidebar')
    @include('availability.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="small text-secondary">
            Publish the dates and times travelers can book you as a guide.
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('partner.availability.bulk') }}" class="btn btn-outline-secondary btn-sm">Bulk publish</a>
            <a href="{{ route('partner.availability.create') }}" class="btn btn-primary btn-sm">Add slot</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Upcoming slots</div><div class="fs-3 fw-bold mt-2">{{ $summary['upcoming'] }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Open for booking</div><div class="fs-3 fw-bold mt-2">{{ $summary['available'] }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Blocked</div><div class="fs-3 fw-bold mt-2">{{ $summary['blocked'] }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">With bookings</div><div class="fs-3 fw-bold mt-2">{{ $summary['booked'] }}</div></div></div></div>
    </div>

    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        @foreach (['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $value => $label)
            <a href="{{ route('partner.availability.index', ['scope' => $value, 'status' => $status]) }}"
               class="btn btn-sm rounded-pill {{ $scope === $value ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach

        <span class="mx-1 text-body-tertiary">|</span>

        <a href="{{ route('partner.availability.index', ['scope' => $scope]) }}"
           class="btn btn-sm rounded-pill {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}">Any status</a>
        @foreach ($statuses as $case)
            <a href="{{ route('partner.availability.index', ['scope' => $scope, 'status' => $case->value]) }}"
               class="btn btn-sm rounded-pill {{ $status === $case->value ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $case->value }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                                <td class="fw-semibold">
                                    {{ $slot->available_date->format('D, M j, Y') }}
                                    @if ($slot->hasEnded())
                                        <div class="small text-body-tertiary">Finished</div>
                                    @endif
                                </td>
                                <td>{{ $slot->time_range }}</td>
                                <td>
                                    {{ $slot->booked_count }} / {{ $slot->capacity }}
                                    @if ($slot->isFullyBooked())
                                        <div class="small text-body-tertiary">Full</div>
                                    @endif
                                </td>
                                <td class="font-monospace">৳{{ number_format((float) $slot->price, 2) }}</td>
                                <td><span class="{{ $slot->status->badgeClass() }}">{{ $slot->status->value }}</span></td>
                                <td class="text-end text-nowrap">
                                    @if ($slot->canBeModified())
                                        <form method="POST" action="{{ route('partner.availability.toggle', $slot) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-link btn-sm p-0 small fw-semibold text-decoration-none">
                                                {{ $slot->status->isAvailable() ? 'Block' : 'Reopen' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('partner.availability.edit', $slot) }}" class="ms-3 small fw-semibold link-primary link-underline-opacity-0">Edit</a>
                                        <form method="POST" action="{{ route('partner.availability.destroy', $slot) }}" class="ms-3 d-inline"
                                              onsubmit="return confirm('Remove this availability slot?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm p-0 small fw-semibold text-danger text-decoration-none">Delete</button>
                                        </form>
                                    @else
                                        <span class="small text-body-tertiary">
                                            {{ $slot->hasBookings() ? 'Locked — has bookings' : 'Locked — past' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
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
    </div>

    @if ($slots->hasPages())
        <div class="mt-4">
            {{ $slots->links() }}
        </div>
    @endif
@endsection

@extends('layouts.app')

@section('title', 'My Rooms')
@section('page-title', 'My Rooms')

@section('sidebar')
    @include('resorts.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route($isAdmin ? 'admin.resorts.show' : 'partner.resorts.show', $resort) }}" class="small fw-semibold link-secondary link-underline-opacity-0">
            &larr; Back to {{ $resort->name }}
        </a>
    </div>

    <div class="mb-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">{{ $resort->name }}</h3>
            <p class="mb-0 small text-secondary">My Rooms</p>
        </div>
        @unless ($isAdmin)
            <a href="{{ route('partner.resorts.rooms.create', $resort) }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
                Add Room
            </a>
        @endunless
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route($isAdmin ? 'admin.resorts.rooms.index' : 'partner.resorts.rooms.index', $resort) }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by room name">
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Price / Night</th>
                            <th>Capacity</th>
                            <th>Available / Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $room)
                            <tr>
                                <td>
                                    <img src="{{ $room->cover_image_url }}" alt="{{ $room->room_name }}" class="rounded" style="width: 56px; height: 56px; object-fit: cover;">
                                </td>
                                <td class="fw-semibold">{{ $room->room_name }}</td>
                                <td><span class="badge text-bg-light border">{{ $room->room_type }}</span></td>
                                <td class="font-monospace">৳{{ number_format($room->price_per_night, 2) }}</td>
                                <td>{{ $room->capacity }}</td>
                                <td>{{ $room->available_rooms }} / {{ $room->total_rooms }}</td>
                                <td>
                                    @if ($room->isAvailable())
                                        <span class="badge text-bg-success">Available</span>
                                    @else
                                        <span class="badge text-bg-secondary">Unavailable</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route($isAdmin ? 'admin.resorts.rooms.show' : 'partner.resorts.rooms.show', [$resort, $room]) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">View</a>
                                    @unless ($isAdmin)
                                        <a href="{{ route('partner.resorts.rooms.edit', [$resort, $room]) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">Edit</a>
                                        <button type="button" class="small fw-semibold link-danger link-underline-opacity-0 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#deleteRoomModal" data-room-name="{{ $room->room_name }}" data-room-action="{{ route('partner.resorts.rooms.destroy', [$resort, $room]) }}">
                                            Delete
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                        <h3>{{ $search ? 'No rooms match your search.' : 'No rooms added yet.' }}</h3>
                                        <p>
                                            @unless ($isAdmin)
                                                Add your first room to start accepting bookings.
                                            @endunless
                                        </p>
                                        @if (! $isAdmin && ! $search)
                                            <a href="{{ route('partner.resorts.rooms.create', $resort) }}" class="btn btn-primary btn-sm mt-2">Add Room</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($rooms->hasPages())
        <div class="mt-4">
            {{ $rooms->links() }}
        </div>
    @endif

    @unless ($isAdmin)
        <!-- Delete confirmation modal -->
        <div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete room?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="deleteRoomName"></strong>? This will permanently remove the room and all of its photos. This cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteRoomForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Room</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const deleteModal = document.getElementById('deleteRoomModal');
                if (!deleteModal) return;

                deleteModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    document.getElementById('deleteRoomName').textContent = button.dataset.roomName;
                    document.getElementById('deleteRoomForm').action = button.dataset.roomAction;
                });
            });
        </script>
    @endunless
@endsection

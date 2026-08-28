@extends('layouts.app')

@section('title', $isAdmin ? 'All Resorts' : 'My Resorts')
@section('page-title', $isAdmin ? 'All Resorts' : 'My Resorts')

@section('sidebar')
    @include('resorts.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">{{ $isAdmin ? 'All Resorts' : 'My Resorts' }}</h3>
            <p class="mb-0 small text-secondary">
                {{ $isAdmin ? 'Browse every resort listed on the platform.' : 'Manage your listed resorts.' }}
            </p>
        </div>
        @unless ($isAdmin)
            <a href="{{ route('partner.resorts.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
                Add Resort
            </a>
        @endunless
    </div>

    <!-- Stats -->
    <div class="row row-cols-1 row-cols-sm-3 g-4 mb-4">
        <div class="col">
            <div class="card h-100"><div class="card-body">
                <div class="small text-secondary fw-semibold">Total Resorts</div>
                <div class="fs-3 fw-bold mt-2">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col">
            <div class="card h-100"><div class="card-body">
                <div class="small text-secondary fw-semibold">Active Resorts</div>
                <div class="fs-3 fw-bold mt-2 text-success">{{ $stats['active'] }}</div>
            </div></div>
        </div>
        <div class="col">
            <div class="card h-100"><div class="card-body">
                <div class="small text-secondary fw-semibold">Inactive Resorts</div>
                <div class="fs-3 fw-bold mt-2 text-secondary">{{ $stats['inactive'] }}</div>
            </div></div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route($isAdmin ? 'admin.resorts.index' : 'partner.resorts.index') }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, district, or division">
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Resort</th>
                            @if ($isAdmin)
                                <th>Owner</th>
                            @endif
                            <th>District</th>
                            <th>Division</th>
                            <th>Price Range</th>
                            <th>Status</th>
                            <th>Rooms</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resorts as $resort)
                            <tr>
                                <td>
                                    <img src="{{ $resort->cover_image_url }}" alt="{{ $resort->name }}" class="rounded" style="width: 56px; height: 56px; object-fit: cover;">
                                </td>
                                <td class="fw-semibold">{{ $resort->name }}</td>
                                @if ($isAdmin)
                                    <td>{{ $resort->user->name }}</td>
                                @endif
                                <td>{{ $resort->district }}</td>
                                <td>{{ $resort->division }}</td>
                                <td>{{ $resort->price_range }}</td>
                                <td>
                                    @if ($resort->isActive())
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td><span class="badge text-bg-light border">{{ $resort->rooms_count }}</span></td>
                                <td>{{ $resort->created_at->format('M d, Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route($isAdmin ? 'admin.resorts.show' : 'partner.resorts.show', $resort) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">View</a>
                                    <a href="{{ route($isAdmin ? 'admin.resorts.rooms.index' : 'partner.resorts.rooms.index', $resort) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">Manage Rooms</a>
                                    @unless ($isAdmin)
                                        <a href="{{ route('partner.resorts.edit', $resort) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">Edit</a>
                                        <button type="button" class="small fw-semibold link-danger link-underline-opacity-0 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#deleteResortModal" data-resort-name="{{ $resort->name }}" data-resort-action="{{ route('partner.resorts.destroy', $resort) }}">
                                            Delete
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 9 : 8 }}">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                                        <h3>{{ $search ? 'No resorts match your search.' : "You haven't added any resorts yet." }}</h3>
                                        <p>
                                            @unless ($isAdmin)
                                                Add your first resort listing to start reaching travelers.
                                            @endunless
                                        </p>
                                        @if (! $isAdmin && ! $search)
                                            <a href="{{ route('partner.resorts.create') }}" class="btn btn-primary btn-sm mt-2">Add Resort</a>
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

    @if ($resorts->hasPages())
        <div class="mt-4">
            {{ $resorts->links() }}
        </div>
    @endif

    @unless ($isAdmin)
        <!-- Delete confirmation modal -->
        <div class="modal fade" id="deleteResortModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete resort?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="deleteResortName"></strong>? This will permanently remove the listing and all of its photos. This cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteResortForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Resort</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const deleteModal = document.getElementById('deleteResortModal');
                if (!deleteModal) return;

                deleteModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    document.getElementById('deleteResortName').textContent = button.dataset.resortName;
                    document.getElementById('deleteResortForm').action = button.dataset.resortAction;
                });
            });
        </script>
    @endunless
@endsection

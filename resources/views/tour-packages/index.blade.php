@extends('layouts.app')

@section('title', $isAdmin ? 'All Tour Packages' : 'My Tour Packages')
@section('page-title', $isAdmin ? 'All Tour Packages' : 'My Tour Packages')

@section('sidebar')
    @include('tour-packages.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">{{ $isAdmin ? 'All Tour Packages' : 'My Tour Packages' }}</h3>
            <p class="mb-0 small text-secondary">
                {{ $isAdmin ? 'Browse every tour package listed on the platform.' : 'Manage your tour packages.' }}
            </p>
        </div>
        @unless ($isAdmin)
            <a href="{{ route('partner.packages.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
                Create Package
            </a>
        @endunless
    </div>

    <!-- Stats -->
    <div class="row row-cols-1 row-cols-sm-3 g-4 mb-4">
        <div class="col">
            <div class="card h-100"><div class="card-body">
                <div class="small text-secondary fw-semibold">Total Packages</div>
                <div class="fs-3 fw-bold mt-2">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col">
            <div class="card h-100"><div class="card-body">
                <div class="small text-secondary fw-semibold">Active Packages</div>
                <div class="fs-3 fw-bold mt-2 text-success">{{ $stats['active'] }}</div>
            </div></div>
        </div>
        <div class="col">
            <div class="card h-100"><div class="card-body">
                <div class="small text-secondary fw-semibold">Inactive Packages</div>
                <div class="fs-3 fw-bold mt-2 text-secondary">{{ $stats['inactive'] }}</div>
            </div></div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route($isAdmin ? 'admin.packages.index' : 'partner.packages.index') }}" class="mb-4">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </span>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by package title">
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Package</th>
                            @if ($isAdmin)
                                <th>Owner</th>
                            @endif
                            <th>Destination</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tourPackages as $tourPackage)
                            <tr>
                                <td>
                                    <img src="{{ $tourPackage->cover_image_url }}" alt="{{ $tourPackage->title }}" class="rounded" style="width: 56px; height: 56px; object-fit: cover;">
                                </td>
                                <td class="fw-semibold">{{ $tourPackage->title }}</td>
                                @if ($isAdmin)
                                    <td>{{ $tourPackage->user->name }}</td>
                                @endif
                                <td>{{ $tourPackage->destination }}</td>
                                <td>{{ $tourPackage->duration_days }}D / {{ $tourPackage->duration_nights }}N</td>
                                <td class="font-monospace">৳{{ number_format($tourPackage->price, 2) }}</td>
                                <td>
                                    @if ($tourPackage->isActive())
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $tourPackage->created_at->format('M d, Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route($isAdmin ? 'admin.packages.show' : 'partner.packages.show', $tourPackage) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">View</a>
                                    @unless ($isAdmin)
                                        <a href="{{ route('partner.packages.edit', $tourPackage) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">Edit</a>
                                        <button type="button" class="small fw-semibold link-danger link-underline-opacity-0 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#deletePackageModal" data-package-title="{{ $tourPackage->title }}" data-package-action="{{ route('partner.packages.destroy', $tourPackage) }}">
                                            Delete
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                                        <h3>{{ $search ? 'No tour packages match your search.' : "You haven't created any tour packages yet." }}</h3>
                                        <p>
                                            @unless ($isAdmin)
                                                Create your first tour package to start reaching travelers.
                                            @endunless
                                        </p>
                                        @if (! $isAdmin && ! $search)
                                            <a href="{{ route('partner.packages.create') }}" class="btn btn-primary btn-sm mt-2">Create Package</a>
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

    @if ($tourPackages->hasPages())
        <div class="mt-4">
            {{ $tourPackages->links() }}
        </div>
    @endif

    @unless ($isAdmin)
        <!-- Delete confirmation modal -->
        <div class="modal fade" id="deletePackageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete tour package?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="deletePackageTitle"></strong>? This will permanently remove the listing and all of its photos. This cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <form id="deletePackageForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Package</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const deleteModal = document.getElementById('deletePackageModal');
                if (!deleteModal) return;

                deleteModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    document.getElementById('deletePackageTitle').textContent = button.dataset.packageTitle;
                    document.getElementById('deletePackageForm').action = button.dataset.packageAction;
                });
            });
        </script>
    @endunless
@endsection

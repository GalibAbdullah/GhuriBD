@extends('layouts.app')

@section('title', 'Partner Dashboard')
@section('page-title', 'Partner Dashboard')

@section('sidebar')
    @include('partials.partner-sidebar')
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Manage your resorts, tour packages, and bookings.
    </div>

    @php
        $tourPackageStats = [
            'total' => auth()->user()->tourPackages()->count(),
            'active' => auth()->user()->tourPackages()->where('status', 'Active')->count(),
        ];
        $recentTourPackages = auth()->user()->tourPackages()->latest()->take(3)->get();
        $monthlyBookingsCount = \App\Models\Booking::query()
            ->forPartner(auth()->user())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    @endphp

    <!-- Tour package stats -->
    <div class="row row-cols-1 row-cols-sm-2 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Total Packages</div><div class="fs-3 fw-bold mt-2">{{ $tourPackageStats['total'] }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Active Packages</div><div class="fs-3 fw-bold mt-2 text-success">{{ $tourPackageStats['active'] }}</div></div></div></div>
    </div>

    <!-- Stats -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">This month's bookings</div><div class="fs-3 fw-bold mt-2">{{ $monthlyBookingsCount }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Revenue (Aug)</div><div class="fs-3 fw-bold mt-2 font-monospace">৳0</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Occupancy rate</div><div class="fs-3 fw-bold mt-2">—</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Avg. rating</div><div class="fs-3 fw-bold mt-2">—</div></div></div></div>
    </div>

    <!-- Recent tour packages -->
    <div class="mb-4">
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Recent tour packages</h3>
            @if ($recentTourPackages->isNotEmpty())
                <a href="{{ route('partner.packages.index') }}" class="small fw-semibold link-primary link-underline-opacity-0">View all</a>
            @endif
        </div>
        @if ($recentTourPackages->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                        <h3>You haven't created any tour packages yet.</h3>
                        <p>Create your first tour package to start reaching travelers.</p>
                        <a href="{{ route('partner.packages.create') }}" class="btn btn-primary btn-sm mt-2">Create Package</a>
                    </div>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-sm-3 g-4">
                @foreach ($recentTourPackages as $tourPackage)
                    <div class="col">
                        <div class="card h-100">
                            <img src="{{ $tourPackage->cover_image_url }}" alt="{{ $tourPackage->title }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                            <div class="card-body">
                                <h4 class="h6 fw-semibold mb-1">{{ $tourPackage->title }}</h4>
                                <div class="small text-secondary mb-2">{{ $tourPackage->destination }}</div>
                                <div class="d-flex align-items-center justify-content-between">
                                    @if ($tourPackage->isActive())
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                    <div>
                                        <a href="{{ route('partner.packages.show', $tourPackage) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">View</a>
                                        <a href="{{ route('partner.packages.edit', $tourPackage) }}" class="small fw-semibold link-primary link-underline-opacity-0 me-2">Edit</a>
                                        <button type="button" class="small fw-semibold link-danger link-underline-opacity-0 border-0 bg-transparent p-0" data-bs-toggle="modal" data-bs-target="#deletePackageModal" data-package-title="{{ $tourPackage->title }}" data-package-action="{{ route('partner.packages.destroy', $tourPackage) }}">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

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
        @endif
    </div>

    <!-- Quick actions -->
    <div>
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Quick actions</h3>
        </div>
        <div class="row row-cols-1 row-cols-sm-3 g-4">
            <div class="col">
                <a href="{{ route('partner.resorts.create') }}" class="card card-body text-decoration-none h-100">
                    <div class="text-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                    </div>
                    <div class="mt-2 small fw-semibold">Add a resort</div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('partner.packages.create') }}" class="card card-body text-decoration-none h-100">
                    <div class="text-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                    </div>
                    <div class="mt-2 small fw-semibold">Create tour package</div>
                </a>
            </div>
            @if (auth()->user()->isVerifiedTourGuide())
                <div class="col">
                    <a href="{{ route('partner.availability.index') }}" class="card card-body text-decoration-none h-100">
                        <div class="text-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                        </div>
                        <div class="mt-2 small fw-semibold">Update availability</div>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

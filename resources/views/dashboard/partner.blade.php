@extends('layouts.app')

@section('title', 'Partner Dashboard')
@section('page-title', 'Partner Dashboard')

@section('sidebar')
    <a href="{{ route('partner.dashboard') }}" class="list-group-item list-group-item-action border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">Dashboard</a>
    <a href="{{ route('partner.resorts.index') }}" class="list-group-item list-group-item-action border-0 rounded">My Resorts</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Tour Packages</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Guides</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Availability</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Bookings</a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action border-0 rounded">Messages</a>
    <a href="{{ route('partner.verifications.status') }}" class="list-group-item list-group-item-action border-0 rounded">Verification</a>
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Manage your resorts, tour packages, and bookings.
    </div>

    <!-- Stats -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">This month's bookings</div><div class="fs-3 fw-bold mt-2">0</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Revenue (Aug)</div><div class="fs-3 fw-bold mt-2 font-monospace">৳0</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Occupancy rate</div><div class="fs-3 fw-bold mt-2">—</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Avg. rating</div><div class="fs-3 fw-bold mt-2">—</div></div></div></div>
    </div>

    <!-- Recent bookings empty -->
    <div class="mb-4">
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Recent bookings</h3>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                    <h3>No bookings yet</h3>
                    <p>Once travelers book your listings, they'll appear here.</p>
                </div>
            </div>
        </div>
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
                <a href="{{ route('explore') }}" class="card card-body text-decoration-none h-100">
                    <div class="text-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                    </div>
                    <div class="mt-2 small fw-semibold">Create tour package</div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('explore') }}" class="card card-body text-decoration-none h-100">
                    <div class="text-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    </div>
                    <div class="mt-2 small fw-semibold">Update availability</div>
                </a>
            </div>
        </div>
    </div>
@endsection

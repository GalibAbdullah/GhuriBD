@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="9" cy="8" r="3.2"/><path d="M2.5 20c1-3.4 3.6-5 6.5-5s5.5 1.6 6.5 5"/><circle cx="17" cy="8" r="2.6"/><path d="M15.5 5.2A2.6 2.6 0 0121 8c0 1.6-1.2 2.6-1.2 2.6"/></svg>
        Users
    </a>
    <a href="{{ route('admin.verifications.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
        Verification
    </a>
    <a href="{{ route('admin.resorts.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
        Resorts
    </a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
        Packages
    </a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
        Bookings
    </a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20M16 15h2"/></svg>
        Payments
    </a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M5 21V4"/><path d="M5 4h13l-3 4 3 4H5"/></svg>
        Complaints
    </a>
    <a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M4 20V10M12 20V4M20 20v-7"/><path d="M2 20h20"/></svg>
        Analytics
    </a>
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Monitor, verify, and manage the GhuriBD platform.
    </div>

    <!-- Stats -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Total bookings (Aug)</div><div class="fs-3 fw-bold mt-2">0</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Platform revenue</div><div class="fs-3 fw-bold mt-2 font-monospace">৳0</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Pending verifications</div><div class="fs-3 fw-bold mt-2">{{ $pendingCount }}</div></div></div></div>
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Open complaints</div><div class="fs-3 fw-bold mt-2">0</div></div></div></div>
    </div>

    <!-- Verification queue -->
    <div class="mb-4">
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Verification queue</h3>
            <a href="{{ route('admin.verifications.index') }}" class="small fw-semibold link-primary link-underline-opacity-0">View all</a>
        </div>

        @if ($pendingVerifications->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
                        <h3>No pending verifications</h3>
                        <p>When travel partners submit documents, they'll appear here for review.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Type</th>
                                    <th>Partner</th>
                                    <th>Submitted</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingVerifications as $verification)
                                    <tr>
                                        <td class="fw-semibold">{{ $verification->provider_name }}</td>
                                        <td>{{ $verification->provider_type }}</td>
                                        <td>
                                            {{ $verification->user->name }}
                                            <div class="small text-body-tertiary">{{ $verification->user->email }}</div>
                                        </td>
                                        <td>{{ $verification->created_at->format('M d, Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.verifications.show', $verification) }}" class="small fw-semibold link-primary link-underline-opacity-0">Review</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Complaints empty -->
    <div>
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h3 class="h6">Open complaints</h3>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M5 21V4"/><path d="M5 4h13l-3 4 3 4H5"/></svg>
                    <h3>No open complaints</h3>
                    <p>Traveler and partner complaints will be tracked here.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

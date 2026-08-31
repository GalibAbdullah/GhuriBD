@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('sidebar')
    @include('partials.admin-sidebar')
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

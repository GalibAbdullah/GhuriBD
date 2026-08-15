@extends('layouts.app')

@section('title', 'Partner Dashboard')
@section('page-title', 'Partner Dashboard')

@section('sidebar')
    <a href="{{ route('partner.dashboard') }}" class="nav-item active">Dashboard</a>
    <a href="{{ route('explore') }}" class="nav-item">My Resorts</a>
    <a href="{{ route('explore') }}" class="nav-item">Tour Packages</a>
    <a href="{{ route('explore') }}" class="nav-item">Guides</a>
    <a href="{{ route('explore') }}" class="nav-item">Availability</a>
    <a href="{{ route('explore') }}" class="nav-item">Bookings</a>
    <a href="{{ route('explore') }}" class="nav-item">Messages</a>
    <a href="{{ route('partner.verifications.status') }}" class="nav-item">Verification</a>
@endsection

@section('content')
    <div class="mb-4 text-sm text-ink-muted">
        Manage your resorts, tour packages, and bookings.
    </div>

    <!-- Stats -->
    <div class="mb-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card"><div class="stat-label">This month's bookings</div><div class="stat-value">0</div></div>
        <div class="stat-card"><div class="stat-label">Revenue (Aug)</div><div class="stat-value font-mono">৳0</div></div>
        <div class="stat-card"><div class="stat-label">Occupancy rate</div><div class="stat-value">—</div></div>
        <div class="stat-card"><div class="stat-label">Avg. rating</div><div class="stat-value">—</div></div>
    </div>

    <!-- Recent bookings empty -->
    <div class="mb-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-[16px]">Recent bookings</h3>
        </div>
        <div class="card card-pad">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
                <h3>No bookings yet</h3>
                <p>Once travelers book your listings, they'll appear here.</p>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-[16px]">Quick actions</h3>
        </div>
        <div class="grid gap-5 sm:grid-cols-3">
            <a href="{{ route('explore') }}" class="card card-pad text-left no-underline">
                <div class="text-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
                </div>
                <div class="mt-2 text-[13px] font-semibold">Add a resort</div>
            </a>
            <a href="{{ route('explore') }}" class="card card-pad text-left no-underline">
                <div class="text-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
                </div>
                <div class="mt-2 text-[13px] font-semibold">Create tour package</div>
            </a>
            <a href="{{ route('explore') }}" class="card card-pad text-left no-underline">
                <div class="text-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                </div>
                <div class="mt-2 text-[13px] font-semibold">Update availability</div>
            </a>
        </div>
    </div>
@endsection
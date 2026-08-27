@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        Dashboard
    </a>
    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        Profile
    </a>
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 560px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-3 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">Profile</h3>
                    <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm">Edit Profile</a>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <img
                        src="{{ auth()->user()->profile_photo_url }}"
                        alt="Profile photo"
                        class="rounded-circle border"
                        style="width: 64px; height: 64px; object-fit: cover;"
                    >
                    <div>
                        <h3 class="h6 fw-semibold mb-0">{{ auth()->user()->name }}</h3>
                        <div class="small text-secondary">{{ auth()->user()->email }}</div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            @foreach (auth()->user()->getRoleNames() as $role)
                                <span class="badge text-bg-secondary">{{ $role }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between px-0 py-2"><span class="text-secondary">Phone</span><span class="fw-semibold">{{ auth()->user()->phone ?? 'Not provided' }}</span></div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2"><span class="text-secondary">Date of Birth</span><span class="fw-semibold">{{ auth()->user()->date_of_birth?->format('M d, Y') ?? 'Not provided' }}</span></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between px-0 py-2"><span class="text-secondary">Gender</span><span class="fw-semibold">{{ auth()->user()->gender ?? 'Not provided' }}</span></div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2"><span class="text-secondary">Address</span><span class="fw-semibold">{{ auth()->user()->address ?? 'Not provided' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

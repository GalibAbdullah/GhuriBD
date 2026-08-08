@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('sidebar')
    <a href="{{ route('home') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        Home
    </a>
    <a href="{{ route('profile.show') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        Profile
    </a>
@endsection

@section('content')
    <div class="mx-auto max-w-[560px]">
        <div class="card card-pad">
            <div class="flex items-center gap-4">
                <img
                    src="{{ auth()->user()->profile_photo ? \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_photo) : asset('images/default-avatar.svg') }}"
                    alt="Profile photo"
                    class="h-16 w-16 rounded-full border border-line object-cover"
                >
                <div>
                    <h3 class="text-[16px] font-semibold">{{ auth()->user()->name }}</h3>
                    <div class="text-[12.5px] text-ink-muted">{{ auth()->user()->email }}</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (auth()->user()->getRoleNames() as $role)
                            <span class="badge badge-neutral">{{ $role }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-pad mt-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="kv-row"><span class="kv-label">Phone</span><span class="font-semibold">{{ auth()->user()->phone ?? 'Not provided' }}</span></div>
                    <div class="kv-row"><span class="kv-label">Date of Birth</span><span class="font-semibold">{{ auth()->user()->date_of_birth?->format('M d, Y') ?? 'Not provided' }}</span></div>
                </div>
                <div>
                    <div class="kv-row"><span class="kv-label">Gender</span><span class="font-semibold">{{ auth()->user()->gender ?? 'Not provided' }}</span></div>
                    <div class="kv-row"><span class="kv-label">Address</span><span class="font-semibold">{{ auth()->user()->address ?? 'Not provided' }}</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection
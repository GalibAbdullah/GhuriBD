@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('notifications.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
        Notifications
    </a>
    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        My Profile
    </a>
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Review updates about your account and platform activity.
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h3 class="h6 fw-semibold">Notification inbox</h3>

                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Mark all as read</button>
                    </form>
                @endif
            </div>

            @forelse ($notifications as $notification)
                <div class="d-flex align-items-start gap-3 border-bottom py-3 {{ $loop->last ? 'border-bottom-0' : '' }} {{ $notification->read_at ? '' : 'bg-primary-subtle bg-opacity-25' }}">
                    <span class="mt-2 rounded-circle flex-shrink-0 {{ $notification->read_at ? 'bg-secondary-subtle' : 'bg-primary' }}" style="width: .625rem; height: .625rem;"></span>

                    <div class="min-w-0 flex-fill">
                        <a href="{{ route('notifications.redirect', $notification) }}" class="d-block text-decoration-none">
                            <div class="d-flex align-items-center gap-2">
                                <div class="small fw-semibold {{ $notification->read_at ? 'text-body' : 'text-primary-emphasis' }}">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </div>
                                @if ($notification->read_at)
                                    <span class="badge text-bg-secondary">Read</span>
                                @else
                                    <span class="badge text-bg-warning">Unread</span>
                                @endif
                            </div>
                            <div class="mt-1 small text-secondary">
                                {{ $notification->data['message'] ?? '' }}
                            </div>
                        </a>
                        <div class="mt-1 small text-body-tertiary">{{ $notification->created_at->format('M d, Y h:i A') }}</div>
                    </div>

                    <div class="d-flex flex-shrink-0 align-items-center gap-2">
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-link btn-sm text-secondary text-decoration-none p-0 small fw-semibold">Mark read</button>
                            </form>
                        @endif
                        <a href="{{ route('notifications.redirect', $notification) }}" class="small fw-semibold link-primary link-underline-opacity-0">View →</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
                    <h3>No notifications yet.</h3>
                    <p>When something happens on the platform, you'll see it here.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
@endsection

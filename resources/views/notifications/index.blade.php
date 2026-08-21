@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('notifications.index') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
        Notifications
    </a>
    <a href="{{ route('profile.show') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        My Profile
    </a>
@endsection

@section('content')
    <div class="mb-4 text-sm text-ink-muted">
        Review updates about your account and platform activity.
    </div>

    <div class="card card-pad">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-[16px] font-semibold">Notification inbox</h3>

            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline btn-sm">Mark all as read</button>
                </form>
            @endif
        </div>

        @forelse ($notifications as $notification)
            <div class="flex items-start gap-4 border-b border-line-soft py-4 last:border-b-0 {{ $notification->read_at ? '' : 'bg-primary-tint/40' }}">
                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->read_at ? 'bg-line' : 'bg-primary' }}"></span>

                <div class="min-w-0 flex-1">
                    <a href="{{ route('notifications.redirect', $notification) }}" class="block">
                        <div class="flex items-center gap-2">
                            <div class="text-[13.5px] font-semibold {{ $notification->read_at ? 'text-ink' : 'text-primary-dark' }}">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </div>
                            @if ($notification->read_at)
                                <span class="badge badge-neutral">Read</span>
                            @else
                                <span class="badge badge-warning">Unread</span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-[13px] text-ink-muted">
                            {{ $notification->data['message'] ?? '' }}
                        </div>
                    </a>
                    <div class="mt-1.5 text-[11.5px] text-ink-faint">{{ $notification->created_at->format('M d, Y h:i A') }}</div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="text-[11.5px] font-semibold text-ink-muted hover:text-primary">Mark read</button>
                        </form>
                    @endif
                    <a href="{{ route('notifications.redirect', $notification) }}" class="text-[11.5px] font-semibold text-primary">View →</a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
                <h3>No notifications yet.</h3>
                <p>When something happens on the platform, you'll see it here.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-5">
            {{ $notifications->links() }}
        </div>
    @endif
@endsection
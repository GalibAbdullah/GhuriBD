@php
    $unreadNotificationsCount = $unreadNotificationsCount ?? 0;
    $recentNotifications = $recentNotifications ?? collect();
@endphp

<div class="dropdown">
    <button
        type="button"
        class="position-relative d-flex align-items-center justify-content-center rounded-circle border bg-white text-secondary"
        style="width: 34px; height: 34px;"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="Notifications"
        title="Notifications"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>

        @if ($unreadNotificationsCount > 0)
            <span class="position-absolute top-0 end-0 d-flex align-items-center justify-content-center rounded-circle bg-danger text-white fw-bold" style="height: 17px; min-width: 17px; font-size: 10px; padding: 0 .25rem;">
                {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
            </span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end p-0 shadow border-0" style="width: 340px; max-width: 90vw;">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <span class="fw-semibold small">Notifications</span>

            @if ($unreadNotificationsCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none small">Mark all as read</button>
                </form>
            @endif
        </div>

        <div style="max-height: 360px; overflow-y: auto;">
            @if (request()->routeIs('notifications.index'))
                <div class="px-3 py-4 text-center small text-secondary">You're viewing your full inbox below.</div>
            @else
                @forelse ($recentNotifications as $notification)
                    <a
                        href="{{ route('notifications.redirect', $notification) }}"
                        class="d-flex align-items-start gap-2 px-3 py-2 text-decoration-none border-bottom {{ $notification->read_at ? '' : 'bg-primary-subtle bg-opacity-25' }}"
                    >
                        <span class="mt-1 rounded-circle flex-shrink-0 {{ $notification->read_at ? 'bg-secondary-subtle' : 'bg-primary' }}" style="width: .5rem; height: .5rem;"></span>

                        <div class="min-w-0 flex-fill">
                            <div class="small text-truncate {{ $notification->read_at ? 'text-body' : 'fw-semibold text-body' }}">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </div>
                            <div class="small text-secondary text-truncate">
                                {{ $notification->data['message'] ?? '' }}
                            </div>
                            <div class="small text-body-tertiary">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="px-3 py-4 text-center small text-secondary">No notifications yet.</div>
                @endforelse
            @endif
        </div>

        <a href="{{ route('notifications.index') }}" class="d-block text-center py-2 small fw-semibold link-primary link-underline-opacity-0">
            See all notifications
        </a>
    </div>
</div>

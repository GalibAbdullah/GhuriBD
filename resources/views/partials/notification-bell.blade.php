<a href="{{ route('notifications.index') }}" class="position-relative d-flex align-items-center justify-content-center rounded-circle border bg-white text-secondary" style="width: 34px; height: 34px;" aria-label="Notifications" title="Notifications">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>

    @if ($unreadNotificationsCount > 0)
        <span class="position-absolute top-0 end-0 d-flex align-items-center justify-content-center rounded-circle bg-danger text-white fw-bold" style="height: 17px; min-width: 17px; font-size: 10px; padding: 0 .25rem;">
            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
        </span>
    @endif
</a>

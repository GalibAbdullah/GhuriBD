<a href="{{ route('notifications.index') }}" class="relative grid h-[34px] w-[34px] place-items-center rounded-full border border-line bg-surface text-ink-muted transition-colors hover:bg-bg hover:text-ink" aria-label="Notifications" title="Notifications">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>

    @if ($unreadNotificationsCount > 0)
        <span class="absolute right-0 top-0 grid h-[17px] min-w-[17px] place-items-center rounded-full bg-error px-1 text-[10px] font-bold text-white">
            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
        </span>
    @endif
</a>
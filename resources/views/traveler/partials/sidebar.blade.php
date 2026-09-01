<a href="{{ route('traveler.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('traveler.dashboard') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
    Home
</a>
<a href="{{ route('search.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('search.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="12" r="9"/><path d="M15 9l-2 6-6 2 2-6 6-2z"/></svg>
    Explore
</a>
<a href="{{ route('traveler.resorts.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('traveler.resorts.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
    Resorts
</a>
<a href="{{ route('traveler.packages.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('traveler.packages.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
    Tours
</a>
<a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
    Guides
</a>
<a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg>
    AI Planner
</a>
<a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 20s-7-4.4-9.4-8.8C1 8 2.4 5 5.6 5c1.8 0 3.2 1 4.4 2.6C11.2 6 12.6 5 14.4 5c3.2 0 4.6 3 3 6.2C19 15.6 12 20 12 20z"/></svg>
    Wishlist
</a>
<a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
    My Bookings
</a>
<a href="{{ route('explore') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M21 15a2 2 0 01-2 2H8l-5 4V6a2 2 0 012-2h14a2 2 0 012 2v9z"/></svg>
    Messages
</a>
<a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
    Profile
</a>

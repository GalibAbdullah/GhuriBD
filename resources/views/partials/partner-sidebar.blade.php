<a href="{{ route('partner.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.dashboard') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
    Dashboard
</a>
<a href="{{ route('partner.resorts.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.resorts.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 18v-7a2 2 0 012-2h14a2 2 0 012 2v7"/><path d="M3 18h18"/><path d="M7 11V7a2 2 0 012-2h6a2 2 0 012 2v4"/></svg>
    My Resorts
</a>
<a href="{{ route('partner.packages.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.packages.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M9 20l-6-3V4l6 3 6-3 6 3v13l-6-3-6 3z"/><path d="M9 4v13M15 7v13"/></svg>
    My Tour Packages
</a>
<a href="{{ route('guides.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('guides.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
    Guides
</a>
@if (auth()->user()->isVerifiedTourGuide())
    <a href="{{ route('partner.availability.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.availability.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
        Availability
    </a>
@endif
<a href="{{ route('partner.bookings.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.bookings.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M4 8a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2a2 2 0 000-4V8z"/></svg>
    Bookings
</a>
<a href="{{ route('partner.reviews.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.reviews.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg>
    Reviews
</a>
<a href="{{ route('messages.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('messages.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M21 15a2 2 0 01-2 2H8l-5 4V6a2 2 0 012-2h14a2 2 0 012 2v9z"/></svg>
    Messages
</a>
<a href="{{ route('complaints.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('complaints.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M5 21V4"/><path d="M5 4h13l-3 4 3 4H5"/></svg>
    Support
</a>
<a href="{{ route('partner.verifications.status') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded {{ request()->routeIs('partner.verifications.*') ? 'active bg-primary-subtle text-primary-emphasis fw-semibold' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
    Verification
</a>

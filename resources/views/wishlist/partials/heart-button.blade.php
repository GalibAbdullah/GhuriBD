@php
    $isTraveler = auth()->user()?->hasRole(\App\Enums\UserRole::TRAVELER->value) ?? false;
@endphp

@if ($isTraveler)
    <form method="POST" action="{{ $type === 'resort' ? route('wishlist.resorts.toggle', $model) : route('wishlist.packages.toggle', $model) }}" class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
        @csrf
        <button
            type="submit"
            class="btn btn-sm btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0"
            style="width: 32px; height: 32px;"
            aria-label="{{ $wishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
            title="{{ $wishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
        >
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="#dc3545" stroke-width="1.8" fill="{{ $wishlisted ? '#dc3545' : 'none' }}">
                <path d="M12 20s-7-4.4-9.4-8.8C1 8 2.4 5 5.6 5c1.8 0 3.2 1 4.4 2.6C11.2 6 12.6 5 14.4 5c3.2 0 4.6 3 3 6.2C19 15.6 12 20 12 20z"/>
            </svg>
        </button>
    </form>
@endif

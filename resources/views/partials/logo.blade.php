<a href="{{ $href ?? route('home') }}" class="d-flex align-items-center gap-2 px-2 py-1 text-decoration-none" aria-label="GhuriBD home">
    <span class="logo-3d position-relative d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
        {{-- Kite flying along a curved path --}}
        <svg viewBox="0 0 40 40" fill="none" class="logo-kite position-absolute top-0 start-0 w-100 h-100" aria-hidden="true">
            <defs>
                <linearGradient id="kiteGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#22c55e"/>
                    <stop offset="100%" stop-color="#0ea5e9"/>
                </linearGradient>
                {{-- The flight path the kite follows (dashed) --}}
                <path id="kiteFlightPath" d="M4 32 C 10 6, 28 4, 36 14" fill="none"/>
            </defs>

            {{-- Dotted flight path display --}}
            <path d="M4 32 C 10 6, 28 4, 36 14" fill="none" stroke="rgba(15,107,92,0.25)" stroke-width="1" stroke-dasharray="2 3" class="logo-kite-path"/>

            {{-- Kite that travels along the path --}}
            <g>
                <animateMotion dur="6s" repeatCount="indefinite" rotate="auto">
                    <mpath href="#kiteFlightPath"/>
                </animateMotion>

                {{-- 3D side extrusion (shadow/depth behind kite) --}}
                <path d="M20 4 L30 20 L20 36 L10 20 Z" fill="rgba(2,44,34,0.45)" transform="translate(1.5 2)" class="logo-kite-depth"/>

                {{-- Kite front face --}}
                <path d="M20 4 L30 20 L20 36 L10 20 Z" fill="url(#kiteGrad)" stroke="rgba(255,255,255,0.45)" stroke-width="0.6" class="logo-kite-body"/>

                {{-- Kite cross spars --}}
                <path d="M20 4 L20 36" stroke="rgba(255,255,255,0.55)" stroke-width="0.5" class="logo-kite-body"/>
                <path d="M10 20 L30 20" stroke="rgba(255,255,255,0.55)" stroke-width="0.5" class="logo-kite-body"/>

                {{-- Kite tail --}}
                <path d="M20 36 C 18 40, 22 42, 20 45" stroke="rgba(232,163,61,0.75)" stroke-width="1" fill="none" stroke-linecap="round" class="logo-kite-tail"/>
                <path d="M20 40 C 18 42, 22 44, 20 46" stroke="rgba(232,163,61,0.5)" stroke-width="0.8" fill="none" stroke-linecap="round" class="logo-kite-tail"/>
            </g>
        </svg>
        {{-- Kite soft shadow --}}
        <span class="logo-kite-shadow position-absolute top-100 start-50 translate-middle rounded-pill bg-black bg-opacity-25" style="width: 24px; height: 6px; margin-top: -4px; filter: blur(2px);"></span>
    </span>
    <span class="fw-bold text-dark" style="font-size: 17px;">Ghuri<span class="text-primary">BD</span></span>
</a>
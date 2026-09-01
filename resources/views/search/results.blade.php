@extends('layouts.search')

@section('title', $filters['q'] ? 'Search: '.$filters['q'] : 'Search Results')

@section('content')
    <div class="mb-3">
        <a href="{{ route('search.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; New Search</a>
    </div>

    <form id="searchForm" method="GET" action="{{ route('search.results') }}">
        <input type="hidden" name="tab" value="{{ $filters['tab'] }}">

        <!-- Top bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="input-group" style="max-width: 360px;">
                <span class="input-group-text bg-white">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                </span>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Where do you want to travel?">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-secondary">{{ $totalResults }} {{ Str::plural('result', $totalResults) }}</span>
                <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('searchForm').submit()">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: High to Low</option>
                    <option value="alpha" @selected($filters['sort'] === 'alpha')>Alphabetical</option>
                </select>
                <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                    Filters
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $filters['tab'] === 'all' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'all', 'page' => null]) }}">
                    All ({{ $totalResults }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $filters['tab'] === 'resorts' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'resorts', 'page' => null]) }}">
                    Resorts ({{ $resortsTotal }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $filters['tab'] === 'packages' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'packages', 'page' => null]) }}">
                    Tour Packages ({{ $packagesTotal }})
                </a>
            </li>
        </ul>

        <div class="row g-4">
            <!-- Filter sidebar (static at lg+, slide-in offcanvas below lg) -->
            <div class="col-lg-3">
                <div class="offcanvas-lg offcanvas-start bg-white" tabindex="-1" id="filterOffcanvas" style="width: 300px;">
                    <div class="offcanvas-header d-lg-none">
                        <h5 class="offcanvas-title">Filters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filterOffcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body d-block">
                        <div class="card border-lg-0">
                            <div class="card-body">
                                @include('search.partials.filters')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="col-lg-9">
                @if ($filters['tab'] === 'resorts')
                    @if ($resorts->isEmpty())
                        @include('search.partials.empty-state')
                    @else
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
                            @foreach ($resorts as $resort)
                                @include('search.partials.resort-card', ['resort' => $resort])
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $resorts->links() }}</div>
                    @endif
                @elseif ($filters['tab'] === 'packages')
                    @if ($packages->isEmpty())
                        @include('search.partials.empty-state')
                    @else
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
                            @foreach ($packages as $package)
                                @include('search.partials.package-card', ['package' => $package])
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $packages->links() }}</div>
                    @endif
                @else
                    @if ($combined->isEmpty())
                        @include('search.partials.empty-state')
                    @else
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
                            @foreach ($combined as $item)
                                @if ($item['type'] === 'resort')
                                    @include('search.partials.resort-card', ['resort' => $item['model']])
                                @else
                                    @include('search.partials.package-card', ['package' => $item['model']])
                                @endif
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $combined->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </form>
@endsection

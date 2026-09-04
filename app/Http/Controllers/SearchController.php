<?php

namespace App\Http\Controllers;

use App\Enums\ResortAmenity;
use App\Enums\ResortStatus;
use App\Enums\TourPackageStatus;
use App\Enums\UserRole;
use App\Models\Resort;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SearchController extends Controller
{
    private const PER_PAGE = 12;

    /**
     * How many rows (per type) are pulled into memory to build the merged
     * "All" tab. Keeps the unified, cross-model sort/paginate cheap without
     * needing a SQL UNION across two differently-shaped tables.
     */
    private const MERGE_POOL_LIMIT = 60;

    private const POPULAR_DESTINATIONS = [
        "Cox's Bazar", 'Sajek Valley', 'Bandarban', 'Sylhet', 'Saint Martin', 'Kuakata',
    ];

    /**
     * The search landing page: hero search bar, popular destinations, and
     * (for a signed-in Traveler) their recent searches.
     */
    public function index(Request $request): View
    {
        return view('search.index', [
            'popularDestinations' => self::POPULAR_DESTINATIONS,
            'recentSearches' => $this->recentSearches($request),
        ]);
    }

    /**
     * Run the unified search across resorts and tour packages and render the
     * results page for the requested tab, filters, and sort order.
     */
    public function results(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $this->rememberSearch($request, $filters['q']);

        $resortsQuery = $this->baseResortQuery($filters);
        $packagesQuery = $this->basePackageQuery($filters);

        $resortsTotal = (clone $resortsQuery)->count();
        $packagesTotal = (clone $packagesQuery)->count();

        $resorts = null;
        $packages = null;
        $combined = null;

        match ($filters['tab']) {
            'resorts' => $resorts = $this->sortResorts($resortsQuery, $filters['sort'])
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'packages' => $packages = $this->sortPackages($packagesQuery, $filters['sort'])
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            default => $combined = $this->combinedResults(
                $resortsQuery,
                $packagesQuery,
                $filters['sort'],
                max((int) $request->integer('page', 1), 1),
            ),
        };

        $totalResults = match ($filters['tab']) {
            'resorts' => $resortsTotal,
            'packages' => $packagesTotal,
            default => $resortsTotal + $packagesTotal,
        };

        return view('search.results', [
            'filters' => $filters,
            'resorts' => $resorts,
            'packages' => $packages,
            'combined' => $combined,
            'resortsTotal' => $resortsTotal,
            'packagesTotal' => $packagesTotal,
            'totalResults' => $totalResults,
            'popularDestinations' => self::POPULAR_DESTINATIONS,
            'divisions' => array_keys(config('bangladesh.divisions')),
            'districtsByDivision' => config('bangladesh.divisions'),
            'amenities' => ResortAmenity::values(),
        ]);
    }

    /**
     * Validate and normalize every search/filter query parameter. Anything
     * missing falls back to a safe default rather than a validation error,
     * since a search page should degrade gracefully on a stray/odd URL.
     *
     * @return array{q: ?string, tab: string, division: ?string, district: ?string, destination: ?string, min_price: ?float, max_price: ?float, amenities: array<int, string>, duration: ?string, max_travelers: ?int, sort: string}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', 'string', Rule::in(['all', 'resorts', 'packages'])],
            'division' => ['nullable', 'string', Rule::in(array_keys(config('bangladesh.divisions')))],
            'district' => ['nullable', 'string', 'max:50'],
            'destination' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', Rule::in(ResortAmenity::values())],
            'duration' => ['nullable', 'string', Rule::in(['1-3', '4-7', '8+'])],
            'max_travelers' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'price_asc', 'price_desc', 'alpha'])],
        ]);

        return [
            'q' => $validated['q'] ?? null,
            'tab' => $validated['tab'] ?? 'all',
            'division' => $validated['division'] ?? null,
            'district' => $validated['district'] ?? null,
            'destination' => $validated['destination'] ?? null,
            'min_price' => isset($validated['min_price']) ? (float) $validated['min_price'] : null,
            'max_price' => isset($validated['max_price']) ? (float) $validated['max_price'] : null,
            'amenities' => $validated['amenities'] ?? [],
            'duration' => $validated['duration'] ?? null,
            'max_travelers' => $validated['max_travelers'] ?? null,
            'sort' => $validated['sort'] ?? 'newest',
        ];
    }

    /**
     * Active resorts matching the keyword search and shared/resort filters.
     * Selects only the columns the result cards need and eager-loads the
     * cheapest room price (for the price filter/sort) in one query.
     */
    private function baseResortQuery(array $filters): Builder
    {
        return Resort::query()
            ->select(['id', 'user_id', 'name', 'division', 'district', 'address', 'latitude', 'longitude', 'price_range', 'amenities', 'cover_image', 'status', 'created_at'])
            ->where('status', ResortStatus::ACTIVE->value)
            ->when($filters['q'], fn (Builder $query, string $q) => $query->searchKeyword($q))
            ->applyFilters($filters)
            ->withMin('rooms', 'price_per_night');
    }

    /**
     * Active tour packages matching the keyword search and shared/package filters.
     */
    private function basePackageQuery(array $filters): Builder
    {
        return TourPackage::query()
            ->select(['id', 'user_id', 'title', 'destination', 'division', 'district', 'latitude', 'longitude', 'duration_days', 'duration_nights', 'price', 'max_travelers', 'cover_image', 'status', 'created_at'])
            ->where('status', TourPackageStatus::ACTIVE->value)
            ->when($filters['q'], fn (Builder $query, string $q) => $query->searchKeyword($q))
            ->applyFilters($filters);
    }

    private function sortResorts(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderByRaw('rooms_min_price_per_night IS NULL')->orderBy('rooms_min_price_per_night'),
            'price_desc' => $query->orderByRaw('rooms_min_price_per_night IS NULL')->orderByDesc('rooms_min_price_per_night'),
            'alpha' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }

    private function sortPackages(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'alpha' => $query->orderBy('title'),
            default => $query->latest(),
        };
    }

    /**
     * Build the merged "All" tab: pull a bounded pool of rows from each
     * model, tag them with a common shape, sort the pool in memory, then
     * slice out the requested page into a real paginator.
     */
    private function combinedResults(Builder $resortsQuery, Builder $packagesQuery, string $sort, int $page): LengthAwarePaginator
    {
        $resorts = (clone $resortsQuery)->limit(self::MERGE_POOL_LIMIT)->get()->map(fn (Resort $resort): array => [
            'type' => 'resort',
            'model' => $resort,
            'price' => $resort->rooms_min_price_per_night !== null ? (float) $resort->rooms_min_price_per_night : null,
            'label' => $resort->name,
            'created_at' => $resort->created_at,
        ]);

        $packages = (clone $packagesQuery)->limit(self::MERGE_POOL_LIMIT)->get()->map(fn (TourPackage $package): array => [
            'type' => 'package',
            'model' => $package,
            'price' => (float) $package->price,
            'label' => $package->title,
            'created_at' => $package->created_at,
        ]);

        /** @var Collection<int, array{type: string, model: Resort|TourPackage, price: ?float, label: string, created_at: \Illuminate\Support\Carbon}> $items */
        $items = match ($sort) {
            'price_asc' => $resorts->concat($packages)->sortBy(fn (array $item) => $item['price'] ?? INF),
            'price_desc' => $resorts->concat($packages)->sortByDesc(fn (array $item) => $item['price'] ?? -INF),
            'alpha' => $resorts->concat($packages)->sortBy(fn (array $item) => mb_strtolower($item['label'])),
            default => $resorts->concat($packages)->sortByDesc(fn (array $item) => $item['created_at']),
        };

        $items = $items->values();

        $slice = $items->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            self::PER_PAGE,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    /**
     * Recent search keywords for the signed-in Traveler, most recent first.
     * Guests and non-Travelers never see or write recent searches.
     *
     * @return array<int, string>
     */
    private function recentSearches(Request $request): array
    {
        if (! $request->user()?->hasRole(UserRole::TRAVELER->value)) {
            return [];
        }

        return $request->session()->get('recent_searches', []);
    }

    /**
     * Push a keyword onto the Traveler's recent-searches list in the session,
     * de-duplicating case-insensitively and keeping only the 5 most recent.
     */
    private function rememberSearch(Request $request, ?string $keyword): void
    {
        if (! $keyword || ! $request->user()?->hasRole(UserRole::TRAVELER->value)) {
            return;
        }

        $recent = collect($request->session()->get('recent_searches', []))
            ->reject(fn (string $term) => mb_strtolower($term) === mb_strtolower($keyword))
            ->prepend($keyword)
            ->take(5)
            ->values()
            ->all();

        $request->session()->put('recent_searches', $recent);
    }
}

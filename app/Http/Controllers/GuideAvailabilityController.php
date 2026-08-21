<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityStatus;
use App\Http\Requests\BulkGuideAvailabilityRequest;
use App\Http\Requests\GuideAvailabilityRequest;
use App\Models\GuideAvailability;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GuideAvailabilityController extends Controller
{
    private const SCOPES = ['upcoming', 'past', 'all'];

    /**
     * The guide's availability calendar, filtered by time scope and status.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', GuideAvailability::class);

        $guide = $request->user();

        // Whitelisted rather than trusted — these reach the query builder.
        $scope = in_array($request->query('scope'), self::SCOPES, true)
            ? $request->query('scope')
            : 'upcoming';

        $status = in_array($request->query('status'), AvailabilityStatus::values(), true)
            ? $request->query('status')
            : null;

        $slots = GuideAvailability::query()
            ->forGuide($guide)
            ->when($scope === 'upcoming', fn ($query) => $query->upcoming())
            ->when($scope === 'past', fn ($query) => $query->past())
            ->when($status, fn ($query) => $query->withStatus($status))
            ->orderBy('available_date', $scope === 'past' ? 'desc' : 'asc')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('availability.index', [
            'slots' => $slots,
            'scope' => $scope,
            'status' => $status,
            'statuses' => AvailabilityStatus::cases(),
            'summary' => $this->summaryFor($guide),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', GuideAvailability::class);

        return view('availability.create', [
            'statuses' => AvailabilityStatus::guideAssignable(),
        ]);
    }

    public function store(GuideAvailabilityRequest $request): RedirectResponse
    {
        Gate::authorize('create', GuideAvailability::class);

        try {
            $request->user()->guideAvailabilities()->create($request->validated());
        } catch (UniqueConstraintViolationException) {
            // Lost a race against a concurrent submit of the same slot.
            return back()
                ->withInput()
                ->withErrors(['start_time' => 'A slot starting at that time already exists for this date.']);
        }

        return redirect()
            ->route('partner.availability.index')
            ->with('status', 'Availability slot added.');
    }

    public function edit(GuideAvailability $availability): View
    {
        Gate::authorize('update', $availability);

        return view('availability.edit', [
            'slot' => $availability,
            'statuses' => AvailabilityStatus::guideAssignable(),
        ]);
    }

    public function update(GuideAvailabilityRequest $request, GuideAvailability $availability): RedirectResponse
    {
        Gate::authorize('update', $availability);

        try {
            $availability->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['start_time' => 'A slot starting at that time already exists for this date.']);
        }

        return redirect()
            ->route('partner.availability.index')
            ->with('status', 'Availability slot updated.');
    }

    public function destroy(GuideAvailability $availability): RedirectResponse
    {
        Gate::authorize('delete', $availability);

        $availability->delete();

        return redirect()
            ->route('partner.availability.index')
            ->with('status', 'Availability slot removed.');
    }

    /**
     * Flip a slot between Available and Blocked without opening the full form.
     */
    public function toggle(GuideAvailability $availability): RedirectResponse
    {
        Gate::authorize('update', $availability);

        $next = $availability->status === AvailabilityStatus::AVAILABLE
            ? AvailabilityStatus::BLOCKED
            : AvailabilityStatus::AVAILABLE;

        $availability->update(['status' => $next->value]);

        return back()->with('status', $next->isBlocked()
            ? 'Slot blocked. Travelers can no longer book it.'
            : 'Slot reopened for booking.');
    }

    public function bulkCreate(): View
    {
        Gate::authorize('create', GuideAvailability::class);

        return view('availability.bulk', [
            'statuses' => AvailabilityStatus::guideAssignable(),
            'maxRangeDays' => config('ghuribd.availability.max_bulk_range_days'),
        ]);
    }

    /**
     * Publish one slot per matching date. Dates that would clash with existing
     * slots are skipped and reported rather than failing the whole batch —
     * a guide topping up a half-filled month should not have to hunt for the
     * one date that already had a slot.
     */
    public function bulkStore(BulkGuideAvailabilityRequest $request): RedirectResponse
    {
        Gate::authorize('create', GuideAvailability::class);

        $guide = $request->user();
        $data = $request->validated();
        $candidates = $request->matchingDates();

        // Compared as cast Carbon dates rather than via whereIn: the column
        // persists a midnight time component, so raw string matching misses.
        $taken = GuideAvailability::query()
            ->forGuide($guide)
            ->whereDate('available_date', '>=', $candidates[0])
            ->whereDate('available_date', '<=', end($candidates))
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->get()
            ->map(fn (GuideAvailability $slot): string => $slot->available_date->toDateString())
            ->all();

        $dates = array_values(array_diff($candidates, $taken));

        if ($dates === []) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'Every date in that range already has an overlapping slot.']);
        }

        DB::transaction(function () use ($guide, $dates, $data): void {
            foreach ($dates as $date) {
                $guide->guideAvailabilities()->create([
                    'available_date' => $date,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'capacity' => $data['capacity'],
                    'price' => $data['price'],
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? null,
                ]);
            }
        });

        $created = count($dates);
        $skipped = count($candidates) - $created;

        $message = "Published {$created} availability slot(s).";

        if ($skipped > 0) {
            $message .= " Skipped {$skipped} date(s) that already had an overlapping slot.";
        }

        return redirect()
            ->route('partner.availability.index')
            ->with('status', $message);
    }

    /**
     * @return array<string, int>
     */
    private function summaryFor(User $guide): array
    {
        $upcoming = GuideAvailability::query()->forGuide($guide)->upcoming();

        return [
            'upcoming' => (clone $upcoming)->count(),
            'available' => (clone $upcoming)->withStatus(AvailabilityStatus::AVAILABLE->value)->count(),
            'blocked' => (clone $upcoming)->withStatus(AvailabilityStatus::BLOCKED->value)->count(),
            'booked' => (clone $upcoming)->where('booked_count', '>', 0)->count(),
        ];
    }
}

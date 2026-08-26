<?php

namespace App\Planning;

use App\Enums\ProviderType;
use App\Enums\VerificationStatus;
use App\Models\GuideAvailability;
use App\Models\TourPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Deterministic, budget- and interest-aware itinerary generator. No external
 * API, no per-request cost — swapping in a real LLM later means writing a new
 * TourPlanner implementation and rebinding it, not touching this feature's
 * controllers, models, or tests.
 *
 * Guides are not tagged by interest anywhere in the platform yet, so a day's
 * "theme" narrates the traveler's chosen interests rather than filtering
 * which guide is suggested for it — suggestions are matched purely on real,
 * affordable, unused availability. Claiming a theme-match the data can't back
 * would be worse than not claiming one.
 */
class RuleBasedTourPlanner implements TourPlanner
{
    public function name(): string
    {
        return 'rule_based';
    }

    public function generate(TourPlan $plan): array
    {
        $dailyBudget = $this->dailyBudget($plan);
        $themes = $plan->interestLabels();
        $candidates = $this->candidateSlots($dailyBudget);
        $used = [];

        $days = [];

        for ($dayNumber = 1; $dayNumber <= $plan->duration_days; $dayNumber++) {
            $theme = $themes[($dayNumber - 1) % count($themes)];
            $targetDate = $plan->start_date?->copy()->addDays($dayNumber - 1);

            $slot = $this->pickSlot($candidates, $used, $targetDate);

            if ($slot !== null) {
                $used[] = $slot->id;
            }

            $days[] = new PlannedDay(
                dayNumber: $dayNumber,
                title: "Day {$dayNumber} · {$theme}",
                theme: $theme,
                budgetAllocated: $dailyBudget,
                description: $this->describe($plan, $theme, $dailyBudget, $slot),
                suggestedAvailabilityId: $slot?->id,
            );
        }

        return $days;
    }

    private function dailyBudget(TourPlan $plan): string
    {
        $reservePercent = (int) config('ghuribd.tour_planner.logistics_reserve_percent', 20);
        $reserve = bcmul((string) $plan->budget, (string) ($reservePercent / 100), 2);
        $activityBudget = bcsub((string) $plan->budget, $reserve, 2);

        return bcdiv($activityBudget, (string) $plan->duration_days, 2);
    }

    /**
     * All upcoming, bookable, affordable slots from verified Tour Guides,
     * ordered chronologically — the pool every day draws from.
     */
    private function candidateSlots(string $dailyBudget): Collection
    {
        return GuideAvailability::query()
            ->bookable()
            ->where('price', '<=', $dailyBudget)
            ->whereHas('guide.providerVerifications', function ($query): void {
                $query->where('status', VerificationStatus::APPROVED->value)
                    ->where('provider_type', ProviderType::TOUR_GUIDE->value);
            })
            ->with('guide')
            ->orderBy('available_date')
            ->orderBy('start_time')
            ->get();
    }

    private function pickSlot(Collection $candidates, array $used, ?Carbon $targetDate): ?GuideAvailability
    {
        $available = $candidates->whereNotIn('id', $used);

        if ($targetDate !== null) {
            return $available->first(fn (GuideAvailability $slot) => $slot->available_date->isSameDay($targetDate));
        }

        return $available->first();
    }

    private function describe(TourPlan $plan, string $theme, string $dailyBudget, ?GuideAvailability $slot): string
    {
        $symbol = config('ghuribd.currency.symbol', '৳');

        if ($slot === null) {
            return "A {$theme} day in {$plan->destination}. No guides are currently listed and affordable ".
                "(within {$symbol}{$dailyBudget}) for this day — check back later or browse guides directly.";
        }

        return "A {$theme} day in {$plan->destination}. Suggested: book {$slot->guide->name}'s tour on ".
            "{$slot->available_date->format('D, M j')} ({$slot->time_range}) for {$symbol}".
            number_format((float) $slot->price, 2)." — fits your {$symbol}{$dailyBudget} day budget.";
    }
}

<?php

namespace App\Planning;

use App\Models\TourPlan;

interface TourPlanner
{
    /**
     * Machine name, e.g. "rule_based" or "claude".
     */
    public function name(): string;

    /**
     * @return PlannedDay[] exactly $plan->days entries, ordered by day number.
     */
    public function generate(TourPlan $plan): array;
}

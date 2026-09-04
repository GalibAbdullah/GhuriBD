<?php

namespace App\Planning;

/**
 * One day of a generated itinerary, before it is persisted as a TourPlanDay.
 */
final class PlannedDay
{
    public function __construct(
        public readonly int $dayNumber,
        public readonly string $title,
        public readonly string $theme,
        public readonly string $budgetAllocated,
        public readonly string $description,
        public readonly ?int $suggestedAvailabilityId,
    ) {}
}

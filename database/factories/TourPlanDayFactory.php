<?php

namespace Database\Factories;

use App\Enums\Interest;
use App\Models\TourPlan;
use App\Models\TourPlanDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourPlanDay>
 */
class TourPlanDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_plan_id' => TourPlan::factory(),
            'day_number' => 1,
            'title' => 'Day 1 · '.Interest::NATURE->value,
            'theme' => Interest::NATURE->value,
            'budget_allocated' => 2000,
            'description' => fake()->sentence(),
            'suggested_availability_id' => null,
        ];
    }
}

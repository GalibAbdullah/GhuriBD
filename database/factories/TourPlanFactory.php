<?php

namespace Database\Factories;

use App\Enums\Interest;
use App\Models\TourPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourPlan>
 */
class TourPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'traveler_id' => User::factory(),
            'destination' => fake()->city(),
            'start_date' => null,
            'duration_days' => fake()->numberBetween(1, 5),
            'budget' => fake()->numberBetween(5000, 50000),
            'interests' => [Interest::NATURE->value],
        ];
    }
}

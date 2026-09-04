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
            'destination' => fake()->randomElement(['Cox\'s Bazar', 'Sylhet', 'Bandarban', 'Sundarbans']),
            'start_date' => now()->addWeek()->toDateString(),
            'duration_days' => fake()->numberBetween(2, 5),
            'budget' => fake()->numberBetween(10000, 100000),
            'interests' => fake()->randomElements(Interest::values(), 2),
            'regenerated_at' => null,
        ];
    }
}

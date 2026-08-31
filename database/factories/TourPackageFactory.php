<?php

namespace Database\Factories;

use App\Enums\TourPackageService;
use App\Enums\TourPackageStatus;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourPackage>
 */
class TourPackageFactory extends Factory
{
    public function definition(): array
    {
        $division = fake()->randomElement(array_keys(config('bangladesh.divisions')));
        $district = fake()->randomElement(config('bangladesh.divisions')[$division]);

        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true).' Tour',
            'destination' => $district,
            'division' => $division,
            'district' => $district,
            'description' => fake()->paragraph(),
            'duration_days' => fake()->numberBetween(1, 10),
            'duration_nights' => fake()->numberBetween(0, 9),
            'price' => fake()->numberBetween(2000, 50000),
            'max_travelers' => fake()->numberBetween(1, 30),
            'meeting_point' => fake()->address(),
            'start_location' => fake()->city(),
            'itinerary' => fake()->paragraphs(3, true),
            'included_services' => fake()->randomElements(TourPackageService::values(), 3),
            'excluded_services' => fake()->randomElements(TourPackageService::values(), 2),
            'cover_image' => 'tour-packages/cover/'.fake()->uuid().'.jpg',
            'status' => TourPackageStatus::ACTIVE->value,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => TourPackageStatus::ACTIVE->value]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => TourPackageStatus::INACTIVE->value]);
    }
}

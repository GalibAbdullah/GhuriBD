<?php

namespace Database\Factories;

use App\Enums\ResortAmenity;
use App\Enums\ResortStatus;
use App\Models\Resort;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resort>
 */
class ResortFactory extends Factory
{
    public function definition(): array
    {
        $division = fake()->randomElement(array_keys(config('bangladesh.divisions')));
        $district = fake()->randomElement(config('bangladesh.divisions')[$division]);

        return [
            'user_id' => User::factory(),
            'name' => fake()->company().' Resort',
            'description' => fake()->paragraph(),
            'division' => $division,
            'district' => $district,
            'address' => fake()->address(),
            'contact_phone' => fake()->numerify('01#########'),
            'price_range' => '৳'.fake()->numberBetween(2000, 5000).' - ৳'.fake()->numberBetween(6000, 15000),
            'amenities' => fake()->randomElements(ResortAmenity::values(), 3),
            'cover_image' => 'resorts/cover/'.fake()->uuid().'.jpg',
            'status' => ResortStatus::ACTIVE->value,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => ResortStatus::ACTIVE->value]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => ResortStatus::INACTIVE->value]);
    }

    /**
     * A resort with a pinned map location.
     */
    public function withCoordinates(): static
    {
        return $this->state([
            'latitude' => fake()->latitude(20.5, 26.6),
            'longitude' => fake()->longitude(88.0, 92.7),
        ]);
    }
}

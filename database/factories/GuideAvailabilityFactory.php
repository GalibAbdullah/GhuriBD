<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Models\GuideAvailability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuideAvailability>
 */
class GuideAvailabilityFactory extends Factory
{
    public function definition(): array
    {
        // Distinct hours keep generated slots from colliding with the unique
        // (user, date, start_time) index when several are made for one date.
        $startHour = fake()->numberBetween(6, 18);

        return [
            'user_id' => User::factory(),
            'available_date' => GuideAvailability::today()->addDays(fake()->numberBetween(1, 60))->toDateString(),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + 2),
            'capacity' => fake()->numberBetween(1, 10),
            'booked_count' => 0,
            'price' => fake()->numberBetween(1000, 15000),
            'status' => AvailabilityStatus::AVAILABLE->value,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function onDate(string $date): static
    {
        return $this->state(['available_date' => $date]);
    }

    public function between(string $startTime, string $endTime): static
    {
        return $this->state([
            'start_time' => GuideAvailability::normalizeTime($startTime),
            'end_time' => GuideAvailability::normalizeTime($endTime),
        ]);
    }

    public function blocked(): static
    {
        return $this->state(['status' => AvailabilityStatus::BLOCKED->value]);
    }

    public function booked(int $count = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'booked_count' => $count,
            'capacity' => max($count, $attributes['capacity'] ?? $count),
            'status' => AvailabilityStatus::BOOKED->value,
        ]);
    }

    /**
     * A slot whose date has already passed. Bypasses the forward-only rules the
     * forms enforce, so tests can assert that past slots are read-only.
     */
    public function past(): static
    {
        return $this->state([
            'available_date' => GuideAvailability::today()->subDays(fake()->numberBetween(1, 30))->toDateString(),
        ]);
    }
}

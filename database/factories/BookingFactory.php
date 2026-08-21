<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $partySize = fake()->numberBetween(1, 3);
        $unitPrice = fake()->numberBetween(1000, 15000);

        return [
            'traveler_id' => User::factory(),
            'bookable_type' => GuideAvailability::class,
            'bookable_id' => GuideAvailability::factory(),
            'party_size' => $partySize,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $partySize,
            'status' => BookingStatus::PENDING_PAYMENT->value,
        ];
    }

    public function forSlot(GuideAvailability $availability, int $partySize = 1): static
    {
        return $this->state([
            'bookable_type' => GuideAvailability::class,
            'bookable_id' => $availability->id,
            'party_size' => $partySize,
            'unit_price' => $availability->price,
            'total_price' => bcmul((string) $availability->price, (string) $partySize, 2),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => BookingStatus::CONFIRMED->value]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => BookingStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);
    }
}

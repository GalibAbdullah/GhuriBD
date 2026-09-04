<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Room;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tour_package_id' => TourPackage::factory(),
            'booking_type' => BookingType::PACKAGE->value,
            'travel_date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'guests' => fake()->numberBetween(1, 4),
            'total_amount' => fake()->numberBetween(2000, 50000),
            'booking_status' => BookingStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'special_request' => null,
        ];
    }

    /**
     * A Resort booking: a room within a resort, plus a date range. The room is
     * created with capacity for one concurrent booking by default.
     */
    public function resort(): static
    {
        return $this->state(function (): array {
            $resort = Resort::factory()->create();
            $room = Room::factory()->create([
                'resort_id' => $resort->id,
                'available_rooms' => 1,
            ]);

            return [
                'tour_package_id' => null,
                'resort_id' => $resort->id,
                'room_id' => $room->id,
                'booking_type' => BookingType::RESORT->value,
                'travel_date' => null,
                'check_in_date' => now()->addWeek()->toDateString(),
                'check_out_date' => now()->addWeek()->addDays(2)->toDateString(),
            ];
        });
    }

    public function paid(): static
    {
        return $this->state([
            'booking_status' => BookingStatus::CONFIRMED->value,
            'payment_status' => PaymentStatus::PAID->value,
        ]);
    }
}

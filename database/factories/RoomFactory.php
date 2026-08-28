<?php

namespace Database\Factories;

use App\Enums\RoomAmenity;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Resort;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        $totalRooms = fake()->numberBetween(2, 10);

        return [
            'resort_id' => Resort::factory(),
            'room_name' => fake()->words(2, true).' Room',
            'room_type' => fake()->randomElement(RoomType::values()),
            'description' => fake()->paragraph(),
            'price_per_night' => fake()->numberBetween(1500, 15000),
            'capacity' => fake()->numberBetween(1, 6),
            'total_rooms' => $totalRooms,
            'available_rooms' => fake()->numberBetween(0, $totalRooms),
            'bed_type' => fake()->randomElement(['Single', 'Double', 'Queen', 'King', 'Twin']),
            'room_size' => fake()->numberBetween(150, 600).' sq ft',
            'amenities' => fake()->randomElements(RoomAmenity::values(), 3),
            'cover_image' => 'rooms/cover/'.fake()->uuid().'.jpg',
            'status' => RoomStatus::AVAILABLE->value,
        ];
    }

    public function available(): static
    {
        return $this->state(['status' => RoomStatus::AVAILABLE->value]);
    }

    public function unavailable(): static
    {
        return $this->state(['status' => RoomStatus::UNAVAILABLE->value]);
    }
}

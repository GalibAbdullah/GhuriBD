<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomImage>
 */
class RoomImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'image_path' => 'rooms/gallery/'.fake()->uuid().'.jpg',
        ];
    }
}

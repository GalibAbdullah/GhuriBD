<?php

namespace Database\Factories;

use App\Models\Resort;
use App\Models\ResortImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResortImage>
 */
class ResortImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'resort_id' => Resort::factory(),
            'image_path' => 'resorts/gallery/'.fake()->uuid().'.jpg',
        ];
    }
}

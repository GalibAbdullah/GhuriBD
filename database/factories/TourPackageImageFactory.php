<?php

namespace Database\Factories;

use App\Models\TourPackage;
use App\Models\TourPackageImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourPackageImage>
 */
class TourPackageImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_package_id' => TourPackage::factory(),
            'image_path' => 'tour-packages/gallery/'.fake()->uuid().'.jpg',
        ];
    }
}

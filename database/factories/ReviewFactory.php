<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'booking_id' => Booking::factory()->completed(),
            'resort_id' => null,
            'tour_package_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'review_text' => fake()->paragraph(),
            'partner_reply' => null,
        ];
    }

    /**
     * A review always belongs to the resort or tour package of its booking —
     * copy those over once the booking exists.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Review $review): void {
            $review->resort_id ??= $review->booking?->resort_id;
            $review->tour_package_id ??= $review->booking?->tour_package_id;
        });
    }
}

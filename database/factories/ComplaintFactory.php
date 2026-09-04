<?php

namespace Database\Factories;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'booking_id' => null,
            'category' => ComplaintCategory::OTHER->value,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => ComplaintStatus::OPEN->value,
            'admin_response' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => ComplaintStatus::RESOLVED->value,
            'admin_response' => fake()->sentence(),
            'resolved_by' => User::factory(),
            'resolved_at' => now(),
        ]);
    }
}

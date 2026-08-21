<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'gateway' => 'mock',
            'gateway_reference' => 'MOCK-'.strtoupper(Str::random(12)),
            'amount' => fake()->numberBetween(1000, 15000),
            'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ];
    }

    public function succeeded(): static
    {
        return $this->state(['status' => PaymentStatus::SUCCEEDED->value, 'paid_at' => now()]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => PaymentStatus::FAILED->value,
            'failure_reason' => 'The traveler declined the payment in the mock checkout.',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(['status' => PaymentStatus::REFUNDED->value, 'paid_at' => now()]);
    }
}

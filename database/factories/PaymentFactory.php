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
            'amount' => fake()->numberBetween(2000, 50000),
            'currency' => 'BDT',
            'status' => PaymentStatus::PENDING->value,
            'failure_reason' => null,
            'gateway_payload' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => PaymentStatus::FAILED->value,
            'failure_reason' => 'The payment was declined in the mock checkout.',
        ]);
    }
}

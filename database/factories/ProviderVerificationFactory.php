<?php

namespace Database\Factories;

use App\Enums\ProviderType;
use App\Enums\VerificationStatus;
use App\Models\ProviderVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderVerification>
 */
class ProviderVerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_name' => fake()->company(),
            'provider_type' => fake()->randomElement(ProviderType::values()),
            'business_address' => fake()->address(),
            'phone' => fake()->numerify('01#########'),
            'verification_document' => 'verification-documents/'.fake()->uuid().'.pdf',
            'additional_information' => fake()->optional()->sentence(),
            'status' => VerificationStatus::PENDING->value,
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => VerificationStatus::PENDING->value,
        ]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => VerificationStatus::APPROVED->value,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => VerificationStatus::REJECTED->value,
            'rejection_reason' => 'The submitted document could not be verified.',
            'reviewed_at' => now(),
        ]);
    }
}
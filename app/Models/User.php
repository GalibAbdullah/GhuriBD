<?php

namespace App\Models;

use App\Enums\ProviderType;
use App\Enums\VerificationStatus;
use App\Support\StorageImage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'gender', 'date_of_birth', 'address', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the absolute URL for the user's profile photo.
     * Falls back to the default avatar when no photo is set.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => StorageImage::url($this->profile_photo, 'images/default-avatar.svg'),
        );
    }

    /**
     * All provider verification requests submitted by this user.
     */
    public function providerVerifications(): HasMany
    {
        return $this->hasMany(ProviderVerification::class);
    }

    /**
     * True when this Travel Partner has an approved verification request.
     */
    public function isVerifiedProvider(): bool
    {
        return $this->providerVerifications()
            ->where('status', VerificationStatus::APPROVED->value)
            ->exists();
    }

    /**
     * All resort listings owned by this Travel Partner.
     */
    public function resorts(): HasMany
    {
        return $this->hasMany(Resort::class);
    }

    /**
     * True when this user holds an approved verification for the given provider
     * type. A partner approved as a Resort Owner is verified, but is not a guide.
     */
    public function isVerifiedProviderOfType(ProviderType $type): bool
    {
        return $this->providerVerifications()
            ->where('status', VerificationStatus::APPROVED->value)
            ->where('provider_type', $type->value)
            ->exists();
    }

    public function isVerifiedTourGuide(): bool
    {
        return $this->isVerifiedProviderOfType(ProviderType::TOUR_GUIDE);
    }

    /**
     * Availability slots published by this guide.
     */
    public function guideAvailabilities(): HasMany
    {
        return $this->hasMany(GuideAvailability::class);
    }
}

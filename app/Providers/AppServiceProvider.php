<?php

namespace App\Providers;

use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\Resort;
use App\Models\Room;
use App\Policies\GuideAvailabilityPolicy;
use App\Policies\ProviderVerificationPolicy;
use App\Policies\ResortPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(ProviderVerification::class, ProviderVerificationPolicy::class);
        Gate::policy(Resort::class, ResortPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(GuideAvailability::class, GuideAvailabilityPolicy::class);
    }
}

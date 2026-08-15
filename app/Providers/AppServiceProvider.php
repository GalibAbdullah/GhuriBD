<?php

namespace App\Providers;

use App\Models\ProviderVerification;
use App\Policies\ProviderVerificationPolicy;
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
    }
}

<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\TourPlan;
use App\Payments\MockPaymentGateway;
use App\Payments\PaymentGateway;
use App\Planning\RuleBasedTourPlanner;
use App\Planning\TourPlanner;
use App\Policies\BookingPolicy;
use App\Policies\GuideAvailabilityPolicy;
use App\Policies\ProviderVerificationPolicy;
use App\Policies\TourPlanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, match (config('ghuribd.payment.gateway', 'mock')) {
            default => MockPaymentGateway::class,
        });

        $this->app->bind(TourPlanner::class, match (config('ghuribd.tour_planner.engine', 'rule_based')) {
            default => RuleBasedTourPlanner::class,
        });
    }

    public function boot(): void
    {
        Gate::policy(ProviderVerification::class, ProviderVerificationPolicy::class);
        Gate::policy(GuideAvailability::class, GuideAvailabilityPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(TourPlan::class, TourPlanPolicy::class);
    }
}

<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Conversation;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\Payment;
use App\Models\Resort;
use App\Models\Room;
use App\Models\TourPackage;
use App\Models\TourPlan;
use App\Payments\MockPaymentGateway;
use App\Payments\PaymentGateway;
use App\Planning\RuleBasedTourPlanner;
use App\Planning\TourPlanner;
use App\Policies\BookingPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\GuideAvailabilityPolicy;
use App\Policies\ProviderVerificationPolicy;
use App\Policies\ResortPolicy;
use App\Policies\RoomPolicy;
use App\Policies\TourPackagePolicy;
use App\Policies\TourPlanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The mock gateway is the only implementation today; a real gateway
        // (e.g. SSLCommerz) is a new binding here, not a controller rewrite.
        $this->app->bind(PaymentGateway::class, MockPaymentGateway::class);

        // Likewise: today's itinerary engine is rule-based; a real LLM later
        // is a new binding here, not a rewrite of the planner feature.
        $this->app->bind(TourPlanner::class, RuleBasedTourPlanner::class);
    }

    public function boot(): void
    {
        Gate::policy(ProviderVerification::class, ProviderVerificationPolicy::class);
        Gate::policy(Resort::class, ResortPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(GuideAvailability::class, GuideAvailabilityPolicy::class);
        Gate::policy(TourPackage::class, TourPackagePolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(TourPlan::class, TourPlanPolicy::class);

        // Feeds the notification bell (unread count + preview list) in the
        // shared layout, wherever it's rendered.
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();

            $view->with([
                'unreadNotificationsCount' => $user?->unreadNotifications()->count() ?? 0,
                'recentNotifications' => $user
                    ? $user->notifications()->latest()->take(8)->get()
                    : collect(),
            ]);
        });
    }
}

<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\Payment;
use App\Models\Resort;
use App\Models\Review;
use App\Models\Room;
use App\Models\TourPackage;
use App\Models\Wishlist;
use App\Payments\MockPaymentGateway;
use App\Payments\PaymentGateway;
use App\Policies\BookingPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\GuideAvailabilityPolicy;
use App\Policies\ProviderVerificationPolicy;
use App\Policies\ResortPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\RoomPolicy;
use App\Policies\TourPackagePolicy;
use App\Policies\WishlistPolicy;
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
        Gate::policy(Wishlist::class, WishlistPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);

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

        // Feeds the wishlist heart icon on resort/package cards and detail
        // pages, wherever they're rendered — only Travelers can wishlist.
        View::composer([
            'search.partials.resort-card',
            'search.partials.package-card',
            'traveler.resorts.index',
            'traveler.tour-packages.index',
        ], function ($view): void {
            $user = auth()->user();
            $isTraveler = $user?->hasRole(UserRole::TRAVELER->value) ?? false;

            $view->with([
                'wishlistedResortIds' => $isTraveler ? $user->wishlists()->whereNotNull('resort_id')->pluck('resort_id') : collect(),
                'wishlistedPackageIds' => $isTraveler ? $user->wishlists()->whereNotNull('tour_package_id')->pluck('tour_package_id') : collect(),
            ]);
        });
    }
}

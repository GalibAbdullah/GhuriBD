<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\Resort;
use App\Models\Room;
use App\Models\TourPackage;
use App\Policies\BookingPolicy;
use App\Policies\GuideAvailabilityPolicy;
use App\Policies\ProviderVerificationPolicy;
use App\Policies\ResortPolicy;
use App\Policies\RoomPolicy;
use App\Policies\TourPackagePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        Gate::policy(TourPackage::class, TourPackagePolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);

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

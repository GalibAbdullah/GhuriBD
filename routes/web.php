<?php

use App\Enums\UserRole;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GuideAvailabilityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderVerificationController;
use App\Http\Controllers\ResortController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TourPackageController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Smart Destination Search — public, works for guests and any authenticated role.
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/results', [SearchController::class, 'results'])->name('search.results');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:3,1')->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Traveler dashboard
Route::middleware(['auth', 'traveler'])->group(function (): void {
    Route::view('/traveler', 'dashboard.traveler')->name('traveler.dashboard');

    // Browse resorts & rooms — read-only, active listings only
    Route::resource('traveler/resorts', ResortController::class)->only(['index', 'show'])->names('traveler.resorts');
    Route::resource('traveler/resorts.rooms', RoomController::class)->only(['index', 'show'])->names('traveler.resorts.rooms');

    // Browse tour packages — read-only, active listings only
    Route::resource('traveler/packages', TourPackageController::class)->only(['index', 'show'])->names('traveler.packages');

    // Booking System — a Traveler's own bookings, and the 3 checkout flows
    Route::get('traveler/bookings', [BookingController::class, 'index'])->name('traveler.bookings.index');
    Route::get('traveler/bookings/{booking}', [BookingController::class, 'show'])->name('traveler.bookings.show');
    Route::patch('traveler/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('traveler.bookings.cancel');

    Route::get('bookings/resorts/{resort}/rooms/{room}/create', [BookingController::class, 'createResort'])->name('bookings.resorts.create');
    Route::get('bookings/packages/{package}/create', [BookingController::class, 'createPackage'])->name('bookings.packages.create');
    Route::get('bookings/combined/create', [BookingController::class, 'createCombined'])->name('bookings.combined.create');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');

    // Secure Online Payment — mock gateway checkout for a pending booking.
    Route::post('bookings/{booking}/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}/callback', [PaymentController::class, 'callback'])->middleware('throttle:10,1')->name('payments.callback');

    // Wishlist — a Traveler can save/remove resorts and tour packages.
    Route::get('traveler/wishlist', [WishlistController::class, 'index'])->name('traveler.wishlist.index');
    Route::post('wishlist/resorts/{resort}/toggle', [WishlistController::class, 'toggleResort'])->name('wishlist.resorts.toggle');
    Route::post('wishlist/packages/{package}/toggle', [WishlistController::class, 'togglePackage'])->name('wishlist.packages.toggle');

    // Reviews & Ratings — a Traveler may review a Completed booking once.
    Route::get('traveler/bookings/{booking}/review', [ReviewController::class, 'create'])->name('traveler.reviews.create');
    Route::post('traveler/bookings/{booking}/review', [ReviewController::class, 'store'])->name('traveler.reviews.store');
});

// Travel Partner dashboard
Route::middleware(['auth', 'partner'])->group(function (): void {
    Route::view('/partner', 'dashboard.partner')->name('partner.dashboard');

    // Provider Verification — Travel Partner can submit and view their status
    Route::get('/partner/verifications', [ProviderVerificationController::class, 'status'])->name('partner.verifications.status');
    Route::get('/partner/verifications/create', [ProviderVerificationController::class, 'create'])->name('partner.verifications.create');
    Route::post('/partner/verifications', [ProviderVerificationController::class, 'store'])->name('partner.verifications.store');
    Route::get('/partner/verifications/{verification}', [ProviderVerificationController::class, 'show'])->name('partner.verifications.show');

    // Guide Availability — verified Tour Guides publish and manage bookable slots.
    Route::get('/partner/availability', [GuideAvailabilityController::class, 'index'])->name('partner.availability.index');
    Route::get('/partner/availability/create', [GuideAvailabilityController::class, 'create'])->name('partner.availability.create');
    Route::get('/partner/availability/bulk', [GuideAvailabilityController::class, 'bulkCreate'])->name('partner.availability.bulk');
    Route::get('/partner/availability/{availability}/edit', [GuideAvailabilityController::class, 'edit'])->name('partner.availability.edit');

    Route::middleware('throttle:30,1')->group(function (): void {
        Route::post('/partner/availability', [GuideAvailabilityController::class, 'store'])->name('partner.availability.store');
        Route::post('/partner/availability/bulk', [GuideAvailabilityController::class, 'bulkStore'])->name('partner.availability.bulk.store');
        Route::put('/partner/availability/{availability}', [GuideAvailabilityController::class, 'update'])->name('partner.availability.update');
        Route::patch('/partner/availability/{availability}/toggle', [GuideAvailabilityController::class, 'toggle'])->name('partner.availability.toggle');
        Route::delete('/partner/availability/{availability}', [GuideAvailabilityController::class, 'destroy'])->name('partner.availability.destroy');
    });

    // Resort Management — Travel Partner must additionally be an approved provider
    Route::middleware('verified.partner')->group(function (): void {
        Route::resource('partner/resorts', ResortController::class)->names('partner.resorts');

        // Room Management — rooms are nested under the resort they belong to
        Route::resource('partner/resorts.rooms', RoomController::class)->names('partner.resorts.rooms');

        // Tour Package Management — Travel Partner must additionally be an approved provider
        Route::resource('partner/packages', TourPackageController::class)->names('partner.packages');
    });

    // Booking System — read-only view of bookings for the Partner's own resorts/packages
    Route::get('partner/bookings', [BookingController::class, 'index'])->name('partner.bookings.index');
    Route::get('partner/bookings/{booking}', [BookingController::class, 'show'])->name('partner.bookings.show');
    Route::patch('partner/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('partner.bookings.complete');

    // Reviews & Ratings — Travel Partner views and replies to reviews on
    // their own resorts/packages.
    Route::get('partner/reviews', [ReviewController::class, 'partnerIndex'])->name('partner.reviews.index');
    Route::patch('partner/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('partner.reviews.reply');
});

// Admin dashboard
Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/admin', AdminDashboardController::class)->name('admin.dashboard');

    // Provider Verification — Admin can view and review all requests
    Route::get('/admin/verifications', [ProviderVerificationController::class, 'index'])->name('admin.verifications.index');
    Route::get('/admin/verifications/{verification}', [ProviderVerificationController::class, 'show'])->name('admin.verifications.show');
    Route::put('/admin/verifications/{verification}', [ProviderVerificationController::class, 'review'])->name('admin.verifications.review');

    // Resort Management — Admin has read-only access
    Route::resource('admin/resorts', ResortController::class)->only(['index', 'show'])->names('admin.resorts');

    // Room Management — Admin has read-only access
    Route::resource('admin/resorts.rooms', RoomController::class)->only(['index', 'show'])->names('admin.resorts.rooms');

    // Tour Package Management — Admin has read-only access
    Route::resource('admin/packages', TourPackageController::class)->only(['index', 'show'])->names('admin.packages');

    // Booking System — Admin can view every booking
    Route::get('admin/bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('admin/bookings/{booking}', [BookingController::class, 'show'])->name('admin.bookings.show');

    // Reviews & Ratings — Admin can view and delete inappropriate reviews.
    Route::get('admin/reviews', [ReviewController::class, 'adminIndex'])->name('admin.reviews.index');
    Route::delete('admin/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
});

// User profile management
Route::middleware(['auth'])->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Notifications — the literal "read-all" path must be registered before the
// {notification} wildcard route below, or PUT /notifications/read-all would
// be swallowed by PUT /notifications/{notification}.
Route::middleware(['auth'])->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'redirectTo'])->name('notifications.redirect');
    Route::put('/notifications/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

// Generic dashboard entry point — redirects each authenticated user to their role dashboard.
Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();

    foreach (UserRole::cases() as $role) {
        if ($user->hasRole($role->value)) {
            return redirect()->route($role->routeName());
        }
    }

    return redirect()->route('profile.show');
})->name('dashboard');

// Placeholder routes for all prototype features (empty states for now)
Route::middleware(['auth'])->group(function (): void {
    Route::get('/explore', fn () => view('empty.feature', ['title' => 'Explore', 'message' => 'Search resorts, tour packages, and guides will live here.']))->name('explore');
});

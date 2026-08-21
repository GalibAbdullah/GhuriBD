<?php

use App\Enums\UserRole;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\GuideAvailabilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderVerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

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
});

// Admin dashboard
Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/admin', AdminDashboardController::class)->name('admin.dashboard');

    // Provider Verification — Admin can view and review all requests
    Route::get('/admin/verifications', [ProviderVerificationController::class, 'index'])->name('admin.verifications.index');
    Route::get('/admin/verifications/{verification}', [ProviderVerificationController::class, 'show'])->name('admin.verifications.show');
    Route::put('/admin/verifications/{verification}', [ProviderVerificationController::class, 'review'])->name('admin.verifications.review');
});

// User profile management
Route::middleware(['auth'])->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
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

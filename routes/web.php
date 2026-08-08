<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
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
});

// Admin dashboard
Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::view('/admin', 'dashboard.admin')->name('admin.dashboard');
});

// Placeholder routes for all prototype features (empty states for now)
Route::middleware(['auth'])->group(function (): void {
    Route::view('/profile', 'profile.show')->name('profile.show');
    Route::get('/explore', fn () => view('empty.feature', ['title' => 'Explore', 'message' => 'Search resorts, tour packages, and guides will live here.']))->name('traveler.explore');
});
<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'traveler'])->group(function (): void {
    Route::view('/traveler', 'rbac.role-access', [
        'role' => UserRole::TRAVELER->value,
        'message' => 'Traveler access is working.',
    ])->name('traveler');
});

Route::middleware(['auth', 'partner'])->group(function (): void {
    Route::view('/partner', 'rbac.role-access', [
        'role' => UserRole::TRAVEL_PARTNER->value,
        'message' => 'Travel Partner access is working.',
    ])->name('partner');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::view('/admin', 'rbac.role-access', [
        'role' => UserRole::ADMIN->value,
        'message' => 'Admin access is working.',
    ])->name('admin');
});

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Redirect unused auth routes to login (manajemen kredensial terpusat di SSO Kampus)
    Route::get('forgot-password', fn() => redirect()->route('login'))->name('password.request');
    Route::post('forgot-password', fn() => redirect()->route('login'))->name('password.email');
    Route::get('reset-password/{token}', fn() => redirect()->route('login'))->name('password.reset');
    Route::post('reset-password', fn() => redirect()->route('login'))->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', fn() => redirect()->route('dashboard'))->name('verification.notice');
    Route::get('confirm-password', fn() => redirect()->route('dashboard'))->name('password.confirm');

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

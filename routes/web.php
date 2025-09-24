<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Login route
Route::get('/login', function () {
    return view('login');
});

// Dashboard route (protected)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData']);
});

// Google OAuth routes
Route::prefix('auth')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
});

// Logout route
Route::post('/logout', [GoogleAuthController::class, 'logout'])->middleware('auth');

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
    Route::get('/dashboard/refresh-stats', [DashboardController::class, 'refreshStats']);
    Route::get('/dashboard/refresh-tasks', [DashboardController::class, 'refreshTasks']);
    Route::get('/dashboard/refresh-notifications', [DashboardController::class, 'refreshNotifications']);
    Route::post('/dashboard/mark-task-completed', [DashboardController::class, 'markTaskCompleted']);
    Route::post('/dashboard/mark-notification-read', [DashboardController::class, 'markNotificationRead']);
    Route::post('/dashboard/mark-all-notifications-read', [DashboardController::class, 'markAllNotificationsRead']);
    Route::post('/dashboard/update-notification-setting', [DashboardController::class, 'updateNotificationSetting']);
});

// Google OAuth routes
Route::prefix('auth')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
});

// Logout route
Route::post('/logout', [GoogleAuthController::class, 'logout'])->middleware('auth');

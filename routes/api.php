<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateCustomToken;

// Google OAuth API routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::get('/user', [GoogleAuthController::class, 'getUserByToken']);
    
    // Protected routes (require session authentication)
    Route::middleware('web')->group(function () {
        Route::get('/token', [GoogleAuthController::class, 'getToken']);
        Route::post('/logout', [GoogleAuthController::class, 'logout']);
    });
});

// Protected API routes using custom token
Route::middleware(ValidateCustomToken::class)->group(function () {
    Route::get('/user-profile', function (Request $request) {
        return response()->json([
            'user' => $request->input('authenticated_user')
        ]);
    });
});

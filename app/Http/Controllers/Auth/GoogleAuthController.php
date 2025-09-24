<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('google_id', $googleUser->id)
                       ->orWhere('email', $googleUser->email)
                       ->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'google_id' => $googleUser->id,
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'password' => Hash::make(Str::random(24)), // Random password for security
                    'email_verified_at' => now(),
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Generate a simple token (you can replace this with Sanctum later)
            $token = base64_encode($user->id . '|' . Str::random(40));
            
            // Store token in session or return it
            session(['api_token' => $token]);

            // Redirect to frontend with success
            return redirect()->to(config('app.frontend_url', 'http://localhost:3000') . '/auth/success?token=' . $token);

        } catch (\Exception $e) {
            return redirect()->to(config('app.frontend_url', 'http://localhost:3000') . '/auth/error?message=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Get the authenticated user's token (API endpoint).
     */
    public function getToken(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        
        // Generate a simple token
        $token = base64_encode($user->id . '|' . Str::random(40));
        
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_id' => $user->google_id,
            ]
        ]);
    }

    /**
     * Get user information by token (API endpoint).
     */
    public function getUserByToken(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('token');
        
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            // Decode the simple token
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);
            
            if (count($parts) !== 2) {
                return response()->json(['error' => 'Invalid token format'], 401);
            }

            $userId = $parts[0];
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->google_id,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Successfully logged out']);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth provider callback
     */
    public function callback(string $provider, Request $request)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Unable to login using ' . ucfirst($provider) . '. Please try again.');
        }

        // Find or create user
        $user = $this->findOrCreateUser($socialUser, $provider);

        // Check if account is active
        if (isset($user->status) && $user->status !== 'active') {
            return redirect('/login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Log the user in
        Auth::login($user, true); // true = remember me

        // Regenerate session
        $request->session()->regenerate();

        // Create API token
        $tokenName = 'auth-token-' . $request->ip() . '-' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;
        session(['sanctum_token' => $token]);

        // Log successful login
        $this->logLoginActivity($request, $user, 'success', $provider);

        // Redirect using LoginResponse for role-based routing
        return app(\App\Http\Responses\LoginResponse::class)->toResponse($request);
    }

    /**
     * Find or create user from social login
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        // Try to find existing user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update user's OAuth info if needed
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'username' => $this->generateUsername($socialUser),
            'password' => Hash::make(Str::random(32)), // Random password
            'email_verified_at' => now(), // Auto-verify email from OAuth
            'profile_image' => $this->downloadProfileImage($socialUser),
            'status' => 'active',
            'role' => 'employee', // Default role
        ]);
    }

    /**
     * Generate unique username from social user data
     */
    protected function generateUsername($socialUser)
    {
        $baseUsername = $socialUser->getNickname()
            ?? Str::slug($socialUser->getName())
            ?? Str::before($socialUser->getEmail(), '@');

        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Download and save profile image from OAuth provider
     */
    protected function downloadProfileImage($socialUser)
    {
        if (!$socialUser->getAvatar()) {
            return null;
        }

        try {
            $avatarUrl = $socialUser->getAvatar();
            $filename = Str::random(40) . '.jpg';
            $path = storage_path('app/public/profile-photos/' . $filename);

            // Ensure directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // Download and save image
            $imageContent = file_get_contents($avatarUrl);
            file_put_contents($path, $imageContent);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Log login activity
     */
    protected function logLoginActivity($request, $user, $status, $provider)
    {
        $userAgent = $request->userAgent();

        LoginActivity::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $this->getDeviceType($userAgent),
            'browser' => $this->getBrowser($userAgent),
            'platform' => $this->getPlatform($userAgent),
            'failure_reason' => $status === 'success' ? "OAuth: {$provider}" : null,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Validate OAuth provider
     */
    protected function validateProvider(string $provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            abort(404);
        }
    }

    /**
     * Helper methods for device detection
     */
    protected function getDeviceType($userAgent)
    {
        if (preg_match('/mobile/i', $userAgent)) return 'mobile';
        if (preg_match('/tablet/i', $userAgent)) return 'tablet';
        return 'desktop';
    }

    protected function getBrowser($userAgent)
    {
        if (preg_match('/firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/safari/i', $userAgent)) return 'Safari';
        if (preg_match('/edge/i', $userAgent)) return 'Edge';
        return 'Unknown';
    }

    protected function getPlatform($userAgent)
    {
        if (preg_match('/windows/i', $userAgent)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'MacOS';
        if (preg_match('/linux/i', $userAgent)) return 'Linux';
        if (preg_match('/android/i', $userAgent)) return 'Android';
        if (preg_match('/ios|iphone|ipad/i', $userAgent)) return 'iOS';
        return 'Unknown';
    }
}

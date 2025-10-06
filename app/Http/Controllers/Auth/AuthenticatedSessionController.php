<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
     public function store(LoginRequest $request)
    {
        // IMPORTANT: Clear any existing authentication before new login
        Auth::logout();
        $request->session()->flush();

        // Validate credentials
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        // Check if user exists and account is active
        if ($user && isset($user->status) && $user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Please contact support.'),
            ]);
        }

        // Attempt authentication
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            // Log failed attempt
            $this->logLoginActivity($request, null, 'failed', 'Invalid credentials');

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        // Create or update API token for this session (keep single token per device)
        $tokenName = 'auth-token-' . $request->ip() . '-' . now()->timestamp;
        $token = $request->user()->createToken($tokenName)->plainTextToken;

        // Store token in session for SPA usage
        session(['sanctum_token' => $token]);

        // Log successful login
        $this->logLoginActivity($request, $request->user(), 'success');

        // Use LoginResponse for role-based redirect
        return app(\App\Http\Responses\LoginResponse::class)->toResponse($request);
    }

    /**
     * Log login activity
     */
    protected function logLoginActivity($request, $user, $status, $failureReason = null)
    {
        $userAgent = $request->userAgent();

        \App\Models\LoginActivity::create([
            'user_id' => $user?->id,
            'email' => $request->input('email'),
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $this->getDeviceType($userAgent),
            'browser' => $this->getBrowser($userAgent),
            'platform' => $this->getPlatform($userAgent),
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Detect device type from user agent
     */
    protected function getDeviceType($userAgent)
    {
        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Detect browser from user agent
     */
    protected function getBrowser($userAgent)
    {
        if (preg_match('/firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/safari/i', $userAgent)) return 'Safari';
        if (preg_match('/edge/i', $userAgent)) return 'Edge';
        if (preg_match('/opera/i', $userAgent)) return 'Opera';
        return 'Unknown';
    }

    /**
     * Detect platform from user agent
     */
    protected function getPlatform($userAgent)
    {
        if (preg_match('/windows/i', $userAgent)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'MacOS';
        if (preg_match('/linux/i', $userAgent)) return 'Linux';
        if (preg_match('/android/i', $userAgent)) return 'Android';
        if (preg_match('/ios|iphone|ipad/i', $userAgent)) return 'iOS';
        return 'Unknown';
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // Delete ALL tokens for the authenticated user
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        // Log out from the web session
        Auth::guard('web')->logout();

        // Flush all session data completely
        $request->session()->flush();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Clear all authentication guards
        Auth::logout();

        // Redirect to login page
        return redirect('/login');
    }
}

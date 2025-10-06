<?php
// app/Http/Responses/LoginResponse.php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        // Role hierarchy priority
        if ($user->hasRole('developer')) {
            $redirectTo = '/developer/dashboard';
        } elseif ($user->hasRole('superadmin')) {
            $redirectTo = '/superadmin/dashboard';
        } elseif ($user->hasRole('admin')) {
            $redirectTo = '/admin/dashboard';
        } elseif ($user->hasRole('employee')) {
            $redirectTo = '/employee/dashboard';
        } else {
            $redirectTo = '/dashboard'; // fallback
        }

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => $redirectTo], 200)
            : redirect()->intended($redirectTo);
    }
}


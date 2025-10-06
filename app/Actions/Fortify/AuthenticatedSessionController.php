<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyController;
use Laravel\Fortify\Contracts\LoginResponse;

class AuthenticatedSessionController extends FortifyController implements LoginResponse
{
    /**
     * Redirect after login
     */
    public function toResponse($request)
    {
        return redirect()->intended('/dashboard');
    }
}


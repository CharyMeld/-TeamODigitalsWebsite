<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse; 


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Fortify Login Response
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share authenticated user data + roles globally to Inertia
        Inertia::share([
            'auth' => function () {
                $user = auth()->user();

                return [
                    'user' => $user ? $user->only(['id', 'name', 'email']) : null,
                    'roles' => $user ? $user->getRoleNames() : [], // safer
                    'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                ];
            },
            
            'errors' => function () {
                return Session::get('errors') 
                    ? Session::get('errors')->getBag('default')->getMessages() 
                    : (object) [];
            },
            'flash' => function () {
                return [
                    'message' => Session::get('message')
                ];
            }
        ]);
    }
}




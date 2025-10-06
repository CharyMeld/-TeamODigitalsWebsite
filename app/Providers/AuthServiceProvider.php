<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Developer Dashboard Access
        Gate::define('accessDeveloperDashboard', function ($user) {
            return $user->hasRole('developer');
        });

        // Superadmin Dashboard Access
        Gate::define('accessSuperadminDashboard', function ($user) {
            return $user->hasRole(['superadmin', 'developer']);
        });

        // Admin Dashboard Access
        Gate::define('accessAdminDashboard', function ($user) {
            return $user->hasRole(['admin', 'superadmin', 'developer']);
        });

        // Employee Dashboard Access
        Gate::define('accessEmployeeDashboard', function ($user) {
            return $user->hasRole(['employee', 'admin', 'superadmin', 'developer']);
        });
    }
}

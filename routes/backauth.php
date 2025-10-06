<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
// 🔑 Login Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

// 🔐 Social Login Routes (OAuth)
Route::middleware('guest')->group(function () {
    Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Auth\SocialLoginController::class, 'redirect'])
        ->name('social.redirect');
    Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialLoginController::class, 'callback'])
        ->name('social.callback');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Role-based Dashboards)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // 🚪 Logout Route
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    
    // 👨‍💻 Developer Dashboard
    Route::get('/developer/dashboard', function () {
        return Inertia::render('Developer/Dashboard');
    })->name('developer.dashboard');
    
    // 🦸‍♂️ SuperAdmin Dashboard
    Route::get('/superadmin/dashboard', function () {
        return Inertia::render('SuperAdmin/Dashboard');
    })->name('superadmin.dashboard');
    
    // 👨‍💼 Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');
    
    // 👷‍♂️ Employee Dashboard
    Route::get('/employee/dashboard', function () {
        return Inertia::render('Employee/Dashboard');
    })->name('employee.dashboard');
    
    // Fallback dashboard for authenticated users
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

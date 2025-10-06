<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\Api\AnalyticsController;

use Spatie\Activitylog\Models\Activity;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Using 'auth:web' for session-based authentication
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('api.login');

// Services - Disabled (ServiceController does not exist)
// Route::get('/services', [ServiceController::class, 'getServices'])->name('api.services');

// Blog
Route::prefix('blog')->name('api.blog.')->group(function () {
    Route::post('/{id}/like', [BlogController::class, 'like'])->name('like');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes - Using 'auth:web' for session authentication
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {

    // Current user
    Route::get('/user', fn (Request $request) => response()->json($request->user()))->name('api.user');

    // Dashboard (General)
    Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/data', [DashboardController::class, 'data'])->name('data');
    });

    // Developer
    Route::middleware('check.role:developer')->prefix('developer')->name('api.developer.')->group(function () {
        Route::get('/dashboard', [DeveloperController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DeveloperController::class, 'getStats'])->name('stats');
        Route::get('/logs', [DeveloperController::class, 'systemLogs'])->name('logs');
        Route::get('/database', [DeveloperController::class, 'databaseManagement'])->name('database');
        Route::get('/activity-logs', function () {
            $logs = Activity::latest()->take(50)->get()->map(fn ($log) => [
                'id' => $log->id,
                'description' => $log->description,
                'user' => $log->causer?->name ?? 'System',
                'time' => $log->created_at->toDateTimeString(),
                'properties' => $log->properties,
            ]);
            return response()->json($logs);
        })->name('activity-logs');
    });

    // Superadmin
    Route::middleware('check.role:superadmin')->prefix('superadmin')->name('api.superadmin.')->group(function () {
        Route::get('/dashboard', [SuperadminController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [SuperadminController::class, 'getStats'])->name('stats');
        Route::get('/recent-activities', [SuperadminController::class, 'getRecentActivities'])->name('recent-activities');
        Route::get('/system-health', fn () => response()->json([
            'status' => 'healthy',
            'uptime' => '99.9%',
            'last_backup' => now()->subHours(6)->toISOString(),
            'database_status' => 'connected',
            'cache_status' => 'operational',
        ]))->name('system-health');
    });

    // Admin
    Route::middleware('check.role:admin')->prefix('admin')->name('api.admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminController::class, 'getAdminStats'])->name('dashboard.stats');
        Route::get('/employees', [AdminController::class, 'getEmployees'])->name('employees');
        Route::get('/user-management', [AdminController::class, 'getUserManagement'])->name('user-management');
        Route::post('/tasks', [AdminController::class, 'storeTask'])->name('tasks.store');
        Route::post('/assign-tasks', [AdminController::class, 'storeTask'])->name('assign-tasks');
        Route::get('/recent-activities', [AdminController::class, 'getRecentActivities'])->name('recent-activities');
        Route::get('/users/search', [UserController::class, 'search'])->name('admin.users.search');
    });
    
     

    // Employee
    Route::middleware('check.role:employee')->prefix('employee')->name('api.employee.')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'index'])->name('dashboard');
        Route::get('/profile-info', [EmployeeController::class, 'getProfileInfo'])->name('profile-info');
        Route::get('/tasks', [EmployeeController::class, 'getTasks'])->name('tasks');
        Route::get('/my-tasks', [EmployeeController::class, 'getMyTasks'])->name('my-tasks');
    });

    // Management (multi-role)
    Route::middleware('check.role:developer,superadmin,admin')->prefix('management')->name('api.management.')->group(function () {
        Route::get('/system-reports', fn () => response()->json([
            'message' => 'System reports endpoint',
            'user_role' => auth()->user()->role,
        ]))->name('system-reports');

        Route::get('/user-stats', fn () => response()->json([
            'total_users' => \App\Models\User::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
            'recent_logins' => \App\Models\User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))
                ->count(),
        ]))->name('user-stats');
    });

    /*
    |--------------------------------------------------------------------------
    | Analytics Routes - Allow admin, superadmin, developer
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.role:admin,superadmin,developer')
        ->prefix('analytics')
        ->name('api.analytics.')
        ->group(function () {
            Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
            Route::post('/email-report', [AnalyticsController::class, 'emailReport'])->name('email-report');
            Route::post('/schedule-report', [AnalyticsController::class, 'scheduleReport'])->name('schedule-report');
            Route::get('/scheduled-reports', [AnalyticsController::class, 'getScheduledReports'])->name('scheduled-reports');
            Route::delete('/scheduled-reports/{id}', [AnalyticsController::class, 'deleteScheduledReport'])->name('delete-scheduled-report');
            Route::post('/custom-report', [AnalyticsController::class, 'generateCustomReport']);
            Route::post('/schedule-custom-report', [AnalyticsController::class, 'scheduleCustomReport']);

        });
});

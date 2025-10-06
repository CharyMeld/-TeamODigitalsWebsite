<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Api\JobVacancyController;

/*
|--------------------------------------------------------------------------
| Authentication Required Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Main Dashboard Route (Role-based redirect)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin-stats', [DashboardController::class, 'getAdminStats']);
    Route::get('/dashboard/profile-stats', [DashboardController::class, 'getProfileStats']);
    Route::get('/dashboard/all-users', [DashboardController::class, 'getAllUsers']);
    
    /*
    |--------------------------------------------------------------------------
    | Universal Profile Routes (All Authenticated Users)
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('index');
        Route::get('/edit', [ProfileController::class, 'editProfile'])->name('edit');
        Route::match(['PUT', 'POST'], '/', [UserController::class, 'updateProfile'])->name('update');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Developer Dashboard Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.role:developer')->prefix('developer')->name('developer.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DeveloperController::class, 'dashboard'])->name('dashboard');
        
        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
        });
        
        // Finance & Reports
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/reports', fn() => Inertia::render('Developer/FinanceReports'))->name('reports');
        });
        
        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/', [SettingsController::class, 'update'])->name('update');
        });
        
        // Access Control
        Route::prefix('access-control')->name('access.')->group(function () {
            Route::get('/', [AccessControlController::class, 'index'])->name('index');
            Route::post('/users/{user}/roles/sync', [AccessControlController::class, 'syncUserRoles'])->name('users.roles.sync');
            Route::post('/roles/{role}/permissions/sync', [AccessControlController::class, 'syncRolePermissions'])->name('roles.permissions.sync');
            Route::post('/permissions', [AccessControlController::class, 'storePermission'])->name('permissions.store');
        });
        
        // Menu Items
        Route::prefix('menu-items')->name('menu-items.')->group(function () {
            Route::get('/create-data', [MenuItemController::class, 'createData'])->name('create-data');
        });
        Route::resource('menu-items', MenuItemController::class)->names('developer.menu-items');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Superadmin Dashboard Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.role:superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [SuperadminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/stats', [SuperadminController::class, 'getStats'])->name('dashboard.stats');
        
        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
        });
        
        // Finance & Transactions
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/reports', fn() => Inertia::render('Superadmin/FinanceReports'))->name('reports');
            Route::get('/transactions', fn() => Inertia::render('Superadmin/Transactions'))->name('transactions');
        });
        
        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/', [SettingsController::class, 'update'])->name('update');
        });
        
        // Access Control
        Route::prefix('access-control')->name('access.')->group(function () {
            Route::get('/', [AccessControlController::class, 'index'])->name('index');
            Route::post('/users/{user}/roles/sync', [AccessControlController::class, 'syncUserRoles'])->name('users.roles.sync');
            Route::post('/roles/{role}/permissions/sync', [AccessControlController::class, 'syncRolePermissions'])->name('roles.permissions.sync');
            Route::post('/permissions', [AccessControlController::class, 'storePermission'])->name('permissions.store');
        });
        
        // Menu Items
        Route::get('/menu-items/create-data', [MenuItemController::class, 'createData'])->name('menu-items.create-data');
        Route::resource('menu-items', MenuItemController::class)->except(['destroy'])->names('menu-items');

        // Blog Management
        Route::resource('blogs', AdminBlogController::class)->names('blogs');

        // Job Vacancies Management
        Route::apiResource('job-vacancies', JobVacancyController::class)->names('job-vacancies');

        // Job Applications Management
        Route::get('job-applications', [\App\Http\Controllers\JobApplicationController::class, 'index'])->name('job-applications.index');
        Route::put('job-applications/{id}/status', [\App\Http\Controllers\JobApplicationController::class, 'updateStatus'])->name('job-applications.update-status');
        Route::delete('job-applications/{id}', [\App\Http\Controllers\JobApplicationController::class, 'destroy'])->name('job-applications.destroy');

        // Leave Requests Management
        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
            Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
            Route::get('/stats', [LeaveRequestController::class, 'stats'])->name('stats');
            Route::put('/{id}', [LeaveRequestController::class, 'update'])->name('update');
            Route::get('/download/{filename}', [LeaveRequestController::class, 'download'])->name('download');
            Route::get('/{id}', [LeaveRequestController::class, 'show'])->name('show');
        });
    });
    
       /*
    |--------------------------------------------------------------------------
    | Admin Dashboard Routes  
    |--------------------------------------------------------------------------
    */


    Route::middleware('check.role:admin')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AdminController::class, 'editProfile'])->name('show');
        Route::put('/', [AdminController::class, 'updateProfile'])->name('update');
    });

    // Attendance Management
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/employees', [AdminAttendanceController::class, 'getEmployeesAttendance'])->name('employees');
        Route::get('/summary', [AdminAttendanceController::class, 'getAttendanceSummary'])->name('summary');
        Route::get('/history', [AdminAttendanceController::class, 'getAttendanceHistory'])->name('history');
        Route::get('/my-attendance', [AdminAttendanceController::class, 'getMyAttendance'])->name('myattendance');
        Route::post('/sign-in', [AdminAttendanceController::class, 'signIn'])->name('signin');
        Route::post('/sign-out', [AdminAttendanceController::class, 'signOut'])->name('signout');
        Route::post('/start-break', [AdminAttendanceController::class, 'startBreak'])->name('startbreak');
        Route::post('/end-break', [AdminAttendanceController::class, 'endBreak'])->name('endbreak');
    });

 

    
        
        
    
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/search', [UserController::class, 'search'])->name('admin.users.search');
        Route::post('/tasks', [AdminController::class, 'storeTask'])->name('admin.user.tasks.store');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
    });

    //  FIX: Leave Requests Management
    Route::prefix('leave-requests')->name('leaveRequests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store'); // Admin can also submit leave
        Route::get('/stats', [LeaveRequestController::class, 'stats'])->name('stats');
        Route::put('/{id}', [LeaveRequestController::class, 'update'])->name('update');
        Route::get('/download/{filename}', [LeaveRequestController::class, 'download'])->name('download');
        Route::get('/{id}', [LeaveRequestController::class, 'show'])->name('show');
    });

    // Finance & Reports
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/reports', fn() => Inertia::render('Admin/FinanceReports'))->name('reports');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    // Menu Items
    Route::resource('menu-items', MenuItemController::class)->only(['index', 'show'])->names('menu-items');

    // Employees (AssignTaskTab)
    Route::get('/employees', [AdminController::class, 'getEmployees'])->name('employees');

    // Tasks (AssignTaskTab)
    Route::post('/tasks', [AdminController::class, 'storeTask'])->name('tasks.store');
    Route::get('/employees/tasks', [AdminController::class, 'getEmployeeTasks'])->name('employees.tasks');
    Route::get('/work-reports', [AdminController::class, 'getWorkReports']);
    Route::put('/work-reports/{id}/update', [AdminController::class, 'updateWorkReportDB'])->name('work-reports.update');

    // Blog Management
    Route::resource('blogs', AdminBlogController::class)->names('blogs');

  });
   
   
   
        
/*
|--------------------------------------------------------------------------
| Employee Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware('check.role:employee')
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
        
        // Profile Management
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'profile'])->name('show');
            Route::get('/edit', [ProfileController::class, 'editProfile'])->name('edit');
            Route::match(['PUT', 'POST'], '/', [UserController::class, 'updateProfile'])->name('update');
        });
        
        Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'profile'])->name('show');
        Route::get('/edit', [ProfileController::class, 'editProfile'])->name('edit');
        Route::match(['PUT', 'POST'], '/', [UserController::class, 'updateProfile'])->name('update');
    });

        
        // Attendance Actions
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::post('/sign-in', [AttendanceController::class, 'signIn'])->name('signin');
            Route::post('/start-break', [AttendanceController::class, 'startBreak'])->name('startbreak');
            Route::post('/end-break', [AttendanceController::class, 'endBreak'])->name('endbreak');
            Route::post('/sign-out', [AttendanceController::class, 'signOut'])->name('signout');
            Route::post('/recalculate-hours', [AttendanceController::class, 'recalculateHours'])->name('recalculate');
        });
        
        // Tasks & Work Management
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [EmployeeController::class, 'tasks'])->name('index');
            Route::post('/complete', [EmployeeController::class, 'completeTask'])->name('complete');
        });
        
        // Employee Leave Requests
        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'myRequests'])->name('my'); 
            Route::post('/submit', [LeaveRequestController::class, 'store'])->name('submit');
            Route::delete('/{id}', [LeaveRequestController::class, 'destroy'])->name('destroy');
            Route::get('/{id}', [LeaveRequestController::class, 'show'])->name('show');
        });
                
        // Time Tracking & Reports
        Route::get('/time-tracking', [EmployeeController::class, 'timeTracking'])->name('time-tracking');
        Route::get('/reports', [EmployeeController::class, 'reports'])->name('reports');
        Route::get('/schedule', [EmployeeController::class, 'schedule'])->name('schedule');
        
        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/my-tasks', [EmployeeController::class, 'getMyTasks'])->name('my-tasks');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Higher Role Access Routes (Developer & Superadmin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.role:developer,superadmin')->prefix('management')->name('management.')->group(function () {
        Route::get('/system-logs', fn() => Inertia::render('Management/SystemLogs'))->name('system-logs');
        Route::get('/backup', fn() => Inertia::render('Management/Backup'))->name('backup');
        
        // User Management Actions
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/export', [UserController::class, 'export'])->name('export');
            Route::post('/import', [UserController::class, 'import'])->name('import');
            Route::get('/audit-log', [UserController::class, 'auditLog'])->name('audit-log');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Reports Access Routes (Developer, Superadmin & Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('check.role:developer,superadmin,admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/user-activity', fn() => Inertia::render('Reports/UserActivity'))->name('user-activity');
        Route::get('/system-stats', fn() => Inertia::render('Reports/SystemStats'))->name('system-stats');
        Route::get('/user-statistics', [UserController::class, 'statistics'])->name('user-statistics');
    });
    
});


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/no-access', function () {
    return Inertia::render('Errors/NoAccess', [
        'message' => 'You do not have permission to access any dashboard. Please contact your administrator.'
    ]);
})->name('no-access');

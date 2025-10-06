<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;




class DeveloperController extends Controller
{
    /**
     * Show the developer dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        return Inertia::render('Developer/Dashboard', [
            'user' => $user,
            'role' => 'developer',
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'stats' => $this->getStats(),
            'recent_activities' => $this->getRecentActivities()
        ]);
    }

    /**
     * Developer stats.
     */
    protected function getStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_admins' => User::role('admin')->count(),
            'total_employees' => User::role('employee')->count(),
            'system_health' => 'excellent',
        ];
    }

    /**
     * Recent activities (stubbed).
     */
    protected function getRecentActivities(): array
    {
        return [
            'System backup completed successfully',
            'Database optimization finished',
            'Security audit passed',
            'Performance monitoring active',
        ];
    }

    /**
     * Show system logs (Developer only).
     */
   /**
 * Show system logs (Developer only).
 */
    public function systemLogs()
    {
        // Example: record a log (optional, remove if not needed here)
        activity()
            ->causedBy(auth()->user()) // who did it
            ->withProperties(['ip' => request()->ip()])
            ->log('Viewed system logs');

        // Fetch activity logs from database
        $logs = \Spatie\Activitylog\Models\Activity::latest()->take(50)->get();

        return Inertia::render('Developer/SystemLogs', [
            'logs' => $logs
        ]);
    }


    /**
     * Show database management (Developer only).
     */
    public function databaseManagement()
    {
        return Inertia::render('Developer/DatabaseManagement', [
            'tables' => [],      // TODO: Add your database info here
            'connections' => []  // TODO: Add DB connections
        ]);
    }
}


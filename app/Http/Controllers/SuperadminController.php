<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class SuperadminController extends Controller
{
    /**
     * Show the superadmin dashboard (Inertia).
     */
    public function dashboard()
    {
        return Inertia::render('Superadmin/Dashboard', [
            'stats'        => $this->getDashboardStats(),
            'charts'       => $this->getChartData(),
            'recentUsers'  => $this->getRecentUsers(),
            'activities'   => $this->getRecentActivities(), // include activities in Inertia too
        ]);
    }

    /**
     * API endpoint for Vue/AJAX requests.
     */
    public function getStats()
    {
        try {
            $stats = $this->getDashboardStats();
            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Error getting superadmin stats: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error'           => 'Failed to fetch stats',
                'message'         => $e->getMessage(),
                'totalUsers'      => 0,
                'totalRoles'      => 0,
                'totalMenuItems'  => 0,
                'systemStatus'    => 'Error',
                'activeAdmins'    => 0,
                'totalEmployees'  => 0,
                'pendingApprovals'=> 0,
                'recentUsers'     => [],
            ], 500);
        }
    }

    /**
     * Public endpoint for recent activities (API + Dashboard).
     */
    public function getRecentActivities()
    {
        // Example: mix of system logs and user activities
        return response()->json([
            'activities' => [
                [
                    'type' => 'user',
                    'message' => 'New admin user registered',
                    'time' => now()->subMinutes(30)->diffForHumans(),
                ],
                [
                    'type' => 'system',
                    'message' => 'Database backup completed',
                    'time' => now()->subHours(3)->diffForHumans(),
                ],
                [
                    'type' => 'user',
                    'message' => 'Employee profile updated',
                    'time' => now()->subHours(5)->diffForHumans(),
                ],
                [
                    'type' => 'system',
                    'message' => 'Cache cleared successfully',
                    'time' => now()->subDay()->diffForHumans(),
                ],
            ]
        ]);
    }

    /**
     * Core stats builder.
     */
    private function getDashboardStats(): array
    {
        try {
            return [
                'totalUsers'      => User::count(),
                'totalRoles'      => Role::count(),
                'totalMenuItems'  => MenuItem::count(),
                'systemStatus'    => 'Operational',
                'activeAdmins'    => User::where('role', 'admin')->count(),
                'totalEmployees'  => User::where('role', 'employee')->count(),
                'pendingApprovals'=> 0, // TODO: hook up real logic
                'recentUsers'     => $this->getRecentUsers(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getDashboardStats: ' . $e->getMessage());
            return [
                'totalUsers'      => User::count(),
                'totalRoles'      => 0,
                'totalMenuItems'  => 0,
                'systemStatus'    => 'Operational',
                'activeAdmins'    => User::where('role', 'admin')->count(),
                'totalEmployees'  => User::where('role', 'employee')->count(),
                'pendingApprovals'=> 0,
                'recentUsers'     => [],
            ];
        }
    }

    /**
     * Chart data for frontend.
     */
    private function getChartData(): array
    {
        return [
            'userGrowth'       => $this->getUserGrowthData(),
            'roleDistribution' => $this->getRoleDistributionData(),
        ];
    }

    /**
     * Get user registrations for last 12 months.
     */
    private function getUserGrowthData(): array
    {
        $months = [];
        $counts = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            $counts[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'labels' => $months,
            'data'   => $counts,
        ];
    }

    /**
     * Get user distribution by roles.
     */
    private function getRoleDistributionData(): array
    {
        $roles = Role::withCount('users')->get();

        return [
            'labels' => $roles->pluck('name')->toArray(),
            'data'   => $roles->pluck('users_count')->toArray(),
        ];
    }

    /**
     * Get recent users.
     */
    private function getRecentUsers()
    {
        return User::latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(function ($user) {
                return [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'roles'      => $user->getRoleNames(),
                    'created_at' => $user->created_at->diffForHumans(),
                ];
            });
    }
}


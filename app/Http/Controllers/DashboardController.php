<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Role;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Show the dashboard page with role-based redirect
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Redirect to the correct dashboard
        if ($user->hasRole('developer')) {
            return redirect()->route('developer.dashboard');
        }

        if ($user->hasRole('superadmin')) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('employee')) {
            return redirect()->route('employee.dashboard');
        }

        // If no roles match, log them out
        Auth::logout();
        return redirect()->route('login')->withErrors([
            'email' => 'No valid role assigned to your account. Please contact admin.',
        ]);
    }
    
    
    /**
     * Get dashboard data for role-specific dashboards - OPTIMIZED
     */
    
    public function getDashboardData($role = null)
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();
        
        // Cache menu items for 15 minutes since they don't change often
        $cacheKey = 'menu_items_' . implode('_', $userRoles);
        
        $menuTree = Cache::remember($cacheKey, 900, function () use ($userRoles) {
            // Get menu items with single query
            $menuItems = MenuItem::select('id', 'name', 'parent_id', 'sort_order', 'url', 'icon')
                ->where('is_active', 1)
                ->orderBy('sort_order', 'asc')
                ->get();

            // Filter menus by role permissions
            $filteredMenus = $menuItems->filter(function ($menu) use ($userRoles) {
                return $this->hasAccess($userRoles, $menu->name);
            });

            // Build nested menu tree
            return $this->buildMenuTree($filteredMenus, $userRoles);
        });

        return [
            'user' => $user->only(['id', 'name', 'email', 'role']), // Only return needed fields
            'roles' => $userRoles,
            'menuItems' => $menuTree,
        ];
    }

    /**
     * Get dashboard stats for API calls - OPTIMIZED
     */
    public function getStats($role)
    {
        // Cache stats for 5 minutes
        $cacheKey = "dashboard_stats_{$role}";
        
        $stats = Cache::remember($cacheKey, 300, function () use ($role) {
            switch ($role) {
                case 'superadmin':
                    // Use single query for better performance
                    $result = DB::selectOne("
                        SELECT 
                            (SELECT COUNT(*) FROM users) as total_users,
                            (SELECT COUNT(*) FROM roles) as total_roles,
                            (SELECT COUNT(*) FROM permissions) as total_permissions,
                            (SELECT COUNT(*) FROM users u JOIN model_has_roles mhr ON u.id = mhr.model_id WHERE mhr.role_id = (SELECT id FROM roles WHERE name = 'admin')) as active_admins
                    ");
                    
                    return [
                        'total_users' => (int)$result->total_users,
                        'total_roles' => (int)$result->total_roles,
                        'total_permissions' => (int)$result->total_permissions,
                        'active_admins' => (int)$result->active_admins,
                    ];
                    
                case 'developer':
                    return [
                        'total_users' => User::count(),
                        'system_health' => 'excellent',
                        'logs' => 125,
                        'alerts' => 3,
                    ];
                    
                default:
                    return [
                        'message' => 'No stats available for this role',
                    ];
            }
        });

        return response()->json($stats);
    }

    /**
     * Return JSON data for the frontend dashboard (legacy endpoint) - OPTIMIZED
     */
    public function data()
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();

        // Use cached menu data
        $cacheKey = 'dashboard_data_' . implode('_', $roles);
        
        $data = Cache::remember($cacheKey, 300, function () use ($roles, $user) {
            // Fetch menus dynamically from DB
            $menuItems = MenuItem::select('id', 'name', 'parent_id', 'sort_order', 'url', 'icon')
                ->where('is_active', 1)
                ->orderBy('sort_order', 'asc')
                ->get();

            // Filter menus by role permissions
            $menuItems = $menuItems->filter(function ($menu) use ($roles) {
                return $this->hasAccess($roles, $menu->name);
            });

            // Build nested menu tree
            $menuTree = $this->buildMenuTree($menuItems, $roles);

            // Optimized stats query
            $stats = [
                'users' => DB::table('users')->count(),
                'orders' => 50, // Replace with actual query when needed
                'revenue' => 10000, // Replace with actual query when needed
            ];

            return [
                'stats' => $stats,
                'menuItems' => $menuTree,
                'roles' => $roles,
                'user' => $user->only(['id', 'name', 'email', 'role']),
            ];
        });

        return response()->json([
            'message' => 'Dashboard data loaded successfully',
            'data' => $data
        ]);
    }

    /**
     * Get admin stats for attendance dashboard - HEAVILY OPTIMIZED
     */
    public function getAdminStats(): JsonResponse
    {
        try {
            // Cache for 2 minutes since attendance data changes frequently
            $cacheKey = 'admin_attendance_stats_' . Carbon::today()->format('Y-m-d');
            
            $stats = Cache::remember($cacheKey, 120, function () {
                $today = Carbon::today();
                
                // Single optimized query to get all stats at once
                $result = DB::selectOne("
                    SELECT 
                        -- Today's attendance stats
                        COUNT(CASE WHEN DATE(a.date) = ? THEN 1 END) as today_total,
                        COUNT(CASE WHEN DATE(a.date) = ? AND a.status IN ('present', 'late') THEN 1 END) as today_present,
                        COUNT(CASE WHEN DATE(a.date) = ? AND a.status = 'late' THEN 1 END) as today_late,
                        COUNT(CASE WHEN DATE(a.date) = ? AND a.status = 'absent' THEN 1 END) as today_absent,
                        COUNT(CASE WHEN DATE(a.date) = ? AND a.break_status = 'on_break' THEN 1 END) as today_on_break,
                        
                        -- Total employees
                        (SELECT COUNT(*) FROM users WHERE role = 'employee') as total_employees
                    FROM attendances a
                    JOIN users u ON a.user_id = u.id
                    WHERE u.role = 'employee'
                ", [$today, $today, $today, $today, $today]);

                // Get weekly trend with a separate optimized query
                $weeklyTrend = DB::select("
                    SELECT 
                        DATE(a.date) as date,
                        COUNT(*) as total,
                        COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) as present
                    FROM attendances a
                    JOIN users u ON a.user_id = u.id
                    WHERE u.role = 'employee'
                    AND DATE(a.date) BETWEEN ? AND ?
                    GROUP BY DATE(a.date)
                    ORDER BY date
                ", [Carbon::now()->subDays(6), $today]);

                $attendanceRate = $result->today_total > 0 
                    ? round(($result->today_present / $result->today_total) * 100, 1)
                    : 0;

                return [
                    'today' => [
                        'total' => (int)$result->today_total,
                        'present' => (int)$result->today_present,
                        'late' => (int)$result->today_late,
                        'absent' => (int)$result->today_absent,
                        'on_break' => (int)$result->today_on_break,
                        'attendance_rate' => $attendanceRate
                    ],
                    'total_employees' => (int)$result->total_employees,
                    'weekly_trend' => collect($weeklyTrend)->map(function ($day) {
                        return [
                            'date' => $day->date,
                            'total' => (int)$day->total,
                            'present' => (int)$day->present
                        ];
                    })
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch admin stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine access for a menu based on role - OPTIMIZED
     */
    private function hasAccess(array $roles, string $menuName): bool
    {
        // Use static cache to avoid repeated array operations
        static $permissionCache = [];
        
        $cacheKey = implode('_', $roles) . '_' . $menuName;
        
        if (isset($permissionCache[$cacheKey])) {
            return $permissionCache[$cacheKey];
        }

        $permissions = [
            'developer'  => ['Home','User Management','Profile Management','Finance','Settings','All Users','User Roles','Permissions','Menu Management','System Settings','Transactions','Reports'],
            'superadmin' => ['Home','User Management','Profile Management','Finance','Settings','All Users','User Roles','Menu Management','Transactions','Reports'],
            'admin'      => ['Home','Profile Management','Settings'],
            'employee'   => ['Home','Profile Management'],
        ];

        $hasAccess = false;
        foreach ($roles as $role) {
            if (isset($permissions[$role]) && in_array($menuName, $permissions[$role])) {
                $hasAccess = true;
                break;
            }
        }

        $permissionCache[$cacheKey] = $hasAccess;
        return $hasAccess;
    }

    /**
     * Build nested menu tree - OPTIMIZED
     */
    private function buildMenuTree($menus, $userRoles, $parentId = null)
    {
        $tree = collect();

        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $children = $this->buildMenuTree($menus, $userRoles, $menu->id);
            
            // Only include necessary menu data
            $menuData = (object)[
                'id' => $menu->id,
                'name' => $menu->name,
                'url' => $menu->url,
                'icon' => $menu->icon,
                'children' => $children->isNotEmpty() ? $children->values()->toArray() : [],
                'can_edit' => in_array('superadmin', $userRoles) || in_array('developer', $userRoles),
                'can_delete' => in_array('developer', $userRoles),
            ];
            
            $tree->push($menuData);
        }

        return $tree;
    }
    
    public function adminStats()
    {
        try {
            return response()->json([
                'success' => true,
                'stats' => [
                    'total_employees' => \App\Models\User::where('role', 'employee')->count(),
                    'present_today' => \App\Models\Attendance::whereDate('date', today())
                        ->whereNotNull('check_in_time')
                        ->count(),
                    'total_projects' => 0, // Add your project count logic
                    'active_sessions' => \App\Models\Attendance::whereDate('date', today())
                        ->whereNotNull('check_in_time')
                        ->whereNull('check_out_time')
                        ->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin stats'
            ], 500);
        }
    }

    /**
     * Get simple dashboard stats for profile tab
     */
    public function getProfileStats(): JsonResponse
    {
        try {
            $totalUsers = User::count();
            $totalReports = 0; // Placeholder for finance reports count
            $systemActive = true; // Placeholder for system status

            return response()->json([
                'success' => true,
                'totalUsers' => $totalUsers,
                'financeReports' => $totalReports,
                'systemSettings' => $systemActive,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile stats',
                'totalUsers' => 0,
                'financeReports' => 0,
                'systemSettings' => false,
            ], 500);
        }
    }

    /**
     * Get all users for admin dashboard (JSON API)
     */
    public function getAllUsers(): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            $currentRole = $currentUser->role ?? 'employee';

            // Role-based filtering
            $usersQuery = User::select('id', 'name', 'email', 'username', 'role', 'department', 'phone', 'employee_id', 'status', 'created_at');

            switch ($currentRole) {
                case 'developer':
                    // Developers can see all users
                    break;
                case 'superadmin':
                    // Superadmins can see all except developers
                    $usersQuery->where('role', '!=', 'developer');
                    break;
                case 'admin':
                    // Admins can see employees and other admins
                    $usersQuery->whereIn('role', ['admin', 'employee']);
                    break;
                default:
                    // Employees can only see themselves
                    $usersQuery->where('id', $currentUser->id);
                    break;
            }

            $users = $usersQuery->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $users,
                'total' => $users->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

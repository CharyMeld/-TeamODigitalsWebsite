<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\RoleMenuPermission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MenuService
{
    /**
     * Get menu items for a specific role with permissions
     *
     * @param string|array $roles
     * @return array
     */
    public function getMenuForRole($roles): array
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $cacheKey = 'menu_' . implode('_', $roles);

        return Cache::remember($cacheKey, 900, function () use ($roles) {
            // Get role IDs
            $roleIds = \Spatie\Permission\Models\Role::whereIn('name', $roles)->pluck('id');

            // Get menu items accessible by these roles
            $menuItemIds = RoleMenuPermission::whereIn('role_id', $roleIds)
                ->where('can_view', true)
                ->pluck('menu_item_id')
                ->unique();

            // Get menu items with their relationships
            $menuItems = MenuItem::with('children')
                ->whereIn('id', $menuItemIds)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            // Build tree structure
            return $this->buildTree($menuItems, $roles);
        });
    }

    /**
     * Build hierarchical menu tree
     *
     * @param \Illuminate\Support\Collection $menuItems
     * @param array $roles
     * @param int|null $parentId
     * @return array
     */
    private function buildTree($menuItems, $roles, $parentId = null): array
    {
        $tree = [];

        foreach ($menuItems->where('parent_id', $parentId) as $menu) {
            $children = $this->buildTree($menuItems, $roles, $menu->id);

            // Generate route if not set or use existing
            $route = $menu->route ?: $this->generateRoute($menu, $roles);

            // Generate slug if not set
            $slug = $menu->slug ?: \Illuminate\Support\Str::slug($menu->name);

            $tree[] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $slug,
                'route' => $route,
                'icon' => $menu->icon ?? 'circle',
                'parent_id' => $menu->parent_id,
                'sort_order' => $menu->sort_order,
                'children' => $children,
                'has_children' => count($children) > 0,
            ];
        }

        return $tree;
    }

    /**
     * Auto-generate route based on menu hierarchy and role
     *
     * @param MenuItem $menu
     * @param array $roles
     * @return string|null
     */
    private function generateRoute(MenuItem $menu, array $roles): ?string
    {
        // If route already exists in database, use it
        if ($menu->route) {
            return $this->validateRoute($menu->route) ? $menu->route : null;
        }

        // Determine the role prefix (use highest priority role)
        $rolePrefix = $this->getRolePrefix($roles);

        // Generate route based on menu name and hierarchy
        $slug = Str::slug($menu->name);

        // Build route name
        if ($menu->parent_id) {
            $parent = MenuItem::find($menu->parent_id);
            if ($parent) {
                $parentSlug = Str::slug($parent->name);
                $routeName = "{$rolePrefix}.{$parentSlug}.{$slug}";
            } else {
                $routeName = "{$rolePrefix}.{$slug}";
            }
        } else {
            $routeName = "{$rolePrefix}.{$slug}";
        }

        // Special cases for common routes
        $routeName = $this->normalizeRouteName($routeName, $menu->name);

        // Check if route exists, if not return a fallback
        return $this->validateRoute($routeName) ? $routeName : null;
    }

    /**
     * Get role prefix for route generation (priority: developer > superadmin > admin > employee)
     *
     * @param array $roles
     * @return string
     */
    private function getRolePrefix(array $roles): string
    {
        if (in_array('developer', $roles)) return 'developer';
        if (in_array('superadmin', $roles)) return 'superadmin';
        if (in_array('admin', $roles)) return 'admin';
        if (in_array('employee', $roles)) return 'employee';

        return 'dashboard';
    }

    /**
     * Normalize route names to match actual Laravel route names
     *
     * @param string $routeName
     * @param string $menuName
     * @return string
     */
    private function normalizeRouteName(string $routeName, string $menuName): string
    {
        // Map common menu names to actual route names
        $routeMap = [
            'home' => 'dashboard',
            'profile-management' => 'profile.index',
            'user-management' => 'users.index',
            'all-users' => 'users.index',
            'user-roles' => 'access.index',
            'permissions' => 'access.index',
            'menu-management' => 'menu-items.index',
            'system-settings' => 'settings.index',
            'finance-reports' => 'finance.reports',
        ];

        $lowerMenuName = Str::slug($menuName);

        // Check if we have a mapping
        foreach ($routeMap as $pattern => $replacement) {
            if (str_contains($routeName, $pattern)) {
                return str_replace($pattern, $replacement, $routeName);
            }
        }

        return $routeName;
    }

    /**
     * Check if a route exists in the application
     *
     * @param string $routeName
     * @return bool
     */
    private function validateRoute(string $routeName): bool
    {
        try {
            return Route::has($routeName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Auto-register routes for menu items that don't have corresponding routes
     *
     * @param array $roles
     * @return array List of auto-registered routes
     */
    public function autoRegisterRoutes(array $roles = []): array
    {
        $registeredRoutes = [];

        $menuItems = MenuItem::where('is_active', true)->get();

        foreach ($menuItems as $menu) {
            if ($menu->route && !$this->validateRoute($menu->route)) {
                // Auto-register missing route
                $routeName = $menu->route;
                $rolePrefix = $this->getRolePrefix($roles);

                // Create a placeholder route that shows a "Coming Soon" page
                Route::get("/{$rolePrefix}/" . Str::slug($menu->name), function () use ($menu) {
                    return inertia('Shared/ComingSoon', [
                        'menuName' => $menu->name,
                        'message' => "The {$menu->name} page is under development."
                    ]);
                })->name($routeName)->middleware(['auth', "check.role:" . implode(',', $roles)]);

                $registeredRoutes[] = $routeName;
            }
        }

        return $registeredRoutes;
    }

    /**
     * Get flat menu list (for dropdown/select inputs)
     *
     * @param array $roles
     * @return array
     */
    public function getFlatMenu(array $roles): array
    {
        $tree = $this->getMenuForRole($roles);
        return $this->flattenTree($tree);
    }

    /**
     * Flatten nested menu tree
     *
     * @param array $tree
     * @param string $prefix
     * @return array
     */
    private function flattenTree(array $tree, string $prefix = ''): array
    {
        $flat = [];

        foreach ($tree as $item) {
            $label = $prefix . $item['name'];

            $flat[] = [
                'id' => $item['id'],
                'name' => $label,
                'route' => $item['route'],
                'level' => substr_count($prefix, '─') + 1,
            ];

            if (!empty($item['children'])) {
                $childFlat = $this->flattenTree($item['children'], $prefix . '─ ');
                $flat = array_merge($flat, $childFlat);
            }
        }

        return $flat;
    }

    /**
     * Clear menu cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        $roles = ['developer', 'superadmin', 'admin', 'employee'];

        foreach ($roles as $role) {
            Cache::forget('menu_' . $role);
        }

        // Clear combination caches
        Cache::forget('menu_developer_superadmin');
        Cache::forget('menu_developer_superadmin_admin');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MenuItem;
use App\Models\RoleMenuPermission;
use App\Services\MenuService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class SetupMenuSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:setup
                            {--sync : Sync existing menu items with routes}
                            {--clear : Clear menu cache}
                            {--show : Show all menus by role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and manage the dynamic menu system';

    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        parent::__construct();
        $this->menuService = $menuService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('==============================================');
        $this->info('   Dynamic Menu System Setup');
        $this->info('==============================================');
        $this->newLine();

        if ($this->option('clear')) {
            return $this->clearCache();
        }

        if ($this->option('show')) {
            return $this->showMenus();
        }

        if ($this->option('sync')) {
            return $this->syncMenus();
        }

        // Default: Show menu status
        $this->showStatus();

        return Command::SUCCESS;
    }

    /**
     * Clear menu cache
     */
    protected function clearCache()
    {
        $this->info('Clearing menu cache...');
        $this->menuService->clearCache();
        $this->info('✅ Menu cache cleared successfully!');

        return Command::SUCCESS;
    }

    /**
     * Show menus by role
     */
    protected function showMenus()
    {
        $roles = Role::all()->pluck('name')->toArray();

        foreach ($roles as $role) {
            $this->newLine();
            $this->info("📋 Menus for role: {$role}");
            $this->line(str_repeat('-', 50));

            $menus = $this->menuService->getMenuForRole($role);

            if (empty($menus)) {
                $this->warn('  No menus found');
                continue;
            }

            $this->displayMenuTree($menus);
        }

        return Command::SUCCESS;
    }

    /**
     * Display menu tree recursively
     */
    protected function displayMenuTree($menus, $level = 0)
    {
        $indent = str_repeat('  ', $level);

        foreach ($menus as $menu) {
            $icon = $menu['icon'] ?? '';
            $route = $menu['route'] ?? 'No route';
            $name = $menu['name'];

            $this->line("{$indent}├─ {$name}");
            $this->line("{$indent}   Route: {$route}");

            if (!empty($menu['children'])) {
                $this->displayMenuTree($menu['children'], $level + 1);
            }
        }
    }

    /**
     * Sync menu items with routes
     */
    protected function syncMenus()
    {
        $this->info('Syncing menu items with routes...');
        $this->newLine();

        $menuItems = MenuItem::with('roles')->get();
        $updated = 0;
        $issues = [];

        foreach ($menuItems as $menu) {
            if (!$menu->route) {
                // Try to generate route
                $generatedRoute = $this->generateRoute($menu);

                if ($generatedRoute) {
                    $menu->route = $generatedRoute;
                    $menu->save();
                    $updated++;
                    $this->info("✅ Generated route for '{$menu->name}': {$generatedRoute}");
                } else {
                    $issues[] = "⚠️  Could not generate route for '{$menu->name}' (ID: {$menu->id})";
                }
            } else {
                // Validate existing route
                if (!\Illuminate\Support\Facades\Route::has($menu->route)) {
                    $issues[] = "❌ Route '{$menu->route}' does not exist for menu '{$menu->name}'";
                }
            }
        }

        $this->newLine();
        $this->info("✅ Synced {$updated} menu items");

        if (!empty($issues)) {
            $this->newLine();
            $this->warn('Issues found:');
            foreach ($issues as $issue) {
                $this->line($issue);
            }
        }

        // Clear cache after sync
        $this->menuService->clearCache();

        return Command::SUCCESS;
    }

    /**
     * Generate route for a menu item
     */
    protected function generateRoute(MenuItem $menu): ?string
    {
        // Get roles for this menu
        $roles = $menu->roles->pluck('name')->toArray();

        if (empty($roles)) {
            return null;
        }

        // Determine role prefix
        $rolePrefix = $this->getRolePrefix($roles);

        // Build route name
        $slug = $menu->slug ?: Str::slug($menu->name);

        if ($menu->parent_id) {
            $parent = $menu->parent;
            if ($parent) {
                $parentSlug = $parent->slug ?: Str::slug($parent->name);
                return "{$rolePrefix}.{$parentSlug}.{$slug}";
            }
        }

        return "{$rolePrefix}.{$slug}";
    }

    /**
     * Get role prefix
     */
    protected function getRolePrefix(array $roles): string
    {
        if (in_array('developer', $roles)) return 'developer';
        if (in_array('superadmin', $roles)) return 'superadmin';
        if (in_array('admin', $roles)) return 'admin';
        if (in_array('employee', $roles)) return 'employee';

        return 'dashboard';
    }

    /**
     * Show menu system status
     */
    protected function showStatus()
    {
        $totalMenus = MenuItem::count();
        $activeMenus = MenuItem::where('is_active', true)->count();
        $menusWithRoutes = MenuItem::whereNotNull('route')->count();
        $menusWithoutRoutes = MenuItem::whereNull('route')->count();

        $roles = Role::count();
        $permissions = RoleMenuPermission::count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Menu Items', $totalMenus],
                ['Active Menu Items', $activeMenus],
                ['Menus with Routes', $menusWithRoutes],
                ['Menus without Routes', $menusWithoutRoutes],
                ['Total Roles', $roles],
                ['Role-Menu Permissions', $permissions],
            ]
        );

        $this->newLine();
        $this->info('Available commands:');
        $this->line('  php artisan menu:setup --sync   Sync menu items with routes');
        $this->line('  php artisan menu:setup --show   Show all menus by role');
        $this->line('  php artisan menu:setup --clear  Clear menu cache');
    }
}

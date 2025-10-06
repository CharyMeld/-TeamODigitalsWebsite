<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menu items
        MenuItem::truncate();

        // Create menu items
        $menuItems = [
            // Home
            [
                'name' => 'Home',
                'route' => '/dashboard',
                'icon' => 'home',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],

            // User Management
            [
                'name' => 'User Management',
                'route' => null,
                'icon' => 'users',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],

            // Profile Management
            [
                'name' => 'Profile Management',
                'route' => '/shared/profile/edit',
                'icon' => 'user',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],

            // Finance
            [
                'name' => 'Finance',
                'route' => null,
                'icon' => 'dollar-sign',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
            ],

            // Settings
            [
                'name' => 'Settings',
                'route' => null,
                'icon' => 'settings',
                'parent_id' => null,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        // Insert parent menu items first
        $parentMenus = [];
        foreach ($menuItems as $item) {
            $menu = MenuItem::create($item);
            $parentMenus[$item['name']] = $menu->id;
        }

        // Create sub-menu items
        $subMenuItems = [
            // User Management sub-items
            [
                'name' => 'All Users',
                'route' => '/superadmin/users',
                'icon' => 'user-check',
                'parent_id' => $parentMenus['User Management'],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'User Roles',
                'route' => '/superadmin/access-control',
                'icon' => 'shield',
                'parent_id' => $parentMenus['User Management'],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Permissions',
                'route' => '/superadmin/access-control',
                'icon' => 'key',
                'parent_id' => $parentMenus['User Management'],
                'sort_order' => 3,
                'is_active' => true,
            ],

            // Finance sub-items
            [
                'name' => 'Transactions',
                'route' => '/superadmin/finance/transactions',
                'icon' => 'credit-card',
                'parent_id' => $parentMenus['Finance'],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Reports',
                'route' => '/superadmin/finance/reports',
                'icon' => 'bar-chart',
                'parent_id' => $parentMenus['Finance'],
                'sort_order' => 2,
                'is_active' => true,
            ],

            // Settings sub-items
            [
                'name' => 'Menu Management',
                'route' => '/superadmin/menu-items',
                'icon' => 'menu',
                'parent_id' => $parentMenus['Settings'],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'System Settings',
                'route' => '/superadmin/settings',
                'icon' => 'tool',
                'parent_id' => $parentMenus['Settings'],
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        // Insert sub-menu items
        foreach ($subMenuItems as $item) {
            MenuItem::create($item);
        }

        $this->command->info('Menu items seeded successfully!');
    }
}

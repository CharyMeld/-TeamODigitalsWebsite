<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'view dashboards',
            'assign roles',
            'delete users',
            'delete self',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $developer = Role::firstOrCreate(['name' => 'developer']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $employee = Role::firstOrCreate(['name' => 'employee']);

        // Assign permissions
        $developer->givePermissionTo(Permission::all());

        $superadmin->givePermissionTo([
            'view dashboards',
            'assign roles',
            'delete self',
        ]);

        $admin->givePermissionTo([
            'view dashboards',
        ]);

        $employee->givePermissionTo([
            'view dashboards',
        ]);
    }
}


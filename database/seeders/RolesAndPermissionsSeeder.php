<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---- Define Permissions (CRUD-style examples) ----
        $permissions = [
            // Users
            'users.view', 'users.create', 'users.update', 'users.delete',
            // Roles
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            // Permissions
            'permissions.view', 'permissions.create', 'permissions.update', 'permissions.delete',
            // Domain examples
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'reports.view',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // ---- Roles ----
        $developer  = Role::findOrCreate('developer', 'web');
        $superadmin = Role::findOrCreate('superadmin', 'web');
        $admin      = Role::findOrCreate('admin', 'web');
        $employee   = Role::findOrCreate('employee', 'web');

        // ---- Assign permissions per your spec ----
        // Developer: full access (including delete)
        $developer->syncPermissions(Permission::all());

        // Superadmin: full access WITHOUT delete
        $superadmin->syncPermissions(
            Permission::all()->filter(fn($perm) => !str_ends_with($perm->name, '.delete'))
        );

        // Admin: start minimal (customize later in UI)
        $admin->syncPermissions([
            'users.view', 'projects.view', 'reports.view'
        ]);

        // Employee: very limited
        $employee->syncPermissions([
            'projects.view', 'reports.view'
        ]);
    }
}


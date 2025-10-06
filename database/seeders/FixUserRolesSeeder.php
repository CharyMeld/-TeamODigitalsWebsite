<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class FixUserRolesSeeder extends Seeder
{
    public function run()
    {
        // Create roles if they don't exist
        $roles = ['developer', 'superadmin', 'admin', 'employee'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Fix existing users - ensure they have proper roles and status
        $users = User::all();
        
        foreach ($users as $user) {
            // Ensure user is active
            if (empty($user->status)) {
                $user->update(['status' => 'active']);
            }

            // If user has no roles, assign employee role as default
            if (!$user->roles->count()) {
                $user->assignRole('employee');
                echo "Assigned 'employee' role to: {$user->name} ({$user->email})\n";
            }
        }

        // Create test users with specific roles if they don't exist
        $testUsers = [
            [
                'name' => 'System Developer',
                'email' => 'developer@company.com',
                'role' => 'developer',
                'employee_id' => 'TDS|DEV001',
            ],
            [
                'name' => 'Super Administrator', 
                'email' => 'superadmin@company.com',
                'role' => 'superadmin',
                'employee_id' => 'TDS|SUP001',
            ],
            [
                'name' => 'System Administrator',
                'email' => 'admin@company.com', 
                'role' => 'admin',
                'employee_id' => 'TDS|ADM001',
            ],
            [
                'name' => 'Test Employee',
                'email' => 'employee@company.com',
                'role' => 'employee', 
                'employee_id' => 'TDS|EMP001',
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'employee_id' => $userData['employee_id'],
                    'email_verified_at' => now(),
                ]
            );

            // Remove all existing roles and assign the correct one
            $user->syncRoles([$userData['role']]);
            
            echo "Created/Updated: {$user->name} with role '{$userData['role']}'\n";
        }

        echo "\n=== User Role Summary ===\n";
        foreach (User::with('roles')->get() as $user) {
            $roleNames = $user->roles->pluck('name')->join(', ');
            echo "{$user->name} ({$user->email}) - Roles: {$roleNames} - Status: {$user->status}\n";
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserAccess extends Command
{
    protected $signature = 'user:check-access {email}';
    protected $description = 'Check user access and role configuration';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->with('roles')->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        $this->info("=== User Access Report ===");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Employee ID: {$user->employee_id}");
        $this->line("Status: {$user->status}");
        
        if ($user->roles->count() > 0) {
            $this->line("Roles: " . $user->roles->pluck('name')->join(', '));
            
            // Test role hierarchy
            if ($user->hasRole('developer')) {
                $this->line("✅ Should redirect to: /developer/dashboard");
            } elseif ($user->hasRole('superadmin')) {
                $this->line("✅ Should redirect to: /superadmin/dashboard");
            } elseif ($user->hasRole('admin')) {
                $this->line("✅ Should redirect to: /admin/dashboard");
            } elseif ($user->hasRole('employee')) {
                $this->line("✅ Should redirect to: /employee/dashboard");
            }
        } else {
            $this->error("❌ No roles assigned!");
        }

        if ($user->status !== 'active') {
            $this->error("❌ Account is not active!");
        }

        return 0;
    }
}

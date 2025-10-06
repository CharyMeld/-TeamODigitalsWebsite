<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class DashboardPolicy
{
    public function accessDeveloperDashboard(User $user)
    {
        return $user->role === 'developer';
    }

    public function accessSuperadminDashboard(User $user)
    {
        // Superadmin can access everything except deleting other users
        return in_array($user->role, ['superadmin', 'developer']);
    }

    public function accessAdminDashboard(User $user)
    {
        // Developer + Superadmin + Admin
        return in_array($user->role, ['developer', 'superadmin', 'admin']);
    }

    public function accessEmployeeDashboard(User $user)
    {
        // Everyone can access employee dashboard (dev, superadmin, admin, employee)
        return in_array($user->role, ['developer', 'superadmin', 'admin', 'employee']);
    }


    // 🔒 Delete Restrictions
    public function delete(User $user, $model): bool
    {
        // Developer can delete anything
        if ($user->hasRole('developer')) {
            return true;
        }

        // Superadmin can delete only their own profile
        if ($user->hasRole('superadmin') && $user->id === $model->id) {
            return true;
        }

        return false;
    }

    // 🔑 Role Assignment
    public function assignRoles(User $user): bool
    {
        return $user->hasRole(['developer', 'superadmin']);
    }
}


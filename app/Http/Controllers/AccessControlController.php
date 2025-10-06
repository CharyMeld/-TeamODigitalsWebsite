<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccessControlController extends Controller
{
    public function index(Request $request)
    {
        // Data for grid:
        $users = User::select('id','name','email')->with('roles:id,name')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get(['id','name']);
        $permissions = Permission::orderBy('name')->get(['id','name']);

        // Role → permissions matrix map
        $rolePermissions = Role::with('permissions:id,name')->get(['id','name'])
            ->mapWithKeys(function ($role) {
                return [$role->id => $role->permissions->pluck('name')->values()];
            });

        return Inertia::render('AccessControl/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function syncUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        // Security: Superadmin cannot assign delete to self indirectly (handled at role level)
        $user->syncRoles($validated['roles'] ?? []);

        return back()->with('success', 'User roles updated.');
    }

    public function syncRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // Enforce “Superadmin cannot have delete” rule:
        if ($role->name === 'superadmin') {
            $validated['permissions'] = collect($validated['permissions'] ?? [])
                ->reject(fn($p) => str_ends_with($p, '.delete'))
                ->values()
                ->all();
        }

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', 'Role permissions updated.');
    }

    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255','unique:permissions,name'],
        ]);

        // Optional: prevent creating ".delete" by non-developers
        if ($request->user()->hasRole('superadmin') && str_ends_with($validated['name'], '.delete')) {
            return back()->withErrors(['name' => 'Superadmin cannot create delete permissions.']);
        }

        Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        return back()->with('success', 'Permission created.');
    }
}


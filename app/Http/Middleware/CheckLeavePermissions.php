<?php

/*
|--------------------------------------------------------------------------
| Custom Middleware for Leave Management
|--------------------------------------------------------------------------
*/

// app/Http/Middleware/CheckLeavePermissions.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLeavePermissions
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Define permissions
        $permissions = [
            'view_all_requests' => ['admin', 'superadmin'],
            'approve_employee_requests' => ['admin', 'superadmin'],
            'approve_admin_requests' => ['superadmin'],
            'submit_request' => ['employee', 'admin', 'superadmin'],
            'download_attachments' => ['employee', 'admin', 'superadmin']
        ];

        if ($permission && isset($permissions[$permission])) {
            if (!in_array($user->role, $permissions[$permission])) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }
        }

        return $next($request);
    }
}

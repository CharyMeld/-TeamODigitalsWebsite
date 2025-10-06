<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Get user's role from the 'role' column
        $userRole = $request->user()->role;

        // Define role hierarchy: higher roles have access to lower role routes
        $roleHierarchy = [
            'developer' => ['developer', 'superadmin', 'admin', 'supervisor', 'employee'],
            'superadmin' => ['superadmin', 'admin', 'supervisor', 'employee'],
            'admin' => ['admin', 'supervisor', 'employee'],
            'supervisor' => ['supervisor', 'employee'],
            'employee' => ['employee'],
        ];

        // Get allowed roles for current user based on hierarchy
        $userAllowedRoles = $roleHierarchy[$userRole] ?? [$userRole];

        // Check if any of the required roles is in user's allowed roles
        $hasAccess = false;
        foreach ($roles as $requiredRole) {
            if (in_array($requiredRole, $userAllowedRoles)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            // Return 403 for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles) . '. Your role: ' . $userRole
                ], 403);
            }

            // Redirect for web requests
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}

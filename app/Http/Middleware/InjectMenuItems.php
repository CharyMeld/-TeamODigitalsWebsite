<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MenuService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InjectMenuItems
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Get user roles
            $roles = $user->roles->pluck('name')->toArray();

            // If no roles from Spatie, use the role column
            if (empty($roles) && $user->role) {
                $roles = [$user->role];
            }

            // Log for debugging
            \Log::info('InjectMenuItems: User roles', ['roles' => $roles]);

            // Get menus for user roles
            $menuItems = $this->menuService->getMenuForRole($roles);

            // Log menu count
            \Log::info('InjectMenuItems: Menus fetched', ['count' => count($menuItems)]);

            // Share menus with all Inertia views
            Inertia::share('menuItems', $menuItems);
        }

        return $next($request);
    }
}

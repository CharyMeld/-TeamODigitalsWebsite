<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'username' => $request->user()->username ?? null,
                    'employee_id' => $request->user()->employee_id ?? null,
                    'phone' => $request->user()->phone ?? null,
                    'address' => $request->user()->address ?? null,
                    'gender' => $request->user()->gender ?? null,
                    'date_of_birth' => $request->user()->date_of_birth ?? null,
                    'marital_status' => $request->user()->marital_status ?? null,
                    'emergency_contact' => $request->user()->emergency_contact ?? null,
                    'local_government' => $request->user()->local_government ?? null,
                    'state' => $request->user()->state ?? null,
                    'country' => $request->user()->country ?? null,
                    'department' => $request->user()->department ?? null,
                    'salary' => $request->user()->salary ?? null,
                    'hire_date' => $request->user()->hire_date ?? null,
                    'status' => $request->user()->status ?? 'active',
                    'roles' => $request->user()->roles ?? [],
                    'profile_photo_path' => $request->user()->profile_photo_path ?? null,
                    'profile_photo_url' => $request->user()->profile_photo_url ?? null,
                    'current_team_id' => $request->user()->current_team_id ?? null,
                    'two_factor_enabled' => $request->user()->two_factor_secret !== null,
                    'two_factor_confirmed_at' => $request->user()->two_factor_confirmed_at ?? null,
                ] : null,
            ],
            'jetstream' => [
                'canCreateTeams' => config('jetstream.features.teams', false),
                'canManageTeams' => config('jetstream.features.teams', false),
                'canUpdateProfileInformation' => config('jetstream.features.profile-management', false),
                'canUpdatePassword' => config('jetstream.features.profile-management', false),
                'canManageTwoFactorAuthentication' => config('jetstream.features.two-factor-authentication', false),
                'managesProfilePhotos' => config('jetstream.features.profile-photos', false),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            // Add CSRF token to shared props
            'csrf_token' => csrf_token(),
            // Add menus and other shared data you might need
            'menus' => fn () => $this->getMenus($request),
            
            'errors' => function () use ($request) {
                $errors = $request->session()->get('errors');
                return $errors ? $errors->getBag('default')->getMessages() : (object) [];
            },
        ]);
    }


    /**
     * Get menu items for the authenticated user
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function getMenus(Request $request): array
    {
        if (!$request->user()) {
            return [];
        }

        try {
            $user = $request->user();

            // Get user roles
            $roles = $user->roles->pluck('name')->toArray();

            // If no Spatie roles, use role column
            if (empty($roles) && $user->role) {
                $roles = [$user->role];
            }

            // Try to get menus from MenuService
            $menuService = app(\App\Services\MenuService::class);
            $menuItems = $menuService->getMenuForRole($roles);

            // If no menu items returned, provide fallback menus
            if (empty($menuItems)) {
                $menuItems = $this->getFallbackMenus($user->role);
            }

            \Log::info('HandleInertiaRequests: Menus shared', [
                'user_id' => $user->id,
                'roles' => $roles,
                'menu_count' => count($menuItems)
            ]);

            return $menuItems;
        } catch (\Exception $e) {
            \Log::error('HandleInertiaRequests: Error getting menus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback menus on error
            return $this->getFallbackMenus($request->user()->role ?? 'employee');
        }
    }

    /**
     * Get fallback menu structure when MenuService returns empty
     *
     * @param string $role
     * @return array
     */
    protected function getFallbackMenus(string $role): array
    {
        // Superadmin menus
        if ($role === 'superadmin') {
            return [
                [
                    'id' => 1,
                    'name' => 'Dashboard',
                    'slug' => 'dashboard',
                    'route' => 'superadmin.dashboard',
                    'icon' => 'home',
                    'parent_id' => null,
                    'sort_order' => 1,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'User Management',
                    'slug' => 'users',
                    'route' => null,
                    'icon' => 'users',
                    'parent_id' => null,
                    'sort_order' => 2,
                    'children' => [
                        [
                            'id' => 21,
                            'name' => 'All Users',
                            'slug' => 'all-users',
                            'route' => 'superadmin.users.index',
                            'icon' => 'user',
                            'parent_id' => 2,
                            'sort_order' => 1,
                            'children' => [],
                            'has_children' => false,
                        ],
                        [
                            'id' => 22,
                            'name' => 'Add User',
                            'slug' => 'add-user',
                            'route' => 'superadmin.users.create',
                            'icon' => 'user-plus',
                            'parent_id' => 2,
                            'sort_order' => 2,
                            'children' => [],
                            'has_children' => false,
                        ],
                    ],
                    'has_children' => true,
                ],
                [
                    'id' => 3,
                    'name' => 'Access Control',
                    'slug' => 'access-control',
                    'route' => 'superadmin.access.index',
                    'icon' => 'shield',
                    'parent_id' => null,
                    'sort_order' => 3,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 4,
                    'name' => 'System',
                    'slug' => 'system',
                    'route' => null,
                    'icon' => 'settings',
                    'parent_id' => null,
                    'sort_order' => 4,
                    'children' => [
                        [
                            'id' => 41,
                            'name' => 'Settings',
                            'slug' => 'settings',
                            'route' => 'superadmin.settings.index',
                            'icon' => 'sliders',
                            'parent_id' => 4,
                            'sort_order' => 1,
                            'children' => [],
                            'has_children' => false,
                        ],
                        [
                            'id' => 42,
                            'name' => 'Menu Management',
                            'slug' => 'menu-management',
                            'route' => 'superadmin.menu-items.index',
                            'icon' => 'menu',
                            'parent_id' => 4,
                            'sort_order' => 2,
                            'children' => [],
                            'has_children' => false,
                        ],
                    ],
                    'has_children' => true,
                ],
                [
                    'id' => 5,
                    'name' => 'Finance',
                    'slug' => 'finance',
                    'route' => null,
                    'icon' => 'dollar-sign',
                    'parent_id' => null,
                    'sort_order' => 5,
                    'children' => [
                        [
                            'id' => 51,
                            'name' => 'Reports',
                            'slug' => 'reports',
                            'route' => 'superadmin.finance.reports',
                            'icon' => 'file-text',
                            'parent_id' => 5,
                            'sort_order' => 1,
                            'children' => [],
                            'has_children' => false,
                        ],
                        [
                            'id' => 52,
                            'name' => 'Transactions',
                            'slug' => 'transactions',
                            'route' => 'superadmin.finance.transactions',
                            'icon' => 'credit-card',
                            'parent_id' => 5,
                            'sort_order' => 2,
                            'children' => [],
                            'has_children' => false,
                        ],
                    ],
                    'has_children' => true,
                ],
            ];
        }

        // Admin menus
        if ($role === 'admin') {
            return [
                [
                    'id' => 1,
                    'name' => 'Dashboard',
                    'slug' => 'dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'home',
                    'parent_id' => null,
                    'sort_order' => 1,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'User Management',
                    'slug' => 'users',
                    'route' => null,
                    'icon' => 'users',
                    'parent_id' => null,
                    'sort_order' => 2,
                    'children' => [
                        [
                            'id' => 21,
                            'name' => 'All Users',
                            'slug' => 'all-users',
                            'route' => 'admin.users.index',
                            'icon' => 'user',
                            'parent_id' => 2,
                            'sort_order' => 1,
                            'children' => [],
                            'has_children' => false,
                        ],
                        [
                            'id' => 22,
                            'name' => 'Add User',
                            'slug' => 'add-user',
                            'route' => 'admin.users.create',
                            'icon' => 'user-plus',
                            'parent_id' => 2,
                            'sort_order' => 2,
                            'children' => [],
                            'has_children' => false,
                        ],
                    ],
                    'has_children' => true,
                ],
                [
                    'id' => 3,
                    'name' => 'Attendance',
                    'slug' => 'attendance',
                    'route' => 'admin.attendance.employees',
                    'icon' => 'calendar',
                    'parent_id' => null,
                    'sort_order' => 3,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 4,
                    'name' => 'Leave Requests',
                    'slug' => 'leave-requests',
                    'route' => 'admin.leaveRequests.index',
                    'icon' => 'clipboard',
                    'parent_id' => null,
                    'sort_order' => 4,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 5,
                    'name' => 'Finance',
                    'slug' => 'finance',
                    'route' => 'admin.finance.reports',
                    'icon' => 'dollar-sign',
                    'parent_id' => null,
                    'sort_order' => 5,
                    'children' => [],
                    'has_children' => false,
                ],
            ];
        }

        // Employee menus
        if ($role === 'employee') {
            return [
                [
                    'id' => 1,
                    'name' => 'Dashboard',
                    'slug' => 'dashboard',
                    'route' => 'employee.dashboard',
                    'icon' => 'home',
                    'parent_id' => null,
                    'sort_order' => 1,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'My Tasks',
                    'slug' => 'tasks',
                    'route' => 'employee.tasks.index',
                    'icon' => 'check-square',
                    'parent_id' => null,
                    'sort_order' => 2,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 3,
                    'name' => 'Leave Requests',
                    'slug' => 'leave-requests',
                    'route' => 'employee.leave-requests.my',
                    'icon' => 'clipboard',
                    'parent_id' => null,
                    'sort_order' => 3,
                    'children' => [],
                    'has_children' => false,
                ],
                [
                    'id' => 4,
                    'name' => 'Time Tracking',
                    'slug' => 'time-tracking',
                    'route' => 'employee.time-tracking',
                    'icon' => 'clock',
                    'parent_id' => null,
                    'sort_order' => 4,
                    'children' => [],
                    'has_children' => false,
                ],
            ];
        }

        return [];
    }
}

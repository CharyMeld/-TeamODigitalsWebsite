<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\RoleMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the menu items.
     */
    public function index()
    {
        $menuItems = MenuItem::with('children', 'parent')->orderBy('sort_order')->get();

        return Inertia::render('MenuItems/Index', [
            'menuItems' => $menuItems
        ]);
    }

    /**
     * Show the form for creating a new menu item (Vue SPA).
     */
    public function create()
    {
        return Inertia::render('MenuItems/Create');
    }

    /**
     * Return data for Vue Create page (parent menus + roles).
     */
    public function createData()
    {
        $parentMenus = MenuItem::parents()->active()->get(['id', 'name']);
        $roles = Role::all(['id', 'name']);

        return response()->json([
            'parentMenus' => $parentMenus,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|url',
            'parent_id' => 'nullable|exists:menu_items,id',
            'sort_order' => 'required|integer|min:0',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Determine level
        $level = 1;
        if ($request->parent_id) {
            $parent = MenuItem::find($request->parent_id);
            $level = $parent->level + 1;
            if ($level > 3) {
                return response()->json(['error' => 'Maximum menu level (3) exceeded.'], 422);
            }
        }

        $menuItem = MenuItem::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'route' => $request->route,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order,
            'level' => $level,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Assign roles
        foreach ($request->roles as $roleId) {
            RoleMenuPermission::create([
                'role_id' => $roleId,
                'menu_item_id' => $menuItem->id,
                'can_view' => true,
            ]);
        }

        return response()->json([
            'message' => 'Menu item created successfully!',
            'menuItem' => $menuItem,
        ]);
    }

    /**
     * Display the specified menu item.
     */
    public function show(MenuItem $menuItem)
    {
        $menuItem->load('children', 'parent', 'roles');

        return Inertia::render('MenuItems/Show', [
            'menuItem' => $menuItem
        ]);
    }

    /**
     * Show the form for editing the specified menu item.
     */
    public function edit(MenuItem $menuItem)
    {
        $parentMenus = MenuItem::parents()->active()->where('id', '!=', $menuItem->id)->get(['id', 'name']);
        $roles = Role::all(['id', 'name']);
        $assignedRoles = $menuItem->roles->pluck('id');

        return Inertia::render('MenuItems/Edit', [
            'menuItem' => $menuItem,
            'parentMenus' => $parentMenus,
            'roles' => $roles,
            'assignedRoles' => $assignedRoles,
        ]);
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|url',
            'parent_id' => 'nullable|exists:menu_items,id',
            'sort_order' => 'required|integer|min:0',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Determine level
        $level = 1;
        if ($request->parent_id) {
            $parent = MenuItem::find($request->parent_id);
            $level = $parent->level + 1;
            if ($level > 3) {
                return response()->json(['error' => 'Maximum menu level (3) exceeded.'], 422);
            }
        }

        $menuItem->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'route' => $request->route,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order,
            'level' => $level,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Sync roles
        RoleMenuPermission::where('menu_item_id', $menuItem->id)->delete();
        foreach ($request->roles as $roleId) {
            RoleMenuPermission::create([
                'role_id' => $roleId,
                'menu_item_id' => $menuItem->id,
                'can_view' => true,
            ]);
        }

        return response()->json([
            'message' => 'Menu item updated successfully!',
            'menuItem' => $menuItem,
        ]);
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully!'
        ]);
    }
}


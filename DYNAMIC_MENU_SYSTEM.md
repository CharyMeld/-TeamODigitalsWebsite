# Dynamic Menu System with Auto-Route Generation

## 📋 Overview

This system provides a **dynamic, role-based sidebar menu** that appears in all dashboards with automatic route generation and permission-based access control.

### Key Features

✅ **Auto-generated routes** - Create menus, routes are automatically generated
✅ **Role-based permissions** - Each menu item respects role permissions
✅ **Nested menus** - Support for parent-child menu hierarchy
✅ **Cache-optimized** - Menus are cached for 15 minutes per role
✅ **Universal sidebar** - Single sidebar component works for all roles
✅ **Coming Soon pages** - Auto-generated placeholder pages for incomplete features
✅ **Easy management** - Artisan commands for setup and maintenance

---

## 🚀 Quick Start

### 1. Create a New Menu Item

```bash
# Option A: Using the database
INSERT INTO menu_items (name, icon, parent_id, sort_order, is_active)
VALUES ('Reports Dashboard', 'chart-line', NULL, 5, 1);

# Option B: Using Laravel Tinker
php artisan tinker
>>> $menu = MenuItem::create([
    'name' => 'Reports Dashboard',
    'icon' => 'chart-line',
    'parent_id' => null,
    'sort_order' => 5,
    'is_active' => true
]);
```

### 2. Assign Roles to Menu

```php
use App\Models\RoleMenuPermission;
use Spatie\Permission\Models\Role;

$role = Role::where('name', 'admin')->first();
$menu = MenuItem::where('name', 'Reports Dashboard')->first();

RoleMenuPermission::create([
    'role_id' => $role->id,
    'menu_item_id' => $menu->id,
    'can_view' => true
]);
```

### 3. Sync and View Menus

```bash
# Sync routes automatically
php artisan menu:setup --sync

# View menus by role
php artisan menu:setup --show

# Clear menu cache
php artisan menu:setup --clear
```

### 4. That's It!

The menu will now automatically appear in the sidebar for users with the admin role. The route will be auto-generated based on the menu name.

---

## 📂 System Architecture

### Components Created

```
app/
├── Services/
│   └── MenuService.php          # Core menu service with auto-route generation
├── Http/
│   ├── Middleware/
│   │   └── InjectMenuItems.php  # Middleware to inject menus into all views
│   └── Kernel.php               # Updated with menu middleware
├── Models/
│   ├── MenuItem.php             # Enhanced with route generation methods
│   └── RoleMenuPermission.php   # Role-menu pivot model
└── Console/
    └── Commands/
        └── SetupMenuSystem.php  # Artisan command for menu management

resources/js/
├── Components/
│   └── DynamicSidebar.vue       # Universal sidebar component
├── Layouts/
│   └── UniversalDashboardLayout.vue  # Layout with integrated sidebar
└── Pages/
    └── Shared/
        └── ComingSoon.vue       # Placeholder for auto-generated routes
```

---

## 🎯 How It Works

### 1. Menu Creation

When you create a menu item in the `menu_items` table:

```sql
INSERT INTO menu_items (name, slug, icon, parent_id, sort_order, is_active)
VALUES ('User Management', 'user-management', 'users', NULL, 2, 1);
```

### 2. Auto-Route Generation

The `MenuService` automatically generates a route based on:

- **Role prefix** (developer/superadmin/admin/employee)
- **Parent menu slug** (if it's a child menu)
- **Menu slug**

**Example:**
- Menu: "User Management"
- Role: "admin"
- Generated Route: `admin.user-management`

### 3. Route Validation

The system checks if the generated route exists in Laravel's route list:

```php
if (Route::has('admin.user-management')) {
    // Use existing route
} else {
    // Auto-generate "Coming Soon" page
}
```

### 4. Permission Filtering

Only menus the user has permission to see are displayed:

```php
// In MenuService.php
$menuItemIds = RoleMenuPermission::whereIn('role_id', $roleIds)
    ->where('can_view', true)
    ->pluck('menu_item_id');
```

### 5. Sidebar Rendering

The `DynamicSidebar.vue` component receives menus via Inertia props and renders them with:
- Collapsible parent menus
- Active state highlighting
- Icons from FontAwesome
- Responsive design

---

## 🛠️ Usage Guide

### Creating Menus

#### Simple Parent Menu

```php
MenuItem::create([
    'name' => 'Dashboard',
    'slug' => 'dashboard',
    'icon' => 'tachometer-alt',
    'route' => 'admin.dashboard', // Optional: specify route manually
    'parent_id' => null,
    'sort_order' => 1,
    'is_active' => true
]);
```

#### Child Menu

```php
$parent = MenuItem::where('slug', 'user-management')->first();

MenuItem::create([
    'name' => 'All Users',
    'slug' => 'all-users',
    'icon' => 'users',
    'parent_id' => $parent->id,
    'sort_order' => 1,
    'is_active' => true
]);
```

### Assigning Permissions

```php
use App\Models\RoleMenuPermission;
use Spatie\Permission\Models\Role;

$adminRole = Role::where('name', 'admin')->first();
$superadminRole = Role::where('name', 'superadmin')->first();
$menu = MenuItem::where('slug', 'user-management')->first();

// Assign to multiple roles
foreach ([$adminRole, $superadminRole] as $role) {
    RoleMenuPermission::create([
        'role_id' => $role->id,
        'menu_item_id' => $menu->id,
        'can_view' => true
    ]);
}
```

### Using the Universal Layout

Update your dashboard view to use the new layout:

```php
// In your controller
use Inertia\Inertia;

public function dashboard()
{
    return Inertia::render('Admin/Dashboard');
}
```

```vue
<!-- In your Vue component -->
<script setup>
import UniversalDashboardLayout from '@/Layouts/UniversalDashboardLayout.vue';
</script>

<template>
  <UniversalDashboardLayout title="Admin Dashboard">
    <!-- Your dashboard content here -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Dashboard widgets -->
    </div>
  </UniversalDashboardLayout>
</template>
```

---

## 🎨 Customization

### Menu Icons

Use any FontAwesome icon (without the `fa-` prefix):

```php
'icon' => 'users'           // fa-users
'icon' => 'chart-line'      // fa-chart-line
'icon' => 'cog'             // fa-cog
'icon' => 'file-alt'        // fa-file-alt
```

### Route Mapping

Override auto-generated routes in `MenuService.php`:

```php
private function normalizeRouteName(string $routeName, string $menuName): string
{
    $routeMap = [
        'dashboard' => 'dashboard',
        'user-management' => 'users.index',
        'settings' => 'settings.index',
        // Add your custom mappings here
    ];

    // ...
}
```

### Custom Sidebar Styling

Modify `DynamicSidebar.vue`:

```vue
<style scoped>
/* Change sidebar width */
.w-64 {
  width: 280px; /* Wider sidebar */
}

/* Change active menu color */
.bg-blue-100 {
  background-color: #your-color;
}

/* Custom hover effects */
.hover\:bg-gray-100:hover {
  background-color: #your-hover-color;
}
</style>
```

---

## 📊 Artisan Commands

### `php artisan menu:setup`

Show menu system status and statistics.

**Output:**
```
==============================================
   Dynamic Menu System Setup
==============================================

+------------------------+-------+
| Metric                 | Count |
+------------------------+-------+
| Total Menu Items       | 15    |
| Active Menu Items      | 12    |
| Menus with Routes      | 10    |
| Menus without Routes   | 5     |
| Total Roles            | 4     |
| Role-Menu Permissions  | 32    |
+------------------------+-------+
```

### `php artisan menu:setup --sync`

Automatically generate routes for menus without routes.

**Output:**
```
Syncing menu items with routes...

✅ Generated route for 'Reports Dashboard': admin.reports-dashboard
✅ Generated route for 'Analytics': admin.analytics
⚠️  Could not generate route for 'External Link' (ID: 8)

✅ Synced 2 menu items
```

### `php artisan menu:setup --show`

Display all menus organized by role.

**Output:**
```
📋 Menus for role: admin
--------------------------------------------------
├─ Dashboard
   Route: admin.dashboard
├─ User Management
   Route: No route
  ├─ All Users
     Route: admin.users.index
  ├─ Add User
     Route: admin.users.create
```

### `php artisan menu:setup --clear`

Clear all menu caches.

**Output:**
```
Clearing menu cache...
✅ Menu cache cleared successfully!
```

---

## 🔧 Troubleshooting

### Menu Not Showing

1. **Check role permissions**:
   ```sql
   SELECT * FROM role_menu_permissions
   WHERE menu_item_id = [your_menu_id]
   AND role_id = [your_role_id];
   ```

2. **Clear cache**:
   ```bash
   php artisan menu:setup --clear
   ```

3. **Check if menu is active**:
   ```sql
   SELECT * FROM menu_items WHERE id = [your_menu_id] AND is_active = 1;
   ```

### Route Not Working

1. **Sync routes**:
   ```bash
   php artisan menu:setup --sync
   ```

2. **Check if route exists**:
   ```bash
   php artisan route:list | grep [your-route-name]
   ```

3. **Manually set route**:
   ```php
   $menu = MenuItem::find($id);
   $menu->route = 'your.actual.route';
   $menu->save();
   ```

### Sidebar Not Appearing

1. **Verify middleware is registered** in `app/Http/Kernel.php`:
   ```php
   protected $middlewareGroups = [
       'web' => [
           // ...
           \App\Http\Middleware\InjectMenuItems::class,
       ],
   ];
   ```

2. **Check layout usage**:
   ```vue
   <template>
     <UniversalDashboardLayout title="Your Page">
       <!-- Content -->
     </UniversalDashboardLayout>
   </template>
   ```

3. **Verify user is authenticated**:
   - Menus only show for logged-in users

---

## 📝 Database Schema

### `menu_items` Table

| Column      | Type         | Description                          |
|-------------|--------------|--------------------------------------|
| id          | bigint       | Primary key                          |
| name        | varchar(255) | Menu display name                    |
| slug        | varchar(255) | URL-friendly name                    |
| route       | varchar(255) | Laravel route name (nullable)        |
| icon        | varchar(255) | FontAwesome icon name (nullable)     |
| parent_id   | bigint       | Parent menu ID (nullable)            |
| sort_order  | int          | Display order (default: 0)           |
| is_active   | boolean      | Active status (default: true)        |
| description | text         | Menu description (nullable)          |
| permissions | json         | Additional permissions (nullable)    |

### `role_menu_permissions` Table

| Column       | Type    | Description                    |
|--------------|---------|--------------------------------|
| id           | bigint  | Primary key                    |
| role_id      | bigint  | Foreign key to roles table     |
| menu_item_id | bigint  | Foreign key to menu_items      |
| can_view     | boolean | View permission (default: true)|

---

## 🎯 Best Practices

### 1. Menu Naming

- Use clear, descriptive names: "User Management" not "Users"
- Keep names concise (under 25 characters)
- Use title case: "Reports Dashboard" not "reports dashboard"

### 2. Slugs

- Auto-generated from names, but you can override
- Use kebab-case: `user-management` not `user_management`
- Keep unique to avoid route conflicts

### 3. Sort Order

- Use increments of 10: 10, 20, 30...
- Allows easy insertion of new items
- Lower numbers appear first

### 4. Icons

- Use semantic icons that match functionality
- Common icons:
  - `tachometer-alt` - Dashboard
  - `users` - User management
  - `cog` - Settings
  - `chart-bar` - Reports/Analytics
  - `file-alt` - Documents

### 5. Permissions

- Grant permissions at the parent level when possible
- Child menus inherit from parents
- Use least-privilege principle

---

## 🚀 Advanced Features

### Programmatic Menu Generation

```php
use App\Services\MenuService;

$menuService = app(MenuService::class);

// Get menus for specific roles
$menus = $menuService->getMenuForRole(['admin', 'superadmin']);

// Get flat menu list (for dropdowns)
$flatMenus = $menuService->getFlatMenu(['admin']);

// Auto-register missing routes
$registeredRoutes = $menuService->autoRegisterRoutes(['admin']);
```

### Custom Route Generation

Override in `MenuItem` model:

```php
public function getCustomRouteAttribute(): string
{
    // Your custom logic here
    return "custom.{$this->slug}.route";
}
```

### Dynamic Menu Updates

Update menus in real-time:

```php
// Add new menu
$menu = MenuItem::create([...]);

// Assign to role
RoleMenuPermission::create([...]);

// Clear cache
app(MenuService::class)->clearCache();

// Menu appears immediately for users
```

---

## 📚 API Reference

### MenuService

#### `getMenuForRole($roles): array`

Get menu tree for specific role(s).

**Parameters:**
- `$roles` (string|array) - Role name(s)

**Returns:** Nested array of menu items

#### `getFlatMenu($roles): array`

Get flattened menu list for role(s).

**Parameters:**
- `$roles` (string|array) - Role name(s)

**Returns:** Flat array of menu items

#### `clearCache(): void`

Clear all menu caches.

---

## ✅ Testing Checklist

- [ ] Create a new menu item
- [ ] Assign role permissions
- [ ] Run `php artisan menu:setup --sync`
- [ ] Check menu appears in sidebar
- [ ] Click menu item - verify route works
- [ ] Test with different roles
- [ ] Test parent-child menu expansion
- [ ] Verify "Coming Soon" page for incomplete routes
- [ ] Test menu caching (check performance)
- [ ] Test sidebar collapse/expand

---

## 🎉 Success!

Your dynamic menu system is now fully set up and ready to use. Create menus in the database, assign permissions, and they'll automatically appear in the sidebar with working routes!

**Need help?** Run `php artisan menu:setup --show` to see your current menu structure.

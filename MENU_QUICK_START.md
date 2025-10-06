# Dynamic Menu System - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Run Sample Menu Setup

```bash
# Via Docker
docker exec teamo-digital-solutions-mysql-1 mysql -u sail -ppassword teamo_digital_solutions < database/sql/setup_menu_system_sample.sql

# Or via phpMyAdmin
# Copy and paste the contents of database/sql/setup_menu_system_sample.sql
```

This creates sample menus for all roles (developer, superadmin, admin, employee).

### Step 2: Clear Cache

```bash
php artisan cache:clear
php artisan menu:setup --clear
```

### Step 3: View Your Menus

```bash
php artisan menu:setup --show
```

You should see output like:

```
📋 Menus for role: admin
--------------------------------------------------
├─ Dashboard
   Route: admin.dashboard
├─ Attendance
   Route: No route
├─ Tasks
   Route: No route
├─ Leave Requests
   Route: admin.leaveRequests.index
├─ Manage Users
   Route: No route
  ├─ All Users
     Route: admin.users.index
  ├─ Add User
     Route: admin.users.create
```

### Step 4: Test in Browser

1. **Login** to your application
2. **Navigate** to your dashboard
3. **See** the sidebar automatically appear with your role's menus!

---

## ✅ What Was Created

### Backend Files

```
app/
├── Services/
│   └── MenuService.php                   # Core menu logic
├── Http/
│   └── Middleware/
│       └── InjectMenuItems.php           # Auto-inject menus
├── Models/
│   └── MenuItem.php (updated)            # Enhanced with route generation
└── Console/Commands/
    └── SetupMenuSystem.php               # Management commands
```

### Frontend Files

```
resources/js/
├── Components/
│   └── DynamicSidebar.vue                # Universal sidebar
├── Layouts/
│   └── UniversalDashboardLayout.vue      # Layout with sidebar
└── Pages/
    └── Shared/
        └── ComingSoon.vue                # Placeholder pages
```

### Database

```
menu_items                  # Menu definitions
role_menu_permissions       # Role access control
```

---

## 🎯 How to Use

### Create a New Menu Item

**Option A: Via Database**

```sql
INSERT INTO menu_items (name, slug, icon, parent_id, sort_order, is_active)
VALUES ('My New Page', 'my-new-page', 'star', NULL, 100, 1);

-- Assign to admin role
INSERT INTO role_menu_permissions (role_id, menu_item_id, can_view)
SELECT
    (SELECT id FROM roles WHERE name = 'admin'),
    (SELECT id FROM menu_items WHERE slug = 'my-new-page'),
    1;
```

**Option B: Via Tinker**

```bash
php artisan tinker
```

```php
use App\Models\MenuItem;
use App\Models\RoleMenuPermission;
use Spatie\Permission\Models\Role;

// Create menu
$menu = MenuItem::create([
    'name' => 'My New Page',
    'slug' => 'my-new-page',
    'icon' => 'star',
    'route' => 'admin.my-new-page', // Optional
    'parent_id' => null,
    'sort_order' => 100,
    'is_active' => true
]);

// Assign to admin role
$role = Role::where('name', 'admin')->first();
RoleMenuPermission::create([
    'role_id' => $role->id,
    'menu_item_id' => $menu->id,
    'can_view' => true
]);

exit
```

### Clear Cache and View

```bash
php artisan menu:setup --clear
php artisan menu:setup --show
```

### Update Your Dashboard View

Replace your existing dashboard layout with the new universal layout:

**Before:**
```vue
<template>
  <div class="dashboard">
    <!-- Your content -->
  </div>
</template>
```

**After:**
```vue
<script setup>
import UniversalDashboardLayout from '@/Layouts/UniversalDashboardLayout.vue';
</script>

<template>
  <UniversalDashboardLayout title="My Dashboard">
    <!-- Your content -->
  </UniversalDashboardLayout>
</template>
```

---

## 🎨 Available Icons

Use any FontAwesome icon (without `fa-` prefix):

- `tachometer-alt` - Dashboard
- `users` - Users
- `users-cog` - User Management
- `chart-bar` - Reports/Analytics
- `chart-line` - Analytics
- `cog` - Settings
- `tasks` - Tasks
- `calendar-check` - Attendance
- `file-alt` - Documents/Reports
- `umbrella-beach` - Leave/Vacation
- `dollar-sign` - Finance
- `exchange-alt` - Transactions
- `shield-alt` - Security
- `bars` - Menu
- `clock` - Time Tracking

[Full icon list](https://fontawesome.com/icons)

---

## 📋 Common Tasks

### Add Child Menu

```sql
-- Get parent menu ID
SET @parent_id = (SELECT id FROM menu_items WHERE slug = 'user-management');

-- Create child menu
INSERT INTO menu_items (name, slug, icon, parent_id, sort_order, is_active)
VALUES ('User Roles', 'user-roles', 'user-shield', @parent_id, 1, 1);
```

### Change Menu Icon

```sql
UPDATE menu_items
SET icon = 'new-icon-name'
WHERE slug = 'menu-slug';
```

### Deactivate Menu

```sql
UPDATE menu_items
SET is_active = 0
WHERE slug = 'menu-slug';
```

### Reorder Menus

```sql
UPDATE menu_items SET sort_order = 10 WHERE slug = 'dashboard';
UPDATE menu_items SET sort_order = 20 WHERE slug = 'users';
UPDATE menu_items SET sort_order = 30 WHERE slug = 'settings';
```

---

## 🔧 Troubleshooting

### Menu Not Showing?

```bash
# 1. Check permissions
php artisan menu:setup --show

# 2. Clear all caches
php artisan cache:clear
php artisan menu:setup --clear
php artisan view:clear

# 3. Rebuild assets
npm run build
```

### Route Not Working?

```bash
# Sync routes
php artisan menu:setup --sync

# Check if route exists
php artisan route:list | grep your-route-name
```

### Sidebar Not Appearing?

1. Make sure you're using `UniversalDashboardLayout`
2. Check that user is authenticated
3. Verify middleware is registered in `app/Http/Kernel.php`

---

## 📚 Next Steps

- Read full documentation: `DYNAMIC_MENU_SYSTEM.md`
- Customize sidebar colors in `DynamicSidebar.vue`
- Create actual pages for your menus
- Add more menu items as needed

---

## ✨ Features

✅ **Auto-route generation** - Routes are generated automatically from menu structure
✅ **Role-based access** - Each role sees only their permitted menus
✅ **Nested menus** - Support for parent-child hierarchy
✅ **Cached** - High performance with 15-minute cache
✅ **Coming Soon pages** - Automatic placeholders for incomplete features
✅ **Collapsible sidebar** - Modern, responsive design
✅ **Easy management** - Artisan commands for everything

---

**Need Help?** Run `php artisan menu:setup` to see system status!

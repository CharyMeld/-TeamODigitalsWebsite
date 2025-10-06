# Sidebar Not Showing - Troubleshooting Guide

## ✅ Steps Completed

1. ✅ Created `role_menu_permissions` table
2. ✅ Inserted 12 menu permissions for admin role
3. ✅ Integrated `DynamicSidebar` component into Admin Dashboard
4. ✅ Added logging to middleware and component
5. ✅ Rebuilt assets successfully

---

## 🔍 Debugging Steps

### Step 1: Check Browser Console

1. Open browser (Chrome/Firefox)
2. Press `F12` to open Developer Tools
3. Go to **Console** tab
4. Refresh the Admin Dashboard page
5. Look for these messages:

**Expected console output:**
```
🔍 DynamicSidebar: Received menus [Array]
🔍 DynamicSidebar: Menu count 12
```

**If you see `Menu count 0`:**
- Menus are not being injected
- Continue to Step 2

**If you DON'T see these messages:**
- Sidebar component not rendering
- Continue to Step 3

---

### Step 2: Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

**Expected log output:**
```
[datetime] local.INFO: InjectMenuItems: User roles {"roles":["admin"]}
[datetime] local.INFO: InjectMenuItems: Menus fetched {"count":12}
```

**If logs show:**
- `"roles":[]` - User has no role assigned
- `"count":0` - No menus found for this role

**Fix for no role:**
```sql
-- Check user's role
SELECT id, name, email, role FROM users WHERE email = 'your@email.com';

-- Update if needed
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
```

**Fix for count 0:**
```sql
-- Check permissions
SELECT COUNT(*) FROM role_menu_permissions
WHERE role_id = (SELECT id FROM roles WHERE name = 'admin');

-- If 0, run:
INSERT IGNORE INTO role_menu_permissions (role_id, menu_item_id, can_view, created_at, updated_at)
SELECT
    (SELECT id FROM roles WHERE name = 'admin'),
    id,
    1,
    NOW(),
    NOW()
FROM menu_items WHERE is_active = 1;
```

---

### Step 3: Verify Sidebar Component

Check if component is in the HTML:

1. In browser DevTools, go to **Elements** tab
2. Press `Ctrl+F` and search for `DynamicSidebar` or `aside`
3. Look for: `<aside class="bg-white...`

**If NOT found:**
- Component failed to render
- Check for JavaScript errors in console

**If found but empty/hidden:**
- CSS issue or component logic issue
- Check Step 4

---

### Step 4: Verify Data Structure

Open browser console and type:

```javascript
console.log($page.props.menuItems);
```

**Expected output:**
```javascript
[
  {
    id: 1,
    name: "Dashboard",
    route: "/dashboard",
    icon: "tachometer-alt",
    children: [],
    ...
  },
  ...
]
```

**If undefined:**
- Middleware not running
- Continue to Step 5

---

### Step 5: Verify Middleware Registration

Check `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\InjectMenuItems::class, // ← Should be here
    ],
];
```

**If missing:**
```bash
# Add it manually, then rebuild
npm run build
```

---

### Step 6: Clear All Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

Then refresh browser.

---

## 🔧 Manual Verification Commands

### Check Database

```sql
-- Check if table exists
SHOW TABLES LIKE 'role_menu_permissions';

-- Check permissions count
SELECT
    r.name AS role_name,
    COUNT(rmp.id) AS menu_count
FROM roles r
LEFT JOIN role_menu_permissions rmp ON r.id = rmp.role_id
GROUP BY r.id, r.name;

-- Check active menus
SELECT id, name, route, parent_id, is_active
FROM menu_items
WHERE is_active = 1;
```

### Check User Role

```sql
-- Via 'role' column
SELECT id, name, email, role FROM users;

-- Via Spatie roles table
SELECT u.name, r.name as role_name
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'your@email.com';
```

---

## 🎯 Quick Fixes

### Fix 1: Force Menu Injection

Add directly to Admin Dashboard controller:

```php
use App\Services\MenuService;

public function dashboard(MenuService $menuService)
{
    $user = Auth::user();
    $roles = [$user->role]; // or $user->roles->pluck('name')->toArray();

    $menuItems = $menuService->getMenuForRole($roles);

    return Inertia::render('Admin/Dashboard', [
        'menuItems' => $menuItems, // Force injection
        // ... other props
    ]);
}
```

### Fix 2: Test with Static Menus

Temporarily test with static data in DynamicSidebar.vue:

```javascript
const menus = computed(() => {
  // Test data
  return [
    {
      id: 1,
      name: 'Dashboard',
      route: 'admin.dashboard',
      icon: 'tachometer-alt',
      children: [],
      has_children: false
    },
    {
      id: 2,
      name: 'Users',
      route: '#',
      icon: 'users',
      children: [],
      has_children: false
    }
  ];
});
```

If this shows sidebar → Middleware issue
If this doesn't show → Component/CSS issue

---

## 📝 Checklist

- [ ] Logged in as admin user
- [ ] Browser console shows menu count > 0
- [ ] Laravel logs show "Menus fetched" with count > 0
- [ ] `role_menu_permissions` table has rows
- [ ] User has `role = 'admin'` in database
- [ ] Middleware registered in Kernel.php
- [ ] Assets rebuilt (`npm run build`)
- [ ] All caches cleared
- [ ] Browser hard refresh (Ctrl+Shift+R)

---

## 🆘 Still Not Working?

**Check these:**

1. **FontAwesome not loaded?**
   - Add to `app.blade.php`:
   ```html
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   ```

2. **Tailwind classes not working?**
   - Run: `npm run build`
   - Check `tailwind.config.js` includes Vue files

3. **Inertia not sharing props?**
   - Check `app/Http/Middleware/HandleInertiaRequests.php`
   - Make sure `share()` method doesn't override menuItems

4. **Route helper not working?**
   - Run: `php artisan ziggy:generate`

---

## 📧 Debug Output

Run this and share output:

```bash
echo "=== Database Check ==="
docker exec teamo-digital-solutions-mysql-1 mysql -u sail -ppassword teamo_digital_solutions -e "
SELECT 'Menu Items:' as info, COUNT(*) as count FROM menu_items WHERE is_active = 1
UNION ALL
SELECT 'Permissions:', COUNT(*) FROM role_menu_permissions;
" 2>&1 | grep -v "Using a password"

echo ""
echo "=== Laravel Log (last 20 lines) ==="
tail -20 storage/logs/laravel.log | grep "InjectMenuItems"

echo ""
echo "=== Build Status ==="
ls -lh public/build/manifest.json
```

---

**Next:** Once you see console messages with menu count > 0, the sidebar should appear!

-- =====================================================
-- Dynamic Menu System - Sample Setup
-- =====================================================
-- This script creates sample menu items for all roles
-- with proper hierarchical structure and permissions
-- =====================================================

-- Clean up existing sample data (optional - comment out if you want to keep existing menus)
-- DELETE FROM role_menu_permissions;
-- DELETE FROM menu_items;

-- =====================================================
-- DEVELOPER MENUS
-- =====================================================

-- Developer Dashboard
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Dashboard', 'dashboard', 'developer.dashboard', 'tachometer-alt', NULL, 10, 1, NOW(), NOW());

SET @dev_dashboard_id = LAST_INSERT_ID();

-- Developer User Management (Parent)
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('User Management', 'user-management', NULL, 'users', NULL, 20, 1, NOW(), NOW());

SET @dev_user_mgmt_id = LAST_INSERT_ID();

-- Developer User Management Children
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES
    ('All Users', 'all-users', 'developer.users.index', 'list', @dev_user_mgmt_id, 1, 1, NOW(), NOW()),
    ('Add User', 'add-user', 'developer.users.create', 'user-plus', @dev_user_mgmt_id, 2, 1, NOW(), NOW());

-- Developer Access Control
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Access Control', 'access-control', 'developer.access.index', 'shield-alt', NULL, 30, 1, NOW(), NOW());

-- Developer Menu Management
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Menu Management', 'menu-management', 'developer.menu-items.index', 'bars', NULL, 40, 1, NOW(), NOW());

-- Developer Settings
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Settings', 'settings', 'developer.settings.index', 'cog', NULL, 50, 1, NOW(), NOW());

-- =====================================================
-- SUPERADMIN MENUS
-- =====================================================

-- Superadmin Dashboard
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Dashboard', 'dashboard-sa', 'superadmin.dashboard', 'tachometer-alt', NULL, 110, 1, NOW(), NOW());

SET @sa_dashboard_id = LAST_INSERT_ID();

-- Superadmin User Management (Parent)
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('User Management', 'user-management-sa', NULL, 'users', NULL, 120, 1, NOW(), NOW());

SET @sa_user_mgmt_id = LAST_INSERT_ID();

-- Superadmin User Management Children
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES
    ('All Users', 'all-users-sa', 'superadmin.users.index', 'list', @sa_user_mgmt_id, 1, 1, NOW(), NOW()),
    ('Add User', 'add-user-sa', 'superadmin.users.create', 'user-plus', @sa_user_mgmt_id, 2, 1, NOW(), NOW());

-- Superadmin Finance (Parent)
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Finance', 'finance-sa', NULL, 'dollar-sign', NULL, 130, 1, NOW(), NOW());

SET @sa_finance_id = LAST_INSERT_ID();

-- Superadmin Finance Children
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES
    ('Reports', 'reports-sa', 'superadmin.finance.reports', 'chart-bar', @sa_finance_id, 1, 1, NOW(), NOW()),
    ('Transactions', 'transactions-sa', 'superadmin.finance.transactions', 'exchange-alt', @sa_finance_id, 2, 1, NOW(), NOW());

-- Superadmin Menu Management
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Menu Management', 'menu-management-sa', 'superadmin.menu-items.index', 'bars', NULL, 140, 1, NOW(), NOW());

-- Superadmin Settings
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Settings', 'settings-sa', 'superadmin.settings.index', 'cog', NULL, 150, 1, NOW(), NOW());

-- =====================================================
-- ADMIN MENUS
-- =====================================================

-- Admin Dashboard
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Dashboard', 'dashboard-admin', 'admin.dashboard', 'tachometer-alt', NULL, 210, 1, NOW(), NOW());

SET @admin_dashboard_id = LAST_INSERT_ID();

-- Admin Attendance
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Attendance', 'attendance-admin', NULL, 'calendar-check', NULL, 220, 1, NOW(), NOW());

-- Admin Task Management
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Tasks', 'tasks-admin', NULL, 'tasks', NULL, 230, 1, NOW(), NOW());

-- Admin Work Reports
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Work Reports', 'work-reports-admin', NULL, 'file-alt', NULL, 240, 1, NOW(), NOW());

-- Admin Leave Management
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Leave Requests', 'leave-requests-admin', 'admin.leaveRequests.index', 'umbrella-beach', NULL, 250, 1, NOW(), NOW());

-- Admin User Management (Parent)
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Manage Users', 'manage-users-admin', NULL, 'users-cog', NULL, 260, 1, NOW(), NOW());

SET @admin_user_mgmt_id = LAST_INSERT_ID();

-- Admin User Management Children
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES
    ('All Users', 'all-users-admin', 'admin.users.index', 'list', @admin_user_mgmt_id, 1, 1, NOW(), NOW()),
    ('Add User', 'add-user-admin', 'admin.users.create', 'user-plus', @admin_user_mgmt_id, 2, 1, NOW(), NOW());

-- Admin Reports
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Analytics', 'analytics-admin', NULL, 'chart-line', NULL, 270, 1, NOW(), NOW());

-- Admin Settings
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Settings', 'settings-admin', 'admin.settings', 'cog', NULL, 280, 1, NOW(), NOW());

-- =====================================================
-- EMPLOYEE MENUS
-- =====================================================

-- Employee Dashboard
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Dashboard', 'dashboard-employee', 'employee.dashboard', 'tachometer-alt', NULL, 310, 1, NOW(), NOW());

-- Employee My Attendance
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('My Attendance', 'my-attendance', NULL, 'calendar-check', NULL, 320, 1, NOW(), NOW());

-- Employee My Tasks
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('My Tasks', 'my-tasks', 'employee.tasks.index', 'tasks', NULL, 330, 1, NOW(), NOW());

-- Employee Leave Requests
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('My Leave Requests', 'my-leave-requests', 'employee.leave-requests.my', 'umbrella-beach', NULL, 340, 1, NOW(), NOW());

-- Employee Time Tracking
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('Time Tracking', 'time-tracking', 'employee.time-tracking', 'clock', NULL, 350, 1, NOW(), NOW());

-- Employee Profile
INSERT INTO menu_items (name, slug, route, icon, parent_id, sort_order, is_active, created_at, updated_at)
VALUES ('My Profile', 'my-profile', 'employee.profile.show', 'user', NULL, 360, 1, NOW(), NOW());

-- =====================================================
-- ASSIGN PERMISSIONS TO ROLES
-- =====================================================

-- Get role IDs
SET @developer_role_id = (SELECT id FROM roles WHERE name = 'developer' LIMIT 1);
SET @superadmin_role_id = (SELECT id FROM roles WHERE name = 'superadmin' LIMIT 1);
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'admin' LIMIT 1);
SET @employee_role_id = (SELECT id FROM roles WHERE name = 'employee' LIMIT 1);

-- Assign Developer Menus (sort_order 10-50)
INSERT INTO role_menu_permissions (role_id, menu_item_id, can_view, created_at, updated_at)
SELECT @developer_role_id, id, 1, NOW(), NOW()
FROM menu_items
WHERE sort_order BETWEEN 10 AND 99;

-- Assign Superadmin Menus (sort_order 110-150)
INSERT INTO role_menu_permissions (role_id, menu_item_id, can_view, created_at, updated_at)
SELECT @superadmin_role_id, id, 1, NOW(), NOW()
FROM menu_items
WHERE sort_order BETWEEN 110 AND 199;

-- Assign Admin Menus (sort_order 210-280)
INSERT INTO role_menu_permissions (role_id, menu_item_id, can_view, created_at, updated_at)
SELECT @admin_role_id, id, 1, NOW(), NOW()
FROM menu_items
WHERE sort_order BETWEEN 210 AND 299;

-- Assign Employee Menus (sort_order 310-360)
INSERT INTO role_menu_permissions (role_id, menu_item_id, can_view, created_at, updated_at)
SELECT @employee_role_id, id, 1, NOW(), NOW()
FROM menu_items
WHERE sort_order BETWEEN 310 AND 399;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Count menus per role
SELECT
    r.name AS role_name,
    COUNT(rmp.id) AS menu_count
FROM roles r
LEFT JOIN role_menu_permissions rmp ON r.id = rmp.role_id
GROUP BY r.id, r.name
ORDER BY r.name;

-- Show all menus with their assigned roles
SELECT
    mi.name AS menu_name,
    mi.route,
    mi.parent_id,
    GROUP_CONCAT(r.name ORDER BY r.name) AS assigned_roles
FROM menu_items mi
LEFT JOIN role_menu_permissions rmp ON mi.id = rmp.menu_item_id
LEFT JOIN roles r ON rmp.role_id = r.id
WHERE mi.is_active = 1
GROUP BY mi.id, mi.name, mi.route, mi.parent_id
ORDER BY mi.sort_order;

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================
SELECT '✅ Dynamic Menu System Setup Complete!' AS message;
SELECT 'Run: php artisan menu:setup --show to view menus by role' AS next_step;

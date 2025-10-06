// resources/js/utils/sidebar.js
export function getSidebarMenu(role) {
    const allMenus = {
        home: { label: "Home", route: role + ".dashboard" }, // dynamic prefix
        userManagement: { label: "User Management", route: role + ".users.index" },
        profileManagement: { label: "Profile Management", route: role + ".profile.edit" },
        finance: { label: "Finance", route: role + ".finance.reports" },
        settings: { label: "Settings", route: role + ".settings.index" },
        allUsers: { label: "All Users", route: role + ".users.index" },
        userRoles: { label: "User Roles", route: role + ".access.index" },
        permissions: { label: "Permissions", route: role + ".access.index" }, // or roles.permissions.sync
        menuManagement: { label: "Menu Management", route: role + ".menu-items.index" },
        systemSettings: { label: "System Settings", route: role + ".settings.index" },
        transactions: { label: "Transactions", route: role + ".finance.transactions" },
        reports: { label: "Reports", route: role + ".finance.reports" },
    };

    const roleMenus = {
        developer: [
            "home",
            "userManagement",
            "profileManagement",
            "finance",
            "settings",
            "allUsers",
            "userRoles",
            "permissions",
            "menuManagement",
            "systemSettings",
            "transactions",
            "reports",
        ],
        superadmin: [
            "home",
            "userManagement",
            "profileManagement",
            "finance",
            "settings",
            "allUsers",
            "userRoles",
            "menuManagement",
            "transactions",
            "reports",
        ],
        admin: [
            "home",
            "profileManagement",
            "settings",
        ],
        employee: [
            "home",
            "profileManagement",
        ],
    };

    const selected = roleMenus[role] || [];
    return selected.map((key) => allMenus[key]).filter(Boolean);
}


<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { Link } from "@inertiajs/inertia-vue3";

const stats = ref({
    totalUsers: 0,
    totalRoles: 0,
    totalMenuItems: 0,
    systemStatus: "Operational"
});

const quickActions = [
    {
        title: "Add New User",
        icon: "user-plus",
        route: "admin.users.create",
        tooltip: "Create a new user account in the system"
    },
    {
        title: "Add Menu Item",
        icon: "plus-circle",
        route: "admin.menu-items.create",
        tooltip: "Add a new menu item for navigation"
    },
    {
        title: "System Settings",
        icon: "settings",
        route: "admin.settings",
        tooltip: "View and modify system settings"
    }
];

onMounted(async () => {
    try {
        const { data } = await axios.get("/api/dashboard/superadmin-stats");
        stats.value.totalUsers = data.totalUsers;
        stats.value.totalRoles = data.totalRoles;
        stats.value.totalMenuItems = data.totalMenuItems;
        stats.value.systemStatus = data.systemStatus || "Operational";
    } catch (error) {
        console.error("Failed to load superadmin stats:", error);
    }

    if (window.feather) feather.replace();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white overflow-hidden shadow rounded-lg p-5">
        <div class="flex items-center">
          <i data-feather="users" class="h-8 w-8 text-gray-400"></i>
          <div class="ml-5 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
              <dd class="text-lg font-medium text-gray-900">{{ stats.totalUsers }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg p-5">
        <div class="flex items-center">
          <i data-feather="shield" class="h-8 w-8 text-gray-400"></i>
          <div class="ml-5 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Roles</dt>
              <dd class="text-lg font-medium text-gray-900">{{ stats.totalRoles }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg p-5">
        <div class="flex items-center">
          <i data-feather="menu" class="h-8 w-8 text-gray-400"></i>
          <div class="ml-5 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Menu Items</dt>
              <dd class="text-lg font-medium text-gray-900">{{ stats.totalMenuItems }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg p-5">
        <div class="flex items-center">
          <i data-feather="activity" class="h-8 w-8 text-green-400"></i>
          <div class="ml-5 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">System Status</dt>
              <dd class="text-lg font-medium text-green-600">{{ stats.systemStatus }}</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link
          v-for="action in quickActions"
          :key="action.title"
          :href="route(action.route)"
          class="group relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-4 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <i :data-feather="action.icon" class="mx-auto h-8 w-8 text-gray-400"></i>
          <span class="mt-2 block text-sm font-medium text-gray-900">{{ action.title }}</span>

          <!-- Tooltip -->
          <span
            class="absolute left-1/2 transform -translate-x-1/2 -top-10 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap z-10 pointer-events-none"
          >
            {{ action.tooltip }}
          </span>
        </Link>
      </div>
    </div>
  </div>
</template>


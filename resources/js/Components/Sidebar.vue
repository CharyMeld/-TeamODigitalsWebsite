<template>
  <aside class="w-64 bg-white dark:bg-gray-900 shadow-md h-screen flex flex-col">
    <!-- Sidebar Menu -->
    <nav class="flex-1 p-4 space-y-2">
      <Link
        v-for="item in finalMenu"
        :key="item.label"
        :href="safeRoute(item.route)"
        :class="[
          'block px-4 py-2 rounded transition-colors duration-150',
          isActive(item.route)
            ? 'bg-blue-100 text-blue-700 font-semibold'
            : 'hover:bg-gray-100 dark:hover:bg-gray-800'
        ]"
      >
        {{ item.label }}
      </Link>
    </nav>
  </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { getSidebarMenu } from '@/utils/sidebar';
import { route } from 'ziggy-js';

const page = usePage();
const role = page.props?.auth?.user?.role ?? 'employee';
const backendMenu = page.props?.menus ?? [];

// Normalize menu items
function normalizeMenu(menuArray) {
  return menuArray.map((item) => {
    if (typeof item === "string") {
      return {
        label: item,
        route: item
          .toLowerCase()
          .replace(/\s+/g, '.') // fallback route name
      };
    }
    if (item?.label && item?.route) return item;
    console.warn("⚠️ Unknown menu item format:", item);
    return { label: "Unknown", route: "#" };
  });
}

// Merge backend menu with frontend default if empty
const finalMenu = backendMenu.length > 0
  ? normalizeMenu(backendMenu)
  : normalizeMenu(getSidebarMenu(role));

console.log("Sidebar menu:", finalMenu);

// Safe route resolver
const safeRoute = (name, params = {}) => {
  try {
    return route(name, params);
  } catch (e) {
    console.warn(`⚠️ Route "${name}" not found`, e);
    return '#';
  }
};

// Check if current route is active
const isActive = (routeName) => {
  try {
    return route().current(routeName);
  } catch {
    return false;
  }
};
</script>

<style scoped>
aside {
  transition: all 0.3s ease-in-out;
}
</style>


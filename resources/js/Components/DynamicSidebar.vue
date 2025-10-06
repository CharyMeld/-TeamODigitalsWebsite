<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['toggle']);

const page = usePage();
const sidebarCollapsed = ref(props.collapsed);
const openMenus = ref(new Set());

// Get menus from page props (shared as 'menus' from HandleInertiaRequests)
const menus = computed(() => {
  const menuItems = page.props.menus || [];
  console.log('🔍 DynamicSidebar: Received menus', menuItems);
  console.log('🔍 DynamicSidebar: Menu count', menuItems.length);
  console.log('🔍 DynamicSidebar: Full page props', page.props);
  return menuItems;
});

// Get current user role
const userRole = computed(() => {
  return page.props.auth?.user?.role || 'employee';
});

// Toggle sidebar
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  if (sidebarCollapsed.value) {
    openMenus.value.clear();
  }
  emit('toggle', sidebarCollapsed.value);
};

// Toggle parent menu
const toggleMenu = (menuId) => {
  if (openMenus.value.has(menuId)) {
    openMenus.value.delete(menuId);
  } else {
    openMenus.value.add(menuId);
  }
};

// Check if menu is open
const isMenuOpen = (menuId) => {
  return openMenus.value.has(menuId);
};

// Safe route helper
const getRoute = (menu) => {
  if (!menu.route) {
    console.log(`⚠️ Menu "${menu.name}" has no route, returning #`);
    return '#';
  }

  try {
    // If route is already a full URL, use it
    if (menu.route.startsWith('http') || menu.route.startsWith('/')) {
      console.log(`✅ Menu "${menu.name}" using absolute URL: ${menu.route}`);
      return menu.route;
    }

    // Try to use Laravel route helper via Ziggy
    if (window.route && typeof window.route === 'function') {
      const generatedRoute = route(menu.route);
      console.log(`✅ Menu "${menu.name}" route "${menu.route}" → ${generatedRoute}`);
      return generatedRoute;
    }

    // Fallback: construct URL based on role
    const fallbackRoute = `/${userRole.value}/${menu.slug}`;
    console.log(`⚠️ Menu "${menu.name}" using fallback route: ${fallbackRoute}`);
    return fallbackRoute;
  } catch (error) {
    console.error(`❌ Route error for menu "${menu.name}" (${menu.route}):`, error);
    return '#';
  }
};

// Check if current route is active
const isActive = (menu) => {
  try {
    const currentUrl = window.location.pathname;
    const menuUrl = getRoute(menu);

    // Exact match
    if (currentUrl === menuUrl) return true;

    // Parent menu check - if any child is active
    if (menu.children && menu.children.length > 0) {
      return menu.children.some(child => isActive(child));
    }

    return false;
  } catch {
    return false;
  }
};

// Get icon class
const getIconClass = (icon) => {
  if (!icon) return 'fa-circle';

  // If icon already has 'fa-' prefix, use it
  if (icon.startsWith('fa-')) return icon;

  // Otherwise add prefix
  return `fa-${icon}`;
};
</script>

<template>
  <aside
    :class="[
      'bg-white dark:bg-gray-900 shadow-lg transition-all duration-300 flex flex-col h-screen',
      sidebarCollapsed ? 'w-20' : 'w-64'
    ]"
  >
    <!-- Sidebar Header with Logo and Toggle -->
    <div class="flex items-center justify-between h-20 border-b border-gray-200 dark:border-gray-700 px-4">
      <Link v-show="!sidebarCollapsed" :href="route('dashboard')" class="flex items-center space-x-2">
        <ApplicationMark class="h-14 w-auto" />
      </Link>

      <button
        @click="toggleSidebar"
        class="p-2 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            :d="sidebarCollapsed ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7'"
          />
        </svg>
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4 px-2">
      <ul class="space-y-1">
        <li v-for="menu in menus" :key="menu.id">
          <!-- Menu with children (collapsible) -->
          <div v-if="menu.has_children">
            <button
              @click="toggleMenu(menu.id)"
              :class="[
                'w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                sidebarCollapsed ? 'justify-center' : '',
                isActive(menu)
                  ? 'bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
              :title="sidebarCollapsed ? menu.name : ''"
            >
              <div class="flex items-center">
                <i :class="['fas', getIconClass(menu.icon), sidebarCollapsed ? 'text-lg' : 'mr-3']"></i>
                <span v-show="!sidebarCollapsed">{{ menu.name }}</span>
              </div>
              <i
                v-show="!sidebarCollapsed"
                :class="['fas', 'text-xs', isMenuOpen(menu.id) ? 'fa-chevron-up' : 'fa-chevron-down']"
              ></i>
            </button>

            <!-- Children -->
            <ul
              v-if="!sidebarCollapsed && isMenuOpen(menu.id)"
              class="ml-4 mt-1 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-2"
            >
              <li v-for="child in menu.children" :key="child.id">
                <Link
                  :href="getRoute(child)"
                  :class="[
                    'flex items-center px-3 py-2 text-sm rounded-lg transition-colors duration-200',
                    isActive(child)
                      ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 font-medium'
                      : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100'
                  ]"
                >
                  <i :class="['fas', getIconClass(child.icon), 'mr-3', 'text-xs']"></i>
                  {{ child.name }}
                </Link>
              </li>
            </ul>
          </div>

          <!-- Menu without children (direct link) -->
          <Link
            v-else
            :href="getRoute(menu)"
            :class="[
              'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
              sidebarCollapsed ? 'justify-center' : '',
              isActive(menu)
                ? 'bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
            :title="sidebarCollapsed ? menu.name : ''"
          >
            <i :class="['fas', getIconClass(menu.icon), sidebarCollapsed ? 'text-lg' : 'mr-3']"></i>
            <span v-show="!sidebarCollapsed">{{ menu.name }}</span>
          </Link>
        </li>
      </ul>

      <!-- Empty State -->
      <div v-if="menus.length === 0" class="text-center py-8 px-4">
        <i class="fas fa-bars text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
        <p v-show="!sidebarCollapsed" class="text-sm text-gray-500 dark:text-gray-400">
          No menu items available
        </p>
      </div>
    </nav>

    <!-- Sidebar Footer (Optional) -->
    <div
      v-show="!sidebarCollapsed"
      class="border-t border-gray-200 dark:border-gray-700 p-3"
    >
      <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
        <i class="fas fa-user-circle mr-1"></i>
        {{ userRole }} Dashboard
      </div>
    </div>
  </aside>
</template>

<style scoped>
/* Smooth transitions */
aside {
  transition: width 0.3s ease-in-out;
}

/* Custom scrollbar */
nav::-webkit-scrollbar {
  width: 6px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Dark mode scrollbar */
.dark nav::-webkit-scrollbar-thumb {
  background: #4a5568;
}

.dark nav::-webkit-scrollbar-thumb:hover {
  background: #2d3748;
}
</style>

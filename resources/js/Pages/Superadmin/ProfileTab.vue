<script setup>
import { computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AdminProfileTab from '@/Pages/Admin/ProfileTab.vue';

const page = usePage();

const props = defineProps({
  quickActions: {
    type: Array,
    default: () => []
  }
});

// Safe navigation using URL, route name, or action function
const navigateTo = (actionOrUrl) => {
  console.log('navigateTo called with:', actionOrUrl);

  // If it's a function, execute it
  if (typeof actionOrUrl === 'function') {
    actionOrUrl();
    return;
  }

  // If it starts with /, treat as URL
  if (typeof actionOrUrl === 'string' && actionOrUrl.startsWith('/')) {
    console.log('Visiting URL:', actionOrUrl);
    router.visit(actionOrUrl);
    return;
  }

  // Otherwise treat as route name
  try {
    if (typeof route !== "function") {
      console.error("Ziggy route() function not available");
      return;
    }

    console.log('Checking if route exists:', actionOrUrl);

    if (!window.Ziggy || !window.Ziggy.routes[actionOrUrl]) {
      console.error(`Route "${actionOrUrl}" not found in Ziggy routes`);
      return;
    }

    const url = route(actionOrUrl);
    console.log('Generated URL:', url);
    router.visit(url);
  } catch (error) {
    console.error('Navigation error:', error);
  }
};

onMounted(() => {
  console.log('ProfileTab mounted');
  console.log('Quick actions:', props.quickActions);
  console.log('Ziggy routes available:', !!window.Ziggy);
});
</script>

<template>
  <div>
    <!-- Admin Profile Tab Content -->
    <AdminProfileTab />

    <!-- Quick Actions Section -->
    <div class="m-4 mt-6">
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="action in quickActions"
            :key="action.title"
            @click="navigateTo(action.url || action.routeName || action.action)"
            :title="action.tooltip"
            class="group relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-6 text-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-200"
          >
            <div class="flex flex-col items-center">
              <i :class="['fas', 'fa-' + action.icon, 'text-3xl', 'text-gray-500', 'group-hover:text-blue-600', 'mb-3']"></i>
              <span class="text-sm font-medium text-gray-900 group-hover:text-blue-700">{{ action.title }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

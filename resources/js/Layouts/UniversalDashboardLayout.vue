<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import DynamicSidebar from '@/Components/DynamicSidebar.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  }
});

const page = usePage();
const sidebarCollapsed = ref(false);

const handleSidebarToggle = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <Head :title="title" />

    <div class="flex h-screen overflow-hidden">
      <!-- Dynamic Sidebar -->
      <DynamicSidebar
        :collapsed="sidebarCollapsed"
        @toggle="handleSidebarToggle"
      />

      <!-- Main Content Area -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between h-16 px-6">
            <!-- Page Title -->
            <div class="flex items-center space-x-4">
              <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
              </h1>
            </div>

            <!-- Right Side - User Menu -->
            <div class="flex items-center space-x-4">
              <!-- Notifications (Optional) -->
              <button
                class="p-2 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                title="Notifications"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                  />
                </svg>
              </button>

              <!-- User Dropdown -->
              <Dropdown align="right" width="48">
                <template #trigger>
                  <button
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  >
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                      {{ page.props.auth?.user?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <span class="hidden md:block">{{ page.props.auth?.user?.name || 'User' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                </template>

                <template #content>
                  <!-- User Info -->
                  <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ page.props.auth?.user?.name || 'User' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ page.props.auth?.user?.email || '' }}
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-medium">
                      {{ page.props.auth?.user?.role?.toUpperCase() || 'USER' }}
                    </p>
                  </div>

                  <!-- Dropdown Links -->
                  <DropdownLink :href="route('profile.index')">
                    <i class="fas fa-user mr-2"></i> Profile
                  </DropdownLink>

                  <DropdownLink
                    v-if="['developer', 'superadmin', 'admin'].includes(page.props.auth?.user?.role)"
                    :href="route('dashboard')"
                  >
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                  </DropdownLink>

                  <div class="border-t border-gray-100 dark:border-gray-700"></div>

                  <DropdownLink :href="route('logout')" method="post" as="button">
                    <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                  </DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
          <div class="container mx-auto px-6 py-8">
            <slot />
          </div>
        </main>

        <!-- Footer (Optional) -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4 px-6">
          <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <p>&copy; {{ new Date().getFullYear() }} Teamo Digital Solutions. All rights reserved.</p>
            <p>Version 1.0.0</p>
          </div>
        </footer>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add any custom styles here */
</style>

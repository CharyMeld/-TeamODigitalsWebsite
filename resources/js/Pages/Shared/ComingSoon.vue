<script setup>
import { Link } from '@inertiajs/vue3';
import UniversalDashboardLayout from '@/Layouts/UniversalDashboardLayout.vue';

defineProps({
  menuName: {
    type: String,
    default: 'This Page'
  },
  message: {
    type: String,
    default: 'This page is under development and will be available soon.'
  }
});
</script>

<template>
  <UniversalDashboardLayout :title="menuName">
    <div class="flex items-center justify-center min-h-[calc(100vh-200px)]">
      <div class="text-center">
        <!-- Icon -->
        <div class="mb-6">
          <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-blue-100 dark:bg-blue-900">
            <svg class="w-12 h-12 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">
          Coming Soon
        </h1>

        <!-- Menu Name -->
        <h2 class="text-2xl font-semibold text-blue-600 dark:text-blue-400 mb-4">
          {{ menuName }}
        </h2>

        <!-- Message -->
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
          {{ message }}
        </p>

        <!-- Additional Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 max-w-md mx-auto mb-8">
          <p class="text-sm text-gray-700 dark:text-gray-300">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-2"></i>
            This page has been automatically generated because the menu item exists but the corresponding page hasn't been created yet.
          </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
          <Link
            :href="route('dashboard')"
            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Go to Dashboard
          </Link>

          <button
            @click="$router.back()"
            class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Go Back
          </button>
        </div>

        <!-- Help Text -->
        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400">
          Need help? Contact your administrator or check the documentation.
        </p>
      </div>
    </div>
  </UniversalDashboardLayout>
</template>

<style scoped>
/* Add any custom animations here */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.text-center > * {
  animation: fadeIn 0.6s ease-out;
}
</style>

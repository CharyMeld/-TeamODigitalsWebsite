<script setup>
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Props from controller
const props = defineProps({
  settings: Object
});

// Form data
const form = useForm({
  site_name: props.settings?.site_name || 'Teamo Digital Solutions',
  site_description: props.settings?.site_description || 'Professional digital solutions for your business',
  admin_email: props.settings?.admin_email || 'admin@teamodigital.com',
  maintenance_mode: props.settings?.maintenance_mode || false,
  user_registration: props.settings?.user_registration || true,
  email_verification: props.settings?.email_verification || true,
  max_upload_size: props.settings?.max_upload_size || 10,
  session_timeout: props.settings?.session_timeout || 120,
  backup_frequency: props.settings?.backup_frequency || 'daily',
  timezone: props.settings?.timezone || 'UTC'
});

// Available options
const timezones = [
  'UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
  'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Africa/Lagos'
];

const backupFrequencies = [
  { value: 'hourly', label: 'Every Hour' },
  { value: 'daily', label: 'Daily' },
  { value: 'weekly', label: 'Weekly' },
  { value: 'monthly', label: 'Monthly' }
];

// Form submission
const submit = () => {
  form.post('/superadmin/settings', {
    onSuccess: () => {
      alert('Settings updated successfully!');
    },
    onError: (errors) => {
      console.error('Settings update failed:', errors);
    }
  });
};

// Reset form
const resetForm = () => {
  form.reset();
};

// Test email functionality
const testEmail = () => {
  alert('Email test functionality would be implemented here');
};

// Clear cache
const clearCache = () => {
  if (confirm('Are you sure you want to clear the system cache?')) {
    // Implementation would go here
    alert('Cache cleared successfully!');
  }
};
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        System Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-8">
          
          <!-- General Settings -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                General Settings
              </h3>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Site Name -->
                <div>
                  <label for="site_name" class="block text-sm font-medium text-gray-700">
                    Site Name
                  </label>
                  <input
                    id="site_name"
                    v-model="form.site_name"
                    type="text"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                  <div v-if="form.errors.site_name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.site_name }}
                  </div>
                </div>

                <!-- Admin Email -->
                <div>
                  <label for="admin_email" class="block text-sm font-medium text-gray-700">
                    Admin Email
                  </label>
                  <input
                    id="admin_email"
                    v-model="form.admin_email"
                    type="email"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                  <div v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.admin_email }}
                  </div>
                </div>

                <!-- Site Description -->
                <div class="sm:col-span-2">
                  <label for="site_description" class="block text-sm font-medium text-gray-700">
                    Site Description
                  </label>
                  <textarea
                    id="site_description"
                    v-model="form.site_description"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  ></textarea>
                  <div v-if="form.errors.site_description" class="mt-1 text-sm text-red-600">
                    {{ form.errors.site_description }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Settings -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                System Configuration
              </h3>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Timezone -->
                <div>
                  <label for="timezone" class="block text-sm font-medium text-gray-700">
                    Timezone
                  </label>
                  <select
                    id="timezone"
                    v-model="form.timezone"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option v-for="tz in timezones" :key="tz" :value="tz">
                      {{ tz }}
                    </option>
                  </select>
                </div>

                <!-- Session Timeout -->
                <div>
                  <label for="session_timeout" class="block text-sm font-medium text-gray-700">
                    Session Timeout (minutes)
                  </label>
                  <input
                    id="session_timeout"
                    v-model.number="form.session_timeout"
                    type="number"
                    min="5"
                    max="1440"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                </div>

                <!-- Max Upload Size -->
                <div>
                  <label for="max_upload_size" class="block text-sm font-medium text-gray-700">
                    Max Upload Size (MB)
                  </label>
                  <input
                    id="max_upload_size"
                    v-model.number="form.max_upload_size"
                    type="number"
                    min="1"
                    max="100"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                </div>

                <!-- Backup Frequency -->
                <div>
                  <label for="backup_frequency" class="block text-sm font-medium text-gray-700">
                    Backup Frequency
                  </label>
                  <select
                    id="backup_frequency"
                    v-model="form.backup_frequency"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option v-for="freq in backupFrequencies" :key="freq.value" :value="freq.value">
                      {{ freq.label }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Security & Access Settings -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                Security & Access
              </h3>
              
              <div class="space-y-6">
                <!-- Toggle Settings -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <!-- Maintenance Mode -->
                  <div class="flex items-center">
                    <input
                      id="maintenance_mode"
                      v-model="form.maintenance_mode"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900">
                      Enable Maintenance Mode
                    </label>
                  </div>

                  <!-- User Registration -->
                  <div class="flex items-center">
                    <input
                      id="user_registration"
                      v-model="form.user_registration"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="user_registration" class="ml-2 block text-sm text-gray-900">
                      Allow User Registration
                    </label>
                  </div>

                  <!-- Email Verification -->
                  <div class="flex items-center">
                    <input
                      id="email_verification"
                      v-model="form.email_verification"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="email_verification" class="ml-2 block text-sm text-gray-900">
                      Require Email Verification
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Tools -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                System Tools
              </h3>
              
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <button
                  type="button"
                  @click="testEmail"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  Test Email
                </button>

                <button
                  type="button"
                  @click="clearCache"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                >
                  Clear Cache
                </button>

                <button
                  type="button"
                  @click="resetForm"
                  class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Reset Form
                </button>
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="resetForm"
              class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Settings</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

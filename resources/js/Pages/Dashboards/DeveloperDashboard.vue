<script setup>
import { onMounted } from "vue";

onMounted(() => {
  // Initialize Feather icons
  if (window.feather) feather.replace();
});

const openDevelopmentConsole = () => {
  // Implement your development console logic here
  alert("Opening Development Console...");
};

const editProfile = () => {
  // Implement profile editing logic here
  alert("Opening Profile Editor...");
};
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Development Tools -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Development Tools</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Access development resources and tools.</p>
          </div>
          <div class="mt-5">
            <button @click="openDevelopmentConsole"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <i data-feather="code" class="mr-2 h-4 w-4"></i>
              Development Console
            </button>
          </div>
        </div>
      </div>

      <!-- Profile Management -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Management</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Update your profile and preferences.</p>
          </div>
          <div class="mt-5">
            <button @click="editProfile"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <i data-feather="user" class="mr-2 h-4 w-4"></i>
              Edit Profile
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


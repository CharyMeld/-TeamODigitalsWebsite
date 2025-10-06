<script setup>
import { onMounted } from "vue";

onMounted(() => {
  // Initialize Feather icons
  if (window.feather) feather.replace();
});

const updateProfile = () => {
  // Implement profile update logic here
  alert("Opening Profile Update Form...");
};
</script>

<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">Welcome to Your Dashboard</h3>
      <div class="mt-2 max-w-xl text-sm text-gray-500">
        <p>Here you can manage your profile and access available resources.</p>
      </div>
      <div class="mt-5 sm:flex sm:items-center sm:justify-between">
        <div class="max-w-xl text-sm text-gray-500">
          <p>Get started by updating your profile information.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <button @click="updateProfile"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i data-feather="user" class="mr-2 h-4 w-4"></i>
            Update Profile
          </button>
        </div>
      </div>
    </div>
  </div>
</template>


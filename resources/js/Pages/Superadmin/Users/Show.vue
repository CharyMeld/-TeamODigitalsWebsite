<!-- resources/js/Pages/Superadmin/Users/Show.vue -->
<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3';

defineProps({
  user: Object,
});
</script>

<template>
  <Head title="View User" />

  <div class="max-w-4xl mx-auto py-10">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">User Details</h1>
      <Link
        :href="route('superadmin.users.index')"
        class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800"
      >
        Back to Users
      </Link>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div>
          <h3 class="text-sm font-semibold text-gray-500">Full Name</h3>
          <p class="text-lg text-gray-800">{{ user.name }}</p>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-gray-500">Email</h3>
          <p class="text-lg text-gray-800">{{ user.email }}</p>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-gray-500">Role</h3>
          <p class="text-lg text-gray-800">{{ user.roles?.[0]?.name ?? 'N/A' }}</p>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-gray-500">Phone</h3>
          <p class="text-lg text-gray-800">{{ user.phone ?? 'N/A' }}</p>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-gray-500">Department</h3>
          <p class="text-lg text-gray-800">{{ user.department ?? 'N/A' }}</p>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-gray-500">Position</h3>
          <p class="text-lg text-gray-800">{{ user.position ?? 'N/A' }}</p>
        </div>

      </div>

      <div class="mt-6 flex space-x-4">
        <Link
          :href="route('superadmin.users.edit', user.id)"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Edit
        </Link>
        <Link
          :href="route('superadmin.users.index')"
          class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
        >
          Back
        </Link>
      </div>
    </div>
  </div>
</template>


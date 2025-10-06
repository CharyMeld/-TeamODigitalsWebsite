<script setup>
import { Link } from '@inertiajs/inertia-vue3'
const props = defineProps({
  user: { type: Object, required: true }
})
</script>

<template>
  <div class="p-6 max-w-3xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">User Details</h1>
      <div>
        <Link :href="`/developer/users/${user.id}/edit`" class="text-blue-600 mr-2">Edit</Link>
        <Link href="/developer/users" class="text-gray-600">Back</Link>
      </div>
    </div>

    <div class="bg-white p-4 rounded shadow space-y-3">
      <div><strong>Name:</strong> {{ user.name }}</div>
      <div><strong>Email:</strong> {{ user.email }}</div>
      <div><strong>Username:</strong> {{ user.username }}</div>
      <div><strong>Role:</strong> {{ user.role }}</div>
      <div><strong>Employee ID:</strong> {{ user.employee_id }}</div>
      <div><strong>Status:</strong> {{ user.status }}</div>
      <div><strong>Department:</strong> {{ user.department }}</div>
      <div><strong>Phone:</strong> {{ user.phone }}</div>
      <div><strong>Address:</strong> {{ user.address }}</div>
      <div v-if="user.profile_photo_path">
        <strong>Photo:</strong>
        <div class="mt-2"><img :src="user.profile_photo_path" alt="profile" class="h-24 w-24 object-cover rounded" /></div>
      </div>
    </div>
  </div>
</template>


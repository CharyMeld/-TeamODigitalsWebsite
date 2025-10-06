<script setup>
import { Link } from '@inertiajs/inertia-vue3'

const props = defineProps({
  user: { type: Object, required: true }
})
</script>

<template>
  <!-- Add v-if="user" to a container element -->
  <div v-if="user" class="p-6 max-w-2xl"> 
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">My Profile</h1>
      <Link href="/employee/profile/edit" class="text-blue-600">Edit Profile</Link>
    </div>

    <div class="bg-white rounded shadow p-4 space-y-2">
      <div class="flex items-center gap-4">
       <img 
          v-if="user.profile_photo_url" 
          :src="user.profile_photo_url" 
          alt="Profile photo"
          class="h-24 w-24 object-cover rounded" 
        />

        <div>
          <div class="text-lg font-medium">{{ user.name }}</div>
          <div class="text-sm">{{ user.email }}</div>
          <div class="text-sm">{{ user.phone }}</div>
        </div>
      </div>

      <div><strong>Employee ID:</strong> {{ user.employee_id }}</div>
      <div><strong>Department:</strong> {{ user.department }}</div>
      <div><strong>Role:</strong> {{ user.role }}</div>
      <div><strong>Address:</strong> {{ user.address }}</div>
      <div><strong>Emergency Contact:</strong> {{ user.emergency_contact }}</div>
      <div><strong>Date of Birth:</strong> {{ user.date_of_birth }}</div>
      <div><strong>Hire Date:</strong> {{ user.hire_date }}</div>
    </div>
  </div>
</template>



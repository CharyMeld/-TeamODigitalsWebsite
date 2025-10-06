<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  user: { type: Object, required: true }
})

// Track active tab (default = "Profile")
const activeTab = ref("Profile")

// Tab options
const tabs = ["Profile"]

// Function to switch tabs
const switchTab = (tab) => {
  activeTab.value = tab
}
</script>

<template>
  <div v-if="user" class="p-6 max-w-4xl mx-auto">
    <!-- Tabs Navigation -->
    <div class="flex border-b mb-4">
      <button 
        v-for="tab in tabs" 
        :key="tab" 
        @click="switchTab(tab)"
        class="px-4 py-2 text-sm font-medium focus:outline-none"
        :class="activeTab === tab 
          ? 'border-b-2 border-blue-600 text-blue-600' 
          : 'text-gray-600 hover:text-blue-600'"
      >
        {{ tab }}
      </button>
    </div>

    <!-- Profile Tab -->
    <div v-if="activeTab === 'Profile'" class="space-y-4">
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
  </div>
</template>


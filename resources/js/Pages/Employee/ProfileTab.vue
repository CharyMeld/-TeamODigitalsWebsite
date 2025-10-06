<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  user: { type: Object, required: true }
})

// Computed property for profile photo with fallback
const profilePhotoUrl = computed(() => {
  // If user has profile_photo_url, use it
  if (props.user.profile_photo_url) {
    // If it's already a full URL (http/https or starts with /storage), use as-is
    if (props.user.profile_photo_url.startsWith('http') || props.user.profile_photo_url.startsWith('/storage')) {
      return props.user.profile_photo_url
    }
    // Otherwise prepend /storage/
    return `/storage/${props.user.profile_photo_url}`
  }

  // Fallback: Generate default avatar URL using UI Avatars
  const initials = userInitials.value
  const name = encodeURIComponent(props.user.name || 'User')
  return `https://ui-avatars.com/api/?name=${name}&color=7F9CF5&background=EBF4FF&size=128`
})

// Generate initials for fallback avatar
const userInitials = computed(() => {
  const name = props.user.name || ''
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return 'Not specified'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch (e) {
    return dateString
  }
}

// Get status badge color
const getStatusColor = (status) => {
  const statusLower = (status || '').toLowerCase()
  return {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
  }[statusLower] || 'bg-gray-100 text-gray-800'
}

// CV download function
const downloadCV = (userId) => {
  const url = route('employee.cv.download', userId) // Make sure route name exists
  window.open(url, '_blank')
}

// Handle image loading errors - fallback to initials avatar
const handleImageError = (event) => {
  console.warn('Failed to load profile image, using fallback')
  const initials = userInitials.value
  const name = encodeURIComponent(props.user.name || 'User')
  event.target.src = `https://ui-avatars.com/api/?name=${name}&color=7F9CF5&background=EBF4FF&size=128`
}
</script>

<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-600 mt-1">Manage your personal information and settings</p>
      </div>
      <Link 
        :href="route('employee.profile.edit')" 
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Edit Profile
      </Link>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <!-- Cover Section -->
      <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-32 relative">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
      </div>

      <!-- Profile Header -->
      <div class="relative px-6 pb-6">
        <!-- Profile Picture -->
        <div class="flex items-end -mt-16 mb-6">
          <div class="relative">
            <!-- Profile Image - Always show, with fallback handling -->
            <img
              :src="profilePhotoUrl"
              :alt="`${user.name}'s profile photo`"
              class="h-32 w-32 object-cover rounded-full border-4 border-white shadow-lg bg-white"
              @error="handleImageError"
            />

            <!-- Online status indicator -->
            <div class="absolute bottom-2 right-2 h-6 w-6 bg-green-400 rounded-full border-2 border-white"></div>
          </div>

          <!-- Basic Info -->
          <div class="ml-6 pb-2">
            <h2 class="text-2xl font-bold text-gray-900">{{ user.name || 'No Name' }}</h2>
            <p class="text-gray-600 text-lg">{{ user.role || 'Employee' }}</p>
            <div class="flex items-center mt-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusColor(user.status)">
                <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                  <circle cx="4" cy="4" r="3" />
                </svg>
                {{ user.status || 'Active' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="text-sm font-medium text-gray-900">{{ user.email || 'Not specified' }}</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Phone</p>
              <p class="text-sm font-medium text-gray-900">{{ user.phone || 'Not specified' }}</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Employee ID</p>
              <p class="text-sm font-medium text-gray-900">{{ user.employee_id || 'Not assigned' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Information -->
      <div class="border-t border-gray-200">
        <div class="px-6 py-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Personal Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Department</label>
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <span class="text-gray-900">{{ user.department || 'Not specified' }}</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Date of Birth</label>
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-gray-900">{{ formatDate(user.date_of_birth) }}</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Hire Date</label>
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 00-2 2H8a2 2 0 00-2-2V6m8 0H8m0 0v.01M8 6v6h8V6M8 12l4 4 4-4" />
                  </svg>
                  <span class="text-gray-900">{{ formatDate(user.hire_date) }}</span>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Address</label>
                <div class="flex items-start">
                  <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="text-gray-900">{{ user.address || 'Not specified' }}</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Emergency Contact</label>
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 012-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <span class="text-gray-900">{{ user.emergency_contact || 'Not specified' }}</span>
                </div>
              </div>

              <div v-if="user.salary" class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-500 mb-1">Salary</label>
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                  <span class="text-gray-900 font-medium">${{ Number(user.salary).toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-500">
            Last updated: {{ formatDate(user.updated_at) }}
          </div>
          <div class="flex space-x-3">
            <Link 
              :href="route('employee.profile.edit')" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit Profile
            </Link>
            <button @click="downloadCV(user.id)" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v8m0 0l-3-3m3 3l3-3M12 4v8" />
              </svg>
              Download CV
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


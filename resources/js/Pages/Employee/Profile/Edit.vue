<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({ 
  user: { type: Object, required: true } 
})

// Defensive check for user data
if (!props.user) {
  console.error('User prop is missing or undefined')
}

// Initialize form with safer fallbacks
const form = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  username: props.user?.username || '',
  role: props.user?.role || 'employee',
  gender: props.user?.gender || '',
  department: props.user?.department || '',
  phone: props.user?.phone || '',
  address: props.user?.address || '',
  marital_status: props.user?.marital_status || '',
  date_of_birth: props.user?.date_of_birth || '',
  local_government: props.user?.local_government || '',
  state: props.user?.state || '',
  country: props.user?.country || 'Nigeria',
  emergency_contact: props.user?.emergency_contact || '',
  salary: props.user?.salary || '',
  hire_date: props.user?.hire_date || '',
  status: props.user?.status || '',
  password: '',
  password_confirmation: '',
  photo: null
})

// Preview existing or new photo
const photoPreview = ref(props.user?.profile_image ? `/storage/${props.user.profile_image}` : null)

function handlePhoto(e) {
  const file = e.target.files[0] ?? null
  form.photo = file

  if (file) {
    const reader = new FileReader()
    reader.onload = e => (photoPreview.value = e.target.result)
    reader.readAsDataURL(file)
  } else {
    photoPreview.value = props.user?.profile_image ? `/storage/${props.user.profile_image}` : null
  }
}

// Log form data on mount for debugging
onMounted(() => {
  console.log('Component mounted with user:', props.user)
  console.log('Form initialized with:', {
    name: form.name,
    email: form.email,
    username: form.username
  })
})

//  Submit function - CORRECTED VERSION
function submit() {
  // Final check and logging
  console.log("Submitting form with data:", {
    name: form.name,
    email: form.email,
    username: form.username,
    hasName: !!form.name,
    hasEmail: !!form.email,
    hasUsername: !!form.username
  })

  // Don't prevent submission - let Laravel handle validation
  // But log if fields appear empty
  if (!form.name || !form.email || !form.username) {
    console.warn('Some required fields appear empty:', {
      name: form.name || 'EMPTY',
      email: form.email || 'EMPTY',
      username: form.username || 'EMPTY'
    })
  }

  // ALWAYS use form.post() with forceFormData for file uploads
  // Add _method to the form data itself, not as an option
  form.transform((data) => ({
    ...data,
    _method: 'PUT'  // Add the method override to form data
  })).post(route('profile.update'), {
    preserveScroll: true,
    forceFormData: true,  // This ensures multipart/form-data
    onBefore: () => console.log('Form submission starting...'),
    onStart: () => console.log('Form submission started'),
    onProgress: (progress) => console.log('Upload progress:', progress),
    onSuccess: () => {
      console.log('Update successful')
      form.reset('password', 'password_confirmation', 'photo')
    },
    onError: (errors) => {
      console.error('Validation errors received:', errors)
      console.log('Form data at time of error:', form.data())
    },
    onFinish: () => console.log('Request finished')
  })
}

// Navigate back function
function goBack() {
  window.history.back()
}
</script>

<template>
  <AppLayout :tabs="[]">
    <div class="p-6 max-w-3xl mx-auto">
      <!-- Show error if user data is missing -->
      <div v-if="!user" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <strong>Error:</strong> User data is not available. Please try refreshing the page or contact support.
      </div>

      <div v-else>
        <div class="flex items-center justify-between mb-4">
          <h1 class="text-xl font-semibold">Edit Profile</h1>
          <button @click="goBack" type="button" class="text-sm text-blue-600 hover:text-blue-800 underline">
            ← Back to Profile
          </button>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ $page.props.flash.error }}
        </div>

        <!-- Validation Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
          <strong>Please fix the following errors:</strong>
          <ul class="list-disc list-inside mt-2">
            <li v-for="(error, field) in form.errors" :key="field">
              <strong>{{ field.replace('_', ' ') }}:</strong> {{ error }}
            </li>
          </ul>
        </div>

        <!-- Profile Form -->
        <form @submit.prevent="submit" class="bg-white p-6 rounded shadow space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
              <label for="name" class="block mb-1 text-sm font-medium">Name <span class="text-red-500">*</span></label>
              <input 
                id="name" 
                v-model="form.name" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.name }"
                required 
              />
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block mb-1 text-sm font-medium">Email <span class="text-red-500">*</span></label>
              <input 
                id="email" 
                v-model="form.email" 
                type="email" 
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.email }"
                required 
              />
              <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
            </div>

            <!-- Username -->
            <div>
              <label for="username" class="block mb-1 text-sm font-medium">Username <span class="text-red-500">*</span></label>
              <input 
                id="username" 
                v-model="form.username" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.username }"
                required 
              />
              <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</div>
            </div>

            <!-- Employee ID (readonly) -->
            <div>
              <label for="employee_id" class="block mb-1 text-sm font-medium">Employee ID</label>
              <input
                id="employee_id"
                :value="user?.employee_id || 'Not assigned'"
                type="text"
                class="border p-2 w-full rounded bg-gray-100 cursor-not-allowed"
                readonly
                disabled
              />
              <p class="text-xs text-gray-500 mt-1">Assigned by admin</p>
            </div>

            <!-- Role (readonly for employees) -->
            <div>
              <label for="role" class="block mb-1 text-sm font-medium">Role</label>
              <input
                id="role"
                :value="form.role"
                type="text"
                class="border p-2 w-full rounded bg-gray-100 cursor-not-allowed"
                readonly
                disabled
              />
              <p class="text-xs text-gray-500 mt-1">Contact admin to change your role</p>
            </div>

            <!-- Gender -->
            <div>
              <label for="gender" class="block mb-1 text-sm font-medium">Gender</label>
              <select 
                id="gender" 
                v-model="form.gender" 
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.gender }"
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              <div v-if="form.errors.gender" class="text-red-500 text-sm mt-1">{{ form.errors.gender }}</div>
            </div>

            <!-- Department -->
            <div>
              <label for="department" class="block mb-1 text-sm font-medium">Department</label>
              <input 
                id="department" 
                v-model="form.department" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.department }"
              />
              <div v-if="form.errors.department" class="text-red-500 text-sm mt-1">{{ form.errors.department }}</div>
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="block mb-1 text-sm font-medium">Phone</label>
              <input 
                id="phone" 
                v-model="form.phone" 
                type="tel"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.phone }"
              />
              <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
              <label for="address" class="block mb-1 text-sm font-medium">Address</label>
              <textarea 
                id="address" 
                v-model="form.address" 
                rows="3"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.address }"
              ></textarea>
              <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</div>
            </div>

            <!-- Marital Status -->
            <div>
              <label for="marital_status" class="block mb-1 text-sm font-medium">Marital Status</label>
              <select 
                id="marital_status" 
                v-model="form.marital_status" 
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.marital_status }"
              >
                <option value="">Select Status</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="divorced">Divorced</option>
                <option value="widowed">Widowed</option>
              </select>
              <div v-if="form.errors.marital_status" class="text-red-500 text-sm mt-1">{{ form.errors.marital_status }}</div>
            </div>

            <!-- Date of Birth -->
            <div>
              <label for="date_of_birth" class="block mb-1 text-sm font-medium">Date of Birth</label>
              <input 
                id="date_of_birth" 
                v-model="form.date_of_birth" 
                type="date" 
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.date_of_birth }"
              />
              <div v-if="form.errors.date_of_birth" class="text-red-500 text-sm mt-1">{{ form.errors.date_of_birth }}</div>
            </div>

            <!-- Local Government -->
            <div>
              <label for="local_government" class="block mb-1 text-sm font-medium">Local Government</label>
              <input 
                id="local_government" 
                v-model="form.local_government" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.local_government }"
              />
              <div v-if="form.errors.local_government" class="text-red-500 text-sm mt-1">{{ form.errors.local_government }}</div>
            </div>

            <!-- State -->
            <div>
              <label for="state" class="block mb-1 text-sm font-medium">State</label>
              <input 
                id="state" 
                v-model="form.state" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.state }"
              />
              <div v-if="form.errors.state" class="text-red-500 text-sm mt-1">{{ form.errors.state }}</div>
            </div>

            <!-- Country -->
            <div>
              <label for="country" class="block mb-1 text-sm font-medium">Country</label>
              <input 
                id="country" 
                v-model="form.country" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.country }"
              />
              <div v-if="form.errors.country" class="text-red-500 text-sm mt-1">{{ form.errors.country }}</div>
            </div>

            <!-- Emergency Contact -->
            <div>
              <label for="emergency_contact" class="block mb-1 text-sm font-medium">Emergency Contact</label>
              <input 
                id="emergency_contact" 
                v-model="form.emergency_contact" 
                type="text"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.emergency_contact }"
              />
              <div v-if="form.errors.emergency_contact" class="text-red-500 text-sm mt-1">{{ form.errors.emergency_contact }}</div>
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block mb-1 text-sm font-medium">New Password</label>
              <input 
                id="password" 
                v-model="form.password" 
                type="password" 
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.password }"
                placeholder="Leave blank to keep current password"
              />
              <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="password_confirmation" class="block mb-1 text-sm font-medium">Confirm Password</label>
              <input 
                id="password_confirmation" 
                v-model="form.password_confirmation" 
                type="password" 
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.password_confirmation }"
                placeholder="Confirm new password"
              />
              <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</div>
            </div>

            <!-- Profile Photo-->
            <div class="md:col-span-2">
              <label for="photo" class="block mb-1 text-sm font-medium">Profile Photo</label>
              <input
                id="photo"
                type="file"
                @change="handlePhoto"
                accept="image/jpeg,image/png,image/jpg"
                class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': form.errors.photo }"
              />
              <div v-if="form.errors.photo" class="text-red-500 text-sm mt-1">{{ form.errors.photo }}</div>
              <p class="text-xs text-gray-500 mt-1">Allowed: JPG, JPEG, PNG. Max size: 2.5MB</p>
              
              <div v-if="photoPreview" class="mt-3">
                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                <img :src="photoPreview" alt="Profile Preview" class="h-32 w-32 object-cover rounded-lg border shadow-sm" />
              </div>
            </div>

            <!-- Submit / Cancel -->
            <div class="flex gap-3 mt-6 pt-4 border-t">
              <button 
                type="submit" 
                :disabled="form.processing" 
                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
              >
                <span v-if="form.processing">Updating...</span>
                <span v-else>Update Profile</span>
              </button>
              
              <button
                type="button"
                @click="goBack"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium"
              >
                Cancel
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
});

const page = usePage();

// Get current admin user data (you'll need to pass this from your main dashboard)
const adminUser = page.props.auth?.user || {};

const isEditMode = ref(false);
const photoPreview = ref(adminUser?.profile_image ? `/storage/${adminUser.profile_image}` : null);

// Initialize form with admin user data
const form = useForm({
  name: adminUser?.name || '',
  email: adminUser?.email || '',
  username: adminUser?.username || '',
  role: adminUser?.role || 'admin',
  gender: adminUser?.gender || '',
  department: adminUser?.department || 'Administration',
  phone: adminUser?.phone || '',
  address: adminUser?.address || '',
  marital_status: adminUser?.marital_status || '',
  date_of_birth: adminUser?.date_of_birth || '',
  local_government: adminUser?.local_government || '',
  state: adminUser?.state || '',
  country: adminUser?.country || 'Nigeria',
  emergency_contact: adminUser?.emergency_contact || '',
  salary: adminUser?.salary || '',
  hire_date: adminUser?.hire_date || '',
  status: adminUser?.status || 'active',
  password: '',
  password_confirmation: '',
  photo: null
});

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  if (!isEditMode.value) {
    // Reset form when canceling edit
    form.reset();
    photoPreview.value = adminUser?.profile_image ? `/storage/${adminUser.profile_image}` : null;
  }
};

function handlePhoto(e) {
  const file = e.target.files[0] ?? null;
  form.photo = file;

  if (file) {
    const reader = new FileReader();
    reader.onload = e => (photoPreview.value = e.target.result);
    reader.readAsDataURL(file);
  } else {
    photoPreview.value = adminUser?.profile_image ? `/storage/${adminUser.profile_image}` : null;
  }
}

function submit() {
  console.log("Submitting admin profile update:", form.data());
  
  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(route('admin.profile.update'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      console.log('Admin profile updated successfully');
      form.reset('password', 'password_confirmation', 'photo');
      isEditMode.value = false;
      photoPreview.value = adminUser?.profile_image ? `/storage/${adminUser.profile_image}` : null;
    },
    onError: (errors) => {
      console.error('Profile update errors:', errors);
    }
  });
}

onMounted(() => {
  console.log('Admin Profile component mounted with stats:', props.stats);
  console.log('Admin user data:', adminUser);
});
</script>

<template>
  <div class="space-y-6">
    <!-- Profile Summary Card (Always Visible) -->
    <div class="p-6 bg-white shadow rounded-lg">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
          <!-- Profile Photo -->
          <div class="flex-shrink-0">
            <img 
              v-if="photoPreview" 
              :src="photoPreview" 
              alt="Admin Profile" 
              class="h-16 w-16 rounded-full object-cover border-2 border-gray-200"
            />
            <div 
              v-else 
              class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center"
            >
              <span class="text-xl font-medium text-indigo-600">
                {{ (form.name || 'A').charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>
          
          <!-- Basic Info -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ form.name || 'Administrator' }}
            </h3>
            <p class="text-sm text-gray-600">{{ form.email || 'admin@company.com' }}</p>
            <p class="text-xs text-indigo-600 font-medium">{{ form.role || 'Admin' }}</p>
          </div>
        </div>

        <!-- Stats and Edit Button -->
        <div class="text-right">
          <div class="text-sm text-gray-500 mb-2">
            Managing {{ stats.totalUsers || 0 }} users
          </div>
          <button 
            @click="toggleEditMode"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
          >
            {{ isEditMode ? 'Cancel Edit' : 'Edit Profile' }}
          </button>
        </div>
      </div>

      <!-- System Statistics -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-sm font-medium text-gray-900 mb-3">System Overview</h4>
        <div class="grid grid-cols-3 gap-4 text-sm">
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ stats.totalUsers || 0 }}</div>
            <div class="text-gray-500">Total Users</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ stats.financeReports || 0 }}</div>
            <div class="text-gray-500">Reports</div>
          </div>
          <div class="text-center">
            <div class="flex items-center justify-center">
              <span 
                class="px-3 py-1 text-xs rounded-full font-medium"
                :class="stats.systemSettings ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ stats.systemSettings ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div class="text-gray-500 mt-1">System</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Expandable Edit Form -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 max-h-0 overflow-hidden"
      enter-to-class="opacity-100 max-h-screen"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 max-h-screen"
      leave-to-class="opacity-0 max-h-0 overflow-hidden"
    >
      <div v-if="isEditMode" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-indigo-50 border-b">
          <h4 class="text-lg font-medium text-indigo-900">Edit Profile Information</h4>
          <p class="text-sm text-indigo-600 mt-1">Update your personal and administrative details</p>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mx-6 mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="mx-6 mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ $page.props.flash.error }}
        </div>

        <!-- Validation Errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="mx-6 mt-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
          <strong>Please fix the following errors:</strong>
          <ul class="list-disc list-inside mt-2">
            <li v-for="(error, field) in form.errors" :key="field">
              <strong>{{ field.replace('_', ' ') }}:</strong> {{ error }}
            </li>
          </ul>
        </div>

        <!-- Edit Form -->
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label for="admin_name" class="block mb-2 text-sm font-medium text-gray-700">
                Full Name <span class="text-red-500">*</span>
              </label>
              <input 
                id="admin_name" 
                v-model="form.name" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.name }"
                required 
              />
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
            </div>

            <!-- Email -->
            <div>
              <label for="admin_email" class="block mb-2 text-sm font-medium text-gray-700">
                Email Address <span class="text-red-500">*</span>
              </label>
              <input 
                id="admin_email" 
                v-model="form.email" 
                type="email" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.email }"
                required 
              />
              <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
            </div>

            <!-- Username -->
            <div>
              <label for="admin_username" class="block mb-2 text-sm font-medium text-gray-700">
                Username <span class="text-red-500">*</span>
              </label>
              <input 
                id="admin_username" 
                v-model="form.username" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.username }"
                required 
              />
              <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</div>
            </div>

            <!-- Role (readonly for admin) -->
            <div>
              <label for="admin_role" class="block mb-2 text-sm font-medium text-gray-700">Role</label>
              <input 
                id="admin_role" 
                :value="form.role" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed"
                readonly 
                disabled
              />
              <p class="text-xs text-gray-500 mt-1">Administrative role cannot be changed</p>
            </div>

            <!-- Gender -->
            <div>
              <label for="admin_gender" class="block mb-2 text-sm font-medium text-gray-700">Gender</label>
              <select 
                id="admin_gender" 
                v-model="form.gender" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
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
              <label for="admin_department" class="block mb-2 text-sm font-medium text-gray-700">Department</label>
              <input 
                id="admin_department" 
                v-model="form.department" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.department }"
                placeholder="Administration"
              />
              <div v-if="form.errors.department" class="text-red-500 text-sm mt-1">{{ form.errors.department }}</div>
            </div>

            <!-- Phone -->
            <div>
              <label for="admin_phone" class="block mb-2 text-sm font-medium text-gray-700">Phone Number</label>
              <input 
                id="admin_phone" 
                v-model="form.phone" 
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.phone }"
                placeholder="+234 xxx xxx xxxx"
              />
              <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
            </div>

            <!-- Date of Birth -->
            <div>
              <label for="admin_dob" class="block mb-2 text-sm font-medium text-gray-700">Date of Birth</label>
              <input 
                id="admin_dob" 
                v-model="form.date_of_birth" 
                type="date" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.date_of_birth }"
              />
              <div v-if="form.errors.date_of_birth" class="text-red-500 text-sm mt-1">{{ form.errors.date_of_birth }}</div>
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
              <label for="admin_address" class="block mb-2 text-sm font-medium text-gray-700">Address</label>
              <textarea 
                id="admin_address" 
                v-model="form.address" 
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.address }"
                placeholder="Complete address including street, city, etc."
              ></textarea>
              <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</div>
            </div>

            <!-- Marital Status -->
            <div>
              <label for="admin_marital" class="block mb-2 text-sm font-medium text-gray-700">Marital Status</label>
              <select 
                id="admin_marital" 
                v-model="form.marital_status" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
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

            <!-- Local Government -->
            <div>
              <label for="admin_lga" class="block mb-2 text-sm font-medium text-gray-700">Local Government</label>
              <input 
                id="admin_lga" 
                v-model="form.local_government" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.local_government }"
              />
              <div v-if="form.errors.local_government" class="text-red-500 text-sm mt-1">{{ form.errors.local_government }}</div>
            </div>

            <!-- State -->
            <div>
              <label for="admin_state" class="block mb-2 text-sm font-medium text-gray-700">State</label>
              <input 
                id="admin_state" 
                v-model="form.state" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.state }"
              />
              <div v-if="form.errors.state" class="text-red-500 text-sm mt-1">{{ form.errors.state }}</div>
            </div>

            <!-- Country -->
            <div>
              <label for="admin_country" class="block mb-2 text-sm font-medium text-gray-700">Country</label>
              <input 
                id="admin_country" 
                v-model="form.country" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.country }"
              />
              <div v-if="form.errors.country" class="text-red-500 text-sm mt-1">{{ form.errors.country }}</div>
            </div>

            <!-- Emergency Contact -->
            <div>
              <label for="admin_emergency" class="block mb-2 text-sm font-medium text-gray-700">Emergency Contact</label>
              <input 
                id="admin_emergency" 
                v-model="form.emergency_contact" 
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.emergency_contact }"
                placeholder="Name and phone number"
              />
              <div v-if="form.errors.emergency_contact" class="text-red-500 text-sm mt-1">{{ form.errors.emergency_contact }}</div>
            </div>

            <!-- Password -->
            <div>
              <label for="admin_password" class="block mb-2 text-sm font-medium text-gray-700">New Password</label>
              <input 
                id="admin_password" 
                v-model="form.password" 
                type="password" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.password }"
                placeholder="Leave blank to keep current password"
              />
              <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="admin_password_confirmation" class="block mb-2 text-sm font-medium text-gray-700">Confirm Password</label>
              <input 
                id="admin_password_confirmation" 
                v-model="form.password_confirmation" 
                type="password" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.password_confirmation }"
                placeholder="Confirm new password"
              />
              <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</div>
            </div>

            <!-- Profile Photo -->
            <div class="md:col-span-2">
              <label for="admin_photo" class="block mb-2 text-sm font-medium text-gray-700">Profile Photo</label>
              <input
                id="admin_photo"
                type="file"
                @change="handlePhoto"
                accept="image/jpeg,image/png,image/jpg"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.photo }"
              />
              <div v-if="form.errors.photo" class="text-red-500 text-sm mt-1">{{ form.errors.photo }}</div>
              <p class="text-xs text-gray-500 mt-1">Allowed: JPG, JPEG, PNG. Max size: 2.5MB</p>
              
              <div v-if="photoPreview" class="mt-3">
                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                <img :src="photoPreview" alt="Profile Preview" class="h-32 w-32 object-cover rounded-lg border shadow-sm" />
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button 
              type="button"
              @click="toggleEditMode"
              class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="form.processing" 
              class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              <span v-if="form.processing">Updating...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Custom transition for smooth expand/collapse */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

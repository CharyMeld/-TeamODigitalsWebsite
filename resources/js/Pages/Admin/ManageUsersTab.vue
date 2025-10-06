<script setup>
import { ref, computed, onMounted } from 'vue';
import { apiClient } from "@/axiosConfig.js";

// State
const users = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const selectedRole = ref('all');
const selectedDepartment = ref('all');
const currentPage = ref(1);
const perPage = 10;

// Modals
const showUserModal = ref(false);
const showAddUserModal = ref(false);
const showEditUserModal = ref(false);
const showDeleteConfirm = ref(false);

// Selected user
const selectedUser = ref(null);
const userToDelete = ref(null);

// Notifications
const notifications = ref([]);

// Form data for add/edit
const userForm = ref({
  name: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
  role: 'employee',
  gender: '',
  department: '',
  phone: '',
  address: '',
  marital_status: '',
  date_of_birth: '',
  local_government: '',
  state: '',
  country: 'Nigeria',
  emergency_contact: '',
  salary: '',
  hire_date: '',
  status: 'active',
  employee_id: ''
});

const formErrors = ref({});

// Available options
const roles = ['employee', 'admin', 'superadmin', 'developer'];
const departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations', 'Administration'];
const genderOptions = ['male', 'female', 'other'];
const maritalStatusOptions = ['single', 'married', 'divorced', 'widowed'];

// Computed: filtered users
const filteredUsers = computed(() => {
  return users.value
    .filter(user => {
      const matchesSearch = !searchQuery.value ||
        user.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.employee_id?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.username?.toLowerCase().includes(searchQuery.value.toLowerCase());

      const matchesRole = selectedRole.value === 'all' || user.role === selectedRole.value;
      const matchesDepartment = selectedDepartment.value === 'all' || user.department === selectedDepartment.value;

      return matchesSearch && matchesRole && matchesDepartment;
    });
});

// Computed: paginated users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredUsers.value.slice(start, end);
});

// Computed: total pages
const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / perPage);
});

// Notification system
function showNotification(message, type = 'info') {
  const id = Date.now();
  notifications.value.push({ id, message, type });
  setTimeout(() => {
    notifications.value = notifications.value.filter(n => n.id !== id);
  }, 5000);
}

function removeNotification(id) {
  notifications.value = notifications.value.filter(n => n.id !== id);
}

// Fetch all users
async function fetchUsers() {
  loading.value = true;
  try {
    console.log('🔄 Fetching users...');
    const response = await apiClient.get('/dashboard/all-users');

    // Extract users from response
    const data = response.data.data || response.data;

    if (Array.isArray(data)) {
      users.value = data;
      console.log('✅ Users loaded:', users.value.length);
    } else {
      console.warn('⚠️ Unexpected data format:', data);
      users.value = [];
      showNotification('Unexpected data format received', 'error');
    }
  } catch (error) {
    console.error('❌ Error fetching users:', error);
    showNotification('Failed to load users. Please try again.', 'error');
    users.value = [];
  } finally {
    loading.value = false;
  }
}

// View user profile
function viewUser(user) {
  selectedUser.value = user;
  showUserModal.value = true;
}

// Open add user modal
function openAddUserModal() {
  resetForm();
  showAddUserModal.value = true;
}

// Open edit user modal
function editUser(user) {
  selectedUser.value = user;

  // Populate form with user data
  userForm.value = {
    name: user.name || '',
    email: user.email || '',
    username: user.username || '',
    password: '',
    password_confirmation: '',
    role: user.role || 'employee',
    gender: user.gender || '',
    department: user.department || '',
    phone: user.phone || '',
    address: user.address || '',
    marital_status: user.marital_status || '',
    date_of_birth: user.date_of_birth || '',
    local_government: user.local_government || '',
    state: user.state || '',
    country: user.country || 'Nigeria',
    emergency_contact: user.emergency_contact || '',
    salary: user.salary || '',
    hire_date: user.hire_date || '',
    status: user.status || 'active',
    employee_id: user.employee_id || ''
  };

  showEditUserModal.value = true;
}

// Confirm delete user
function confirmDelete(user) {
  userToDelete.value = user;
  showDeleteConfirm.value = true;
}

// Add new user
async function addUser() {
  if (!validateForm()) return;

  loading.value = true;
  try {
    console.log('🔄 Adding new user...');

    const response = await apiClient.post('/admin/users', userForm.value);

    showNotification('User added successfully!', 'success');
    showAddUserModal.value = false;
    resetForm();

    // Refresh users list
    await fetchUsers();

  } catch (error) {
    console.error('❌ Error adding user:', error);

    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors;
    }

    showNotification(
      error.response?.data?.message || 'Failed to add user. Please check the form.',
      'error'
    );
  } finally {
    loading.value = false;
  }
}

// Update user
async function updateUser() {
  if (!validateForm(true)) return;

  loading.value = true;
  try {
    console.log('🔄 Updating user...');

    const response = await apiClient.put(`/admin/users/${selectedUser.value.id}`, userForm.value);

    showNotification('User updated successfully!', 'success');
    showEditUserModal.value = false;
    resetForm();

    // Refresh users list
    await fetchUsers();

  } catch (error) {
    console.error('❌ Error updating user:', error);

    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors;
    }

    showNotification(
      error.response?.data?.message || 'Failed to update user. Please check the form.',
      'error'
    );
  } finally {
    loading.value = false;
  }
}

// Delete user
async function deleteUser() {
  if (!userToDelete.value) return;

  loading.value = true;
  try {
    console.log('🔄 Deleting user...');

    await apiClient.delete(`/admin/users/${userToDelete.value.id}`);

    showNotification('User deleted successfully!', 'success');
    showDeleteConfirm.value = false;
    userToDelete.value = null;

    // Refresh users list
    await fetchUsers();

  } catch (error) {
    console.error('❌ Error deleting user:', error);
    showNotification(
      error.response?.data?.message || 'Failed to delete user.',
      'error'
    );
  } finally {
    loading.value = false;
  }
}

// Form validation
function validateForm(isEdit = false) {
  const errors = {};

  if (!userForm.value.name) errors.name = 'Name is required';
  if (!userForm.value.email) errors.email = 'Email is required';
  if (!userForm.value.username) errors.username = 'Username is required';

  // Password validation (required for new users, optional for edit)
  if (!isEdit) {
    if (!userForm.value.password) {
      errors.password = 'Password is required';
    } else if (userForm.value.password.length < 8) {
      errors.password = 'Password must be at least 8 characters';
    }
  } else if (userForm.value.password && userForm.value.password.length < 8) {
    errors.password = 'Password must be at least 8 characters';
  }

  if (userForm.value.password && userForm.value.password !== userForm.value.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match';
  }

  if (!userForm.value.role) errors.role = 'Role is required';

  formErrors.value = errors;
  return Object.keys(errors).length === 0;
}

// Reset form
function resetForm() {
  userForm.value = {
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
    role: 'employee',
    gender: '',
    department: '',
    phone: '',
    address: '',
    marital_status: '',
    date_of_birth: '',
    local_government: '',
    state: '',
    country: 'Nigeria',
    emergency_contact: '',
    salary: '',
    hire_date: '',
    status: 'active',
    employee_id: ''
  };
  formErrors.value = {};
  selectedUser.value = null;
}

// Format date
function formatDate(date) {
  if (!date) return 'N/A';
  try {
    return new Date(date).toLocaleDateString();
  } catch {
    return 'Invalid Date';
  }
}

// Get role badge class
function getRoleBadgeClass(role) {
  return {
    developer: 'bg-purple-100 text-purple-800',
    superadmin: 'bg-red-100 text-red-800',
    admin: 'bg-blue-100 text-blue-800',
    employee: 'bg-green-100 text-green-800',
  }[role] || 'bg-gray-100 text-gray-800';
}

// Get status badge class
function getStatusBadgeClass(status) {
  return status === 'active'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800';
}

// Pagination
function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

// Lifecycle
onMounted(() => {
  console.log('📋 Manage Users component mounted');
  fetchUsers();
});
</script>

<template>
  <div class="p-6 bg-white shadow rounded-lg">
    <!-- Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="max-w-sm p-4 rounded-lg shadow-lg transition-all duration-300"
        :class="{
          'bg-green-500 text-white': notification.type === 'success',
          'bg-red-500 text-white': notification.type === 'error',
          'bg-blue-500 text-white': notification.type === 'info'
        }"
      >
        <div class="flex justify-between items-center">
          <span>{{ notification.message }}</span>
          <button @click="removeNotification(notification.id)" class="ml-2 text-white hover:text-gray-200">
            ×
          </button>
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-2xl font-semibold text-gray-900">Manage Users</h3>
        <p class="text-sm text-gray-600 mt-1">Add, edit, delete, and view user profiles</p>
      </div>
      <button
        @click="openAddUserModal"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Add New User</span>
      </button>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- Search -->
      <div class="md:col-span-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name, email, username, or ID..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Role filter -->
      <div>
        <select
          v-model="selectedRole"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">All Roles</option>
          <option v-for="role in roles" :key="role" :value="role">
            {{ role.charAt(0).toUpperCase() + role.slice(1) }}
          </option>
        </select>
      </div>

      <!-- Department filter -->
      <div>
        <select
          v-model="selectedDepartment"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">All Departments</option>
          <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
        </select>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg">
        <div class="text-sm text-blue-600 font-medium">Total Users</div>
        <div class="text-2xl font-bold text-blue-900">{{ users.length }}</div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg">
        <div class="text-sm text-green-600 font-medium">Filtered Results</div>
        <div class="text-2xl font-bold text-green-900">{{ filteredUsers.length }}</div>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg">
        <div class="text-sm text-purple-600 font-medium">Current Page</div>
        <div class="text-2xl font-bold text-purple-900">{{ currentPage }} / {{ totalPages || 1 }}</div>
      </div>
      <div class="bg-orange-50 p-4 rounded-lg">
        <div class="text-sm text-orange-600 font-medium">Showing</div>
        <div class="text-2xl font-bold text-orange-900">{{ paginatedUsers.length }}</div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email/Username</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">
                      {{ (user.name || 'U').charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ user.name || 'Unknown' }}</div>
                  <div class="text-sm text-gray-500">ID: {{ user.employee_id || user.id }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ user.email }}</div>
              <div class="text-sm text-gray-500">@{{ user.username }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getRoleBadgeClass(user.role)">
                {{ user.role }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ user.department || 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeClass(user.status)">
                {{ user.status || 'active' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button
                @click="viewUser(user)"
                class="text-blue-600 hover:text-blue-900"
                title="View Profile"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
              <button
                @click="editUser(user)"
                class="text-green-600 hover:text-green-900"
                title="Edit User"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button
                @click="confirmDelete(user)"
                class="text-red-600 hover:text-red-900"
                title="Delete User"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="paginatedUsers.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">👥</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
        <p class="text-gray-500">
          {{ searchQuery || selectedRole !== 'all' || selectedDepartment !== 'all'
            ? 'Try adjusting your filters'
            : 'Click "Add New User" to get started'
          }}
        </p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-between items-center mt-6">
      <div class="text-sm text-gray-700">
        Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, filteredUsers.length) }} of {{ filteredUsers.length }} results
      </div>
      <div class="flex space-x-2">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1 border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button
          v-for="page in totalPages"
          :key="page"
          @click="goToPage(page)"
          class="px-3 py-1 border rounded-md hover:bg-gray-50"
          :class="{ 'bg-blue-600 text-white': page === currentPage }"
        >
          {{ page }}
        </button>
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
      <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="text-blue-600 font-medium">Loading...</span>
      </div>
    </div>

    <!-- View User Modal -->
    <div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
          <h3 class="text-xl font-semibold text-gray-900">User Profile</h3>
          <button @click="showUserModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div v-if="selectedUser" class="p-6">
          <!-- User Header -->
          <div class="flex items-center space-x-4 mb-6 pb-6 border-b">
            <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center">
              <span class="text-3xl font-bold text-blue-600">
                {{ selectedUser.name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <h4 class="text-2xl font-bold text-gray-900">{{ selectedUser.name }}</h4>
              <p class="text-gray-600">{{ selectedUser.email }}</p>
              <div class="flex space-x-2 mt-2">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getRoleBadgeClass(selectedUser.role)">
                  {{ selectedUser.role }}
                </span>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeClass(selectedUser.status)">
                  {{ selectedUser.status || 'active' }}
                </span>
              </div>
            </div>
          </div>

          <!-- User Details Grid -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Username</h5>
              <p class="text-gray-900">@{{ selectedUser.username }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Employee ID</h5>
              <p class="text-gray-900">{{ selectedUser.employee_id || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Department</h5>
              <p class="text-gray-900">{{ selectedUser.department || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Phone</h5>
              <p class="text-gray-900">{{ selectedUser.phone || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Gender</h5>
              <p class="text-gray-900">{{ selectedUser.gender ? selectedUser.gender.charAt(0).toUpperCase() + selectedUser.gender.slice(1) : 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Marital Status</h5>
              <p class="text-gray-900">{{ selectedUser.marital_status ? selectedUser.marital_status.charAt(0).toUpperCase() + selectedUser.marital_status.slice(1) : 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Date of Birth</h5>
              <p class="text-gray-900">{{ formatDate(selectedUser.date_of_birth) }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Hire Date</h5>
              <p class="text-gray-900">{{ formatDate(selectedUser.hire_date) }}</p>
            </div>
            <div class="col-span-2">
              <h5 class="text-sm font-medium text-gray-500 mb-1">Address</h5>
              <p class="text-gray-900">{{ selectedUser.address || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">State</h5>
              <p class="text-gray-900">{{ selectedUser.state || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Country</h5>
              <p class="text-gray-900">{{ selectedUser.country || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Emergency Contact</h5>
              <p class="text-gray-900">{{ selectedUser.emergency_contact || 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Salary</h5>
              <p class="text-gray-900">{{ selectedUser.salary ? '₦' + Number(selectedUser.salary).toLocaleString() : 'N/A' }}</p>
            </div>
            <div>
              <h5 class="text-sm font-medium text-gray-500 mb-1">Account Created</h5>
              <p class="text-gray-900">{{ formatDate(selectedUser.created_at) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-2">
          <button
            @click="showUserModal = false; editUser(selectedUser)"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Edit User
          </button>
          <button
            @click="showUserModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
          <h3 class="text-xl font-semibold text-gray-900">Add New User</h3>
          <button @click="showAddUserModal = false; resetForm()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="addUser" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
              <input
                v-model="userForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.name }"
              />
              <p v-if="formErrors.name" class="text-red-500 text-xs mt-1">{{ formErrors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input
                v-model="userForm.email"
                type="email"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.email }"
              />
              <p v-if="formErrors.email" class="text-red-500 text-xs mt-1">{{ formErrors.email }}</p>
            </div>

            <!-- Username -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
              <input
                v-model="userForm.username"
                type="text"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.username }"
              />
              <p v-if="formErrors.username" class="text-red-500 text-xs mt-1">{{ formErrors.username }}</p>
            </div>

            <!-- Employee ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
              <input
                v-model="userForm.employee_id"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
              <input
                v-model="userForm.password"
                type="password"
                required
                minlength="8"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.password }"
              />
              <p v-if="formErrors.password" class="text-red-500 text-xs mt-1">{{ formErrors.password }}</p>
            </div>

            <!-- Password Confirmation -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
              <input
                v-model="userForm.password_confirmation"
                type="password"
                required
                minlength="8"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.password_confirmation }"
              />
              <p v-if="formErrors.password_confirmation" class="text-red-500 text-xs mt-1">{{ formErrors.password_confirmation }}</p>
            </div>

            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
              <select
                v-model="userForm.role"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option v-for="role in roles" :key="role" :value="role">
                  {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Department -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <select
                v-model="userForm.department"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
              </select>
            </div>

            <!-- Gender -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
              <select
                v-model="userForm.gender"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Gender</option>
                <option v-for="gender in genderOptions" :key="gender" :value="gender">
                  {{ gender.charAt(0).toUpperCase() + gender.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="userForm.phone"
                type="tel"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Marital Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
              <select
                v-model="userForm.marital_status"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Status</option>
                <option v-for="status in maritalStatusOptions" :key="status" :value="status">
                  {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Date of Birth -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              <input
                v-model="userForm.date_of_birth"
                type="date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Hire Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
              <input
                v-model="userForm.hire_date"
                type="date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Salary -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Salary (₦)</label>
              <input
                v-model="userForm.salary"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="userForm.status"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <input
                v-model="userForm.address"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Local Government -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Local Government</label>
              <input
                v-model="userForm.local_government"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- State -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
              <input
                v-model="userForm.state"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Country -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
              <input
                v-model="userForm.country"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Emergency Contact -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
              <input
                v-model="userForm.emergency_contact"
                type="tel"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="flex justify-end space-x-2 mt-6 pt-6 border-t">
            <button
              type="button"
              @click="showAddUserModal = false; resetForm()"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              Add User
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
          <h3 class="text-xl font-semibold text-gray-900">Edit User</h3>
          <button @click="showEditUserModal = false; resetForm()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="updateUser" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
              <input
                v-model="userForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.name }"
              />
              <p v-if="formErrors.name" class="text-red-500 text-xs mt-1">{{ formErrors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input
                v-model="userForm.email"
                type="email"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.email }"
              />
              <p v-if="formErrors.email" class="text-red-500 text-xs mt-1">{{ formErrors.email }}</p>
            </div>

            <!-- Username -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
              <input
                v-model="userForm.username"
                type="text"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.username }"
              />
              <p v-if="formErrors.username" class="text-red-500 text-xs mt-1">{{ formErrors.username }}</p>
            </div>

            <!-- Employee ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
              <input
                v-model="userForm.employee_id"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Password (optional for edit) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
              <input
                v-model="userForm.password"
                type="password"
                minlength="8"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.password }"
              />
              <p v-if="formErrors.password" class="text-red-500 text-xs mt-1">{{ formErrors.password }}</p>
            </div>

            <!-- Password Confirmation -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <input
                v-model="userForm.password_confirmation"
                type="password"
                minlength="8"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formErrors.password_confirmation }"
              />
              <p v-if="formErrors.password_confirmation" class="text-red-500 text-xs mt-1">{{ formErrors.password_confirmation }}</p>
            </div>

            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
              <select
                v-model="userForm.role"
                required
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option v-for="role in roles" :key="role" :value="role">
                  {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Department -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <select
                v-model="userForm.department"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
              </select>
            </div>

            <!-- Gender -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
              <select
                v-model="userForm.gender"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Gender</option>
                <option v-for="gender in genderOptions" :key="gender" :value="gender">
                  {{ gender.charAt(0).toUpperCase() + gender.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="userForm.phone"
                type="tel"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Marital Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
              <select
                v-model="userForm.marital_status"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Status</option>
                <option v-for="status in maritalStatusOptions" :key="status" :value="status">
                  {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Date of Birth -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              <input
                v-model="userForm.date_of_birth"
                type="date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Hire Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
              <input
                v-model="userForm.hire_date"
                type="date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Salary -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Salary (₦)</label>
              <input
                v-model="userForm.salary"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="userForm.status"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <input
                v-model="userForm.address"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Local Government -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Local Government</label>
              <input
                v-model="userForm.local_government"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- State -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
              <input
                v-model="userForm.state"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Country -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
              <input
                v-model="userForm.country"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Emergency Contact -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
              <input
                v-model="userForm.emergency_contact"
                type="tel"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="flex justify-end space-x-2 mt-6 pt-6 border-t">
            <button
              type="button"
              @click="showEditUserModal = false; resetForm()"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              Update User
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete User</h3>
          <p class="text-gray-600 text-center mb-6">
            Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone.
          </p>
          <div class="flex space-x-2">
            <button
              @click="showDeleteConfirm = false; userToDelete = null"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              @click="deleteUser"
              :disabled="loading"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add any custom styles here */
</style>

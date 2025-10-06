<script setup>
import { ref, watch, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue'; // Import the modal

// PROPS & FILTERS (No changes here)
const props = defineProps({
    users: Object,
    filters: { type: Object, default: () => ({}) },
    errors: Object,
});
const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? null);
watch([search, role], ([newSearch, newRole]) => {
    router.get(route('users.index'), { search: newSearch, role: newRole }, { preserveState: true, replace: true });
});

// MODAL & FORM STATE
const isModalOpen = ref(false);
const isEditMode = ref(false);
const userToDelete = ref(null);
const isDeleteModalOpen = ref(false);

// Use Inertia's useForm for easy form handling, validation, and state
const form = useForm({
    id: null,
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    gender: null,
    role: 'employee',
    department: '',
    phone: '',
    address: '',
    marital_status: null,
    date_of_birth: '',
    local_government: '',
    state: '',
    country: 'Nigeria',
    emergency_contact: '',
});

// MODAL FUNCTIONS
const openAddModal = () => {
    isEditMode.value = false;
    form.reset(); // Clear form fields
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    isEditMode.value = true;
    // Populate form with user data
    form.id = user.id;
    form.name = user.name;
    form.username = user.username;
    form.email = user.email;
    form.gender = user.gender;
    form.role = user.role;
    form.department = user.department;
    form.phone = user.phone;
    form.address = user.address;
    form.marital_status = user.marital_status;
    form.date_of_birth = user.date_of_birth;
    form.local_government = user.local_government;
    form.state = user.state;
    form.country = user.country;
    form.emergency_contact = user.emergency_contact;
    form.password = ''; // Always clear password on edit
    form.password_confirmation = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

// DELETE MODAL FUNCTIONS
const openDeleteModal = (user) => {
    userToDelete.value = user;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    userToDelete.value = null;
};

// FORM SUBMISSION
const submitForm = () => {
    if (isEditMode.value) {
        // Update User
        form.put(route('users.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        // Create User
        form.post(route('users.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = () => {
    router.delete(route('users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};

// Display flash messages from the backend
const flashMessage = computed(() => props.jetstream?.flash?.message);

</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
                <button @click="openAddModal" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    + Add User
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Message -->
                <div v-if="$page.props.flash.message" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.message }}
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Filter Section -->
                    <div class="mb-4 flex space-x-4">
                        <input v-model="search" type="text" placeholder="Search..." class="w-1/3 rounded-md shadow-sm border-gray-300">
                        <select v-model="role" class="rounded-md shadow-sm border-gray-300">
                            <option :value="null">-- All Roles --</option>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <!-- User Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.role }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.employee_id || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button @click="openDeleteModal(user)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="users.links" class="mt-6" />
                </div>
            </div>
        </div>

        <!-- Add/Edit User Modal -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">{{ isEditMode ? 'Edit User' : 'Add New User' }}</h2>
                <form @submit.prevent="submitForm" class="mt-6 space-y-4">
                    <!-- Grid for form layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- All your form fields go here -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" id="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" id="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input v-model="form.username" id="username" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input v-model="form.phone" id="phone" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select v-model="form.role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                            <input v-model="form.department" id="department" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                            <select v-model="form.gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option :value="null">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                            <select v-model="form.marital_status" id="marital_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option :value="null">Select...</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                            <input v-model="form.date_of_birth" id="date_of_birth" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input v-model="form.address" id="address" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <!-- ... Add inputs for state, country, etc. in the same way -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input v-model="form.password" id="password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <div v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input v-model="form.password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-2">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md" :class="{ 'opacity-50': form.processing }">
                            {{ form.processing ? 'Saving...' : (isEditMode ? 'Update User' : 'Create User') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Are you sure?</h2>
                <p class="mt-2 text-sm text-gray-600">
                    This will permanently delete the user "{{ userToDelete?.name }}". This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <button @click="closeDeleteModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-2">Cancel</button>
                    <button @click="deleteUser" class="px-4 py-2 bg-red-600 text-white rounded-md">Delete User</button>
                </div>
            </div>
        </Modal>

    </AppLayout>
</template>


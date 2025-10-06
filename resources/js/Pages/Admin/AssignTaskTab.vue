<script setup>
import { ref } from 'vue';
import Swal from 'sweetalert2';
import vSelect from 'vue-select';
import { makeRequest } from '@/axiosConfig.js'; // ✅ your Axios instance

const form = ref({
  user_id: null,
  employee_id: "",
  work_location: '',
  project_name: '',
  task_title: '',
  task_type: '',
  task_priority: '',
  deadline: '',
  task_description: '',
  attachment: null,
});

const users = ref([]);
const searching = ref(false);

// 🔍 Fetch users from backend
const searchUsers = async (search) => {
  if (!search) {
    users.value = [];
    return;
  }

  searching.value = true;
  try {
    const { data } = await makeRequest({
      method: 'GET',
      url: '/admin/users/search',
      params: { query: search },
    });
    users.value = data.users || [];
  } catch (err) {
    console.error('Error searching users:', err);
    Swal.fire('Error', err.response?.data?.message || err.message || 'Failed to search users', 'error');
  } finally {
    searching.value = false;
  }
};

// 📂 File attachment
const handleFileChange = (e) => {
  form.value.attachment = e.target.files[0] || null;
};

// 🚀 Submit form
const submitForm = async () => {
  if (!form.value.user_id) {
    return Swal.fire('Error', 'Please select a user', 'error');
  }

  const formData = new FormData();
  formData.append('employee_id', form.value.user_id.id); // ✅ use backend expected field

  // append other fields
  formData.append('work_location', form.value.work_location);
  formData.append('project_name', form.value.project_name);
  formData.append('task_title', form.value.task_title);
  formData.append('task_type', form.value.task_type);
  formData.append('task_priority', form.value.task_priority);
  formData.append('deadline', form.value.deadline);
  formData.append('task_description', form.value.task_description);

  if (form.value.attachment) {
    formData.append('attachment', form.value.attachment);
  }

  try {
    await makeRequest({
      method: 'POST',
      url: '/admin/users/tasks',
      data: formData,
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    Swal.fire('Success', 'Task assigned successfully!', 'success');

    // reset form
    form.value = {
      user_id: null,
      work_location: '',
      project_name: '',
      task_title: '',
      task_type: '',
      task_priority: '',
      deadline: '',
      task_description: '',
      attachment: null,
    };

    // clear file input manually
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';

  } catch (err) {
    console.error('Error assigning task:', err);
    if (err.response?.status === 401) {
      Swal.fire('Session Expired', 'Please login again', 'warning')
        .then(() => window.location.href = '/login');
      return;
    }
    Swal.fire('Error', err.response?.data?.message || err.message || 'Failed to assign task', 'error');
  }
};
</script>

<template>
  <div class="space-y-6 p-6">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-4">
      <h2 class="text-2xl font-bold text-gray-900">Assign Task to Employee</h2>
      <p class="text-gray-600 mt-1">Create and assign new tasks to your team members</p>
    </div>

    <!-- Task Form -->
    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- User Select -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select User *</label>
          <v-select
            v-model="form.user_id"
            :options="users"
            label="name"
            :reduce="user => user"
            placeholder="Type to search user..."
            @search="searchUsers"
            :loading="searching"
            :clearable="true"
          />
        </div>

        <!-- Work Location -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Work Location *</label>
          <select v-model="form.work_location" required class="w-full px-3 py-2 border rounded-md">
            <option value="">-- Select Location --</option>
            <option value="ONSITE">ONSITE</option>
            <option value="REMOTE">REMOTE</option>
            <option value="HYBRID">HYBRID</option>
          </select>
        </div>

        <!-- Project Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
          <select v-model="form.project_name" required class="w-full px-3 py-2 border rounded-md">
            <option value="">-- Select Project --</option>
            <option value="WACP">WACP</option>
            <option value="WACS">WACS</option>
            <option value="UI">UI</option>
          </select>
        </div>

        <!-- Task Priority -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Task Priority *</label>
          <select v-model="form.task_priority" required class="w-full px-3 py-2 border rounded-md">
            <option value="">-- Select Priority --</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
          </select>
        </div>
      </div>

      <!-- Task Title -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
        <input v-model="form.task_title" type="text" placeholder="Enter task title" required class="w-full px-3 py-2 border rounded-md"/>
      </div>

      <!-- Task Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Task Type *</label>
        <select v-model="form.task_type" required class="w-full px-3 py-2 border rounded-md">
          <option value="">-- Select Task Type --</option>
          <option value="CLEANING, SORTING & ARRANGING">CLEANING, SORTING & ARRANGING</option>
          <option value="REMOVING PINS">REMOVING PINS</option>
          <option value="TAGGING & FILING">TAGGING & FILING</option>
          <option value="SCANNING">SCANNING</option>
          <option value="RENAMING">RENAMING</option>
          <option value="VERIFICATION">VERIFICATION</option>
          <option value="WRITING PROPOSAL">WRITING PROPOSAL</option>
          <option value="CODING">CODING</option>
          <option value="ACCOUNTING">ACCOUNTING</option>
          <option value="REPORT WRITING">REPORT WRITING</option>
          <option value="PRESENTATION">PRESENTATION</option>
        </select>
      </div>

      <!-- Deadline -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Deadline *</label>
        <input v-model="form.deadline" type="date" required :min="new Date().toISOString().split('T')[0]" class="w-full px-3 py-2 border rounded-md"/>
      </div>

      <!-- Task Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Task Description *</label>
        <textarea v-model="form.task_description" rows="4" placeholder="Provide detailed description..." required class="w-full px-3 py-2 border rounded-md"/>
      </div>

      <!-- File Attachment -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Attach File (Optional)</label>
        <input type="file" @change="handleFileChange" class="w-full px-3 py-2 border rounded-md" />
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end pt-4 border-t border-gray-200">
        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Assign Task
        </button>
      </div>
    </form>
  </div>
</template>


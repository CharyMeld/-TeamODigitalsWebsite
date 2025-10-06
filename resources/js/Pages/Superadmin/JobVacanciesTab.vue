<script setup>
import { ref, onMounted } from 'vue';
import { apiClient } from '@/axiosConfig.js';

const jobVacancies = ref([]);
const isLoading = ref(false);
const showModal = ref(false);
const editingJob = ref(null);

const form = ref({
    title: '',
    department: '',
    location: '',
    job_type: 'full-time',
    description: '',
    requirements: '',
    responsibilities: '',
    salary_range: '',
    application_deadline: '',
    status: 'active'
});

const jobTypes = [
    { value: 'full-time', label: 'Full-Time' },
    { value: 'part-time', label: 'Part-Time' },
    { value: 'contract', label: 'Contract' },
    { value: 'internship', label: 'Internship' }
];

const statuses = [
    { value: 'active', label: 'Active' },
    { value: 'draft', label: 'Draft' },
    { value: 'closed', label: 'Closed' }
];

async function fetchJobs() {
    isLoading.value = true;
    try {
        const response = await apiClient.get('/superadmin/job-vacancies');
        jobVacancies.value = response.data;
    } catch (error) {
        console.error('Error fetching jobs:', error);
        alert('Failed to fetch job vacancies');
    } finally {
        isLoading.value = false;
    }
}

function openModal(job = null) {
    if (job) {
        editingJob.value = job;
        form.value = { ...job };
    } else {
        editingJob.value = null;
        resetForm();
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingJob.value = null;
    resetForm();
}

function resetForm() {
    form.value = {
        title: '',
        department: '',
        location: '',
        job_type: 'full-time',
        description: '',
        requirements: '',
        responsibilities: '',
        salary_range: '',
        application_deadline: '',
        status: 'active'
    };
}

async function saveJob() {
    isLoading.value = true;
    try {
        if (editingJob.value) {
            await apiClient.put(`/superadmin/job-vacancies/${editingJob.value.id}`, form.value);
            alert('Job vacancy updated successfully');
        } else {
            await apiClient.post('/superadmin/job-vacancies', form.value);
            alert('Job vacancy created successfully');
        }
        await fetchJobs();
        closeModal();
    } catch (error) {
        console.error('Error saving job:', error);
        alert(error.response?.data?.message || 'Failed to save job vacancy');
    } finally {
        isLoading.value = false;
    }
}

async function deleteJob(id) {
    if (!confirm('Are you sure you want to delete this job vacancy?')) return;

    isLoading.value = true;
    try {
        await apiClient.delete(`/superadmin/job-vacancies/${id}`);
        alert('Job vacancy deleted successfully');
        await fetchJobs();
    } catch (error) {
        console.error('Error deleting job:', error);
        alert('Failed to delete job vacancy');
    } finally {
        isLoading.value = false;
    }
}

function getStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800',
        draft: 'bg-gray-100 text-gray-800',
        closed: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

onMounted(() => {
    fetchJobs();
});
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Job Vacancies Management</h2>
      <button
        @click="openModal()"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        :disabled="isLoading"
      >
        <i class="fas fa-plus mr-2"></i>Post New Job
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading && jobVacancies.length === 0" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading job vacancies...</p>
    </div>

    <!-- Jobs list -->
    <div v-else-if="jobVacancies.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="job in jobVacancies" :key="job.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ job.title }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ job.department }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ job.location }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ job.job_type }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ new Date(job.application_deadline).toLocaleDateString() }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(job.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ job.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="openModal(job)"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                  :disabled="isLoading"
                >
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button
                  @click="deleteJob(job.id)"
                  class="text-red-600 hover:text-red-900"
                  :disabled="isLoading"
                >
                  <i class="fas fa-trash"></i> Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- No jobs state -->
    <div v-else class="bg-white rounded-lg shadow p-6">
      <div class="text-center text-gray-500">
        <i class="fas fa-briefcase text-6xl text-gray-300 mb-4"></i>
        <p class="text-lg">No job vacancies posted yet.</p>
        <p class="text-sm mt-2">Click "Post New Job" to create your first job posting!</p>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeModal">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ editingJob ? 'Edit Job Vacancy' : 'Post New Job Vacancy' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveJob" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Senior Software Developer"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
              <input
                v-model="form.department"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Engineering"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
              <input
                v-model="form.location"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Lagos, Nigeria"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Job Type *</label>
              <select
                v-model="form.job_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="type in jobTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Salary Range</label>
              <input
                v-model="form.salary_range"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., ₦300,000 - ₦500,000/month"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Application Deadline *</label>
              <input
                v-model="form.application_deadline"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
              <select
                v-model="form.status"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="status in statuses" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
            </div>

            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Job Description *</label>
              <textarea
                v-model="form.description"
                required
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Describe the role and its purpose..."
              ></textarea>
            </div>

            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Requirements *</label>
              <textarea
                v-model="form.requirements"
                required
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="List the required qualifications and skills..."
              ></textarea>
            </div>

            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
              <textarea
                v-model="form.responsibilities"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="List the key responsibilities..."
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              :disabled="isLoading"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              :disabled="isLoading"
            >
              {{ isLoading ? 'Saving...' : (editingJob ? 'Update Job' : 'Post Job') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add any additional styling here if needed */
</style>

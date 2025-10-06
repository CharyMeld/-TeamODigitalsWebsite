<script setup>
import { ref, onMounted, computed } from 'vue';
import { apiClient } from '@/axiosConfig.js';

const applications = ref([]);
const isLoading = ref(false);
const selectedApp = ref(null);
const showModal = ref(false);
const filterStatus = ref('all');

const statuses = [
    { value: 'pending', label: 'Pending', color: 'bg-yellow-100 text-yellow-800' },
    { value: 'reviewed', label: 'Reviewed', color: 'bg-blue-100 text-blue-800' },
    { value: 'shortlisted', label: 'Shortlisted', color: 'bg-green-100 text-green-800' },
    { value: 'rejected', label: 'Rejected', color: 'bg-red-100 text-red-800' },
    { value: 'contacted', label: 'Contacted', color: 'bg-purple-100 text-purple-800' }
];

const filteredApplications = computed(() => {
    if (filterStatus.value === 'all') return applications.value;
    return applications.value.filter(app => app.status === filterStatus.value);
});

async function fetchApplications() {
    isLoading.value = true;
    try {
        const response = await apiClient.get('/superadmin/job-applications');
        applications.value = response.data;
    } catch (error) {
        console.error('Error fetching applications:', error);
        alert('Failed to fetch applications');
    } finally {
        isLoading.value = false;
    }
}

function viewApplication(app) {
    selectedApp.value = { ...app };
    showModal.value = true;
}

async function updateStatus() {
    if (!selectedApp.value) return;

    isLoading.value = true;
    try {
        await apiClient.put(`/superadmin/job-applications/${selectedApp.value.id}/status`, {
            status: selectedApp.value.status,
            admin_notes: selectedApp.value.admin_notes
        });
        alert('Application updated successfully');
        await fetchApplications();
        showModal.value = false;
    } catch (error) {
        console.error('Error updating application:', error);
        alert('Failed to update application');
    } finally {
        isLoading.value = false;
    }
}

async function deleteApplication(id) {
    if (!confirm('Are you sure you want to delete this application?')) return;

    isLoading.value = true;
    try {
        await apiClient.delete(`/superadmin/job-applications/${id}`);
        alert('Application deleted successfully');
        await fetchApplications();
    } catch (error) {
        console.error('Error deleting application:', error);
        alert('Failed to delete application');
    } finally {
        isLoading.value = false;
    }
}

function getStatusClass(status) {
    return statuses.find(s => s.value === status)?.color || 'bg-gray-100 text-gray-800';
}

function downloadResume(path) {
    window.open(`/storage/${path}`, '_blank');
}

function contactApplicant(email, phone) {
    const message = `Email: ${email}\nPhone: ${phone}`;
    if (confirm(`Contact applicant via:\n${message}\n\nCopy contact info?`)) {
        navigator.clipboard.writeText(message);
        alert('Contact info copied to clipboard!');
    }
}

onMounted(() => {
    fetchApplications();
});
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Job Applications</h2>
      <div class="flex gap-2">
        <button @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200'" class="px-3 py-1 rounded">All</button>
        <button v-for="s in statuses" :key="s.value" @click="filterStatus = s.value" :class="filterStatus === s.value ? 'bg-blue-600 text-white' : 'bg-gray-200'" class="px-3 py-1 rounded text-sm">{{ s.label }}</button>
      </div>
    </div>

    <div v-if="isLoading && applications.length === 0" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="filteredApplications.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Position</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Experience</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="app in filteredApplications" :key="app.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-gray-900">{{ app.full_name }}</div>
              <div class="text-sm text-gray-500">{{ app.email }}</div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">{{ app.job_vacancy?.title }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ app.years_of_experience || 'N/A' }} years</td>
            <td class="px-6 py-4">
              <span :class="getStatusClass(app.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">{{ app.status }}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(app.created_at).toLocaleDateString() }}</td>
            <td class="px-6 py-4 text-sm font-medium space-x-2">
              <button @click="viewApplication(app)" class="text-blue-600 hover:text-blue-900">View</button>
              <button @click="contactApplicant(app.email, app.phone)" class="text-green-600 hover:text-green-900">Contact</button>
              <button @click="deleteApplication(app.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
      No applications found
    </div>

    <!-- Modal -->
    <div v-if="showModal && selectedApp" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showModal = false">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium">Application Details</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div><strong>Name:</strong> {{ selectedApp.full_name }}</div>
          <div><strong>Email:</strong> <a :href="`mailto:${selectedApp.email}`" class="text-blue-600">{{ selectedApp.email }}</a></div>
          <div><strong>Phone:</strong> <a :href="`tel:${selectedApp.phone}`" class="text-blue-600">{{ selectedApp.phone }}</a></div>
          <div><strong>Experience:</strong> {{ selectedApp.years_of_experience || 'N/A' }} years</div>
          <div v-if="selectedApp.linkedin_url"><strong>LinkedIn:</strong> <a :href="selectedApp.linkedin_url" target="_blank" class="text-blue-600">View Profile</a></div>
          <div v-if="selectedApp.portfolio_url"><strong>Portfolio:</strong> <a :href="selectedApp.portfolio_url" target="_blank" class="text-blue-600">View Portfolio</a></div>
        </div>

        <div class="mb-4">
          <strong>Cover Letter:</strong>
          <p class="mt-2 p-3 bg-gray-50 rounded">{{ selectedApp.cover_letter }}</p>
        </div>

        <div class="mb-4">
          <button @click="downloadResume(selectedApp.resume_path)" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <i class="fas fa-download mr-2"></i>Download Resume
          </button>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="selectedApp.status" class="w-full px-3 py-2 border rounded">
            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Admin Notes</label>
          <textarea v-model="selectedApp.admin_notes" rows="3" class="w-full px-3 py-2 border rounded"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Cancel</button>
          <button @click="updateStatus" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" :disabled="isLoading">Update</button>
        </div>
      </div>
    </div>
  </div>
</template>

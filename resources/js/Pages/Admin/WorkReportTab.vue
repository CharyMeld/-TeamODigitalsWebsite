<script setup>
import { ref, onMounted } from 'vue';
import { apiClient } from "@/axiosConfig.js";

const props = defineProps({
  stats: Object
});

// State for work reports
const workReports = ref([]);
const loading = ref(false);
const error = ref('');
const searchQuery = ref('');
const selectedFilter = ref('all');

// Modal state
const showModal = ref(false);
const selectedReport = ref(null);

// Filters for work reports
const statusFilters = [
  { value: 'all', label: 'All Reports' },
  { value: 'pending', label: 'Pending Review' },
  { value: 'submitted', label: 'Submitted' },
  { value: 'in progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
];


const fetchWorkReports = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const { data } = await apiClient.get('/admin/work-reports'); 
    workReports.value = data.reports || data || [];
    console.log('Work reports fetched:', workReports.value);
  } catch (err) {
    console.error('Error fetching work reports:', err);
    error.value = err.response?.data?.message || err.message || 'Failed to load work reports';
  } finally {
    loading.value = false;
  }
};


const updateReportStatus = async (reportId, status, comments = '') => {
  try {
    console.log('Updating report:', reportId, 'Status:', status);
    
    const payload = {
      status: status,
      comments: comments
    };

    // Use POST method - matching your route definition
    const { data } = await apiClient.put(`/admin/work-reports/${reportId}/update`, payload);

    console.log('Update response:', data);

    // Update local state
    const reportIndex = workReports.value.findIndex(r => r.id === reportId);
    if (reportIndex !== -1) {
      workReports.value[reportIndex] = {
        ...workReports.value[reportIndex],
        status: status,
        comments: comments,
        reviewed_by_admin_id: data.reviewed_by_admin_id || null,
        reviewed_at: data.reviewed_at || new Date().toISOString()
      };
    }

    // Close modal if open
    showModal.value = false;
    selectedReport.value = null;

    alert(`Report ${status} successfully!`);
    
    // Refresh the list
    await fetchWorkReports();
  } catch (err) {
    console.error('Error updating report status:', err);
    console.error('Error response:', err.response);
    alert(err.response?.data?.message || 'Failed to update report status. Check console for details.');
  }
};

// Approve handler
const approveReport = (reportId) => {
  const comments = prompt('Approval comments (optional):');
  updateReportStatus(reportId, 'approved', comments || '');
};

// Reject handler
const rejectReport = (reportId) => {
  const comments = prompt('Please provide reason for rejection:');
  if (comments) {
    updateReportStatus(reportId, 'rejected', comments);
  }
};

// View details handler
const viewReportDetails = (report) => {
  selectedReport.value = report;
  showModal.value = true;
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  selectedReport.value = null;
};

// Approve from modal
const approveFromModal = () => {
  if (selectedReport.value) {
    const comments = prompt('Approval comments (optional):');
    updateReportStatus(selectedReport.value.id, 'approved', comments || '');
  }
};

// Reject from modal
const rejectFromModal = () => {
  if (selectedReport.value) {
    const comments = prompt('Please provide reason for rejection:');
    if (comments) {
      updateReportStatus(selectedReport.value.id, 'rejected', comments);
    }
  }
};

// Format date helper
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Get status badge class
const getStatusBadgeClass = (status) => {
  const statusLower = (status || '').toLowerCase();
  return {
    pending: 'bg-yellow-100 text-yellow-800',
    submitted: 'bg-blue-100 text-blue-800',
    'in progress': 'bg-indigo-100 text-indigo-800',
    completed: 'bg-purple-100 text-purple-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }[statusLower] || 'bg-gray-100 text-gray-800';
};

// Filtered reports computed
const filteredReports = () => {
  return workReports.value
    .filter(report => {
      if (selectedFilter.value === 'all') return true;
      const reportStatus = (report.status || '').toLowerCase();
      const filterValue = selectedFilter.value.toLowerCase();
      return reportStatus === filterValue;
    })
    .filter(report => {
      if (!searchQuery.value) return true;
      const query = searchQuery.value.toLowerCase();
      return (
        (report.employee_name || '').toLowerCase().includes(query) ||
        (report.task_title || '').toLowerCase().includes(query) ||
        (report.project_name || '').toLowerCase().includes(query)
      );
    });
};

onMounted(() => {
  console.log('Work Report component mounted');
  fetchWorkReports();
});
</script>


<template>
  <div class="p-6 bg-white shadow rounded-lg">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-xl font-semibold text-gray-900">Work Report Management</h3>
        <p class="text-gray-600 mt-1">View and manage daily work reports from employees</p>
      </div>
      <button 
        @click="fetchWorkReports"
        :disabled="loading"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex justify-between items-center">
        <span class="text-red-600 text-sm">{{ error }}</span>
        <button @click="error = ''" class="text-red-600 hover:text-red-800">&times;</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
      <input 
        v-model="searchQuery" 
        placeholder="Search reports..." 
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <select 
        v-model="selectedFilter" 
        class="sm:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option v-for="filter in statusFilters" :key="filter.value" :value="filter.value">
          {{ filter.label }}
        </option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Reports Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Employee
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Task/Project
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date Submitted
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Hours Worked
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="report in filteredReports()" :key="report.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ report.employee_name || 'Unknown' }}</div>
              <div class="text-sm text-gray-500">{{ report.employee_email || '' }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-gray-900">{{ report.task_title || 'N/A' }}</div>
              <div class="text-sm text-gray-500">{{ report.project_name || 'General' }}</div>
              <div class="text-xs text-gray-400 mt-1">{{ report.work_description || 'No description' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatDate(report.created_at || report.date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ report.hours_worked || '0' }}h
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                    :class="getStatusBadgeClass(report.status)">
                {{ report.status || 'pending' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button 
                  v-if="!report.status || report.status === 'pending'"
                  @click="approveReport(report.id)"
                  class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50"
                >
                  Approve
                </button>
                <button 
                  v-if="!report.status || report.status === 'pending'"
                  @click="rejectReport(report.id)"
                  class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50"
                >
                  Reject
                </button>
                <button 
                  @click="viewReportDetails(report)"
                  class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50"
                >
                  View Details
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="filteredReports().length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📋</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No work reports found</h3>
        <p class="text-gray-500">
          {{ searchQuery || selectedFilter !== 'all' ? 'Try adjusting your filters' : 'Work reports will appear here when submitted' }}
        </p>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-blue-50 p-4 rounded-lg">
        <div class="text-2xl font-bold text-blue-600">{{ workReports.length }}</div>
        <div class="text-blue-700 text-sm">Total Reports</div>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg">
        <div class="text-2xl font-bold text-yellow-600">
          {{ workReports.filter(r => {
            const status = (r.status || '').toLowerCase();
            return status === 'pending' || status === 'submitted' || status === 'in progress';
          }).length }}
        </div>
        <div class="text-yellow-700 text-sm">Pending Review</div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg">
        <div class="text-2xl font-bold text-green-600">
          {{ workReports.filter(r => (r.status || '').toLowerCase() === 'approved').length }}
        </div>
        <div class="text-green-700 text-sm">Approved</div>
      </div>
      <div class="bg-red-50 p-4 rounded-lg">
        <div class="text-2xl font-bold text-red-600">
          {{ workReports.filter(r => (r.status || '').toLowerCase() === 'rejected').length }}
        </div>
        <div class="text-red-700 text-sm">Rejected</div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <!-- Modal panel -->
        <div class="relative inline-block w-full max-w-3xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
          <!-- Close button -->
          <button 
            @click="closeModal"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Modal content -->
          <div v-if="selectedReport">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Work Report Details</h3>

            <div class="space-y-6">
              <!-- Employee Info -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Employee Information</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.employee_name || 'Unknown' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.employee_email || 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <!-- Task Info -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Task Information</h4>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-500">Task Title</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.task_title || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Project Name</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.project_name || 'General' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Task Type</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.task_type || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Priority</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.task_priority || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Work Location</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.work_location || 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <!-- Work Description -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Work Description</h4>
                <p class="text-gray-700 whitespace-pre-wrap">{{ selectedReport.work_description || selectedReport.task_description || 'No description provided' }}</p>
              </div>

              <!-- Time Info -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Time Information</h4>
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <p class="text-sm text-gray-500">Submitted</p>
                    <p class="font-medium text-gray-900">{{ formatDate(selectedReport.created_at || selectedReport.date) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Deadline</p>
                    <p class="font-medium text-gray-900">{{ formatDate(selectedReport.deadline) }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Hours Worked</p>
                    <p class="font-medium text-gray-900">{{ selectedReport.hours_worked || '0' }}h</p>
                  </div>
                </div>
              </div>

              <!-- Status -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Status & Review Information</h4>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm text-gray-500">Current Status</p>
                      <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full" 
                            :class="getStatusBadgeClass(selectedReport.status)">
                        {{ selectedReport.status || 'pending' }}
                      </span>
                    </div>
                  </div>
                  
                  <!-- Review Information -->
                  <div v-if="selectedReport.reviewed_at" class="grid grid-cols-2 gap-4 pt-3 border-t">
                    <div>
                      <p class="text-sm text-gray-500">Reviewed At</p>
                      <p class="font-medium text-gray-900">{{ formatDate(selectedReport.reviewed_at) }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500">Reviewed By Admin ID</p>
                      <p class="font-medium text-gray-900">{{ selectedReport.reviewed_by_admin_id || 'N/A' }}</p>
                    </div>
                  </div>
                  
                  <!-- Comments/Feedback -->
                  <div v-if="selectedReport.goals_met || selectedReport.issues_encountered" class="pt-3 border-t">
                    <p class="text-sm text-gray-500 mb-2">Review Comments</p>
                    <div v-if="selectedReport.goals_met" class="text-sm text-green-700 bg-green-50 p-2 rounded mb-2">
                      <strong>Goals Met:</strong> {{ selectedReport.goals_met }}
                    </div>
                    <div v-if="selectedReport.issues_encountered" class="text-sm text-red-700 bg-red-50 p-2 rounded">
                      <strong>Issues:</strong> {{ selectedReport.issues_encountered }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Attachment -->
              <div v-if="selectedReport.attachment" class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-3">Attachment</h4>
                <a :href="`/storage/${selectedReport.attachment}`" 
                   target="_blank" 
                   class="text-blue-600 hover:text-blue-800 underline">
                  View Attachment
                </a>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
              <button 
                @click="closeModal"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
              >
                Close
              </button>
              
              <!-- Show buttons if status is pending, submitted, or needs review -->
              <template v-if="!selectedReport.status || 
                             selectedReport.status === '' || 
                             selectedReport.status.toLowerCase() === 'pending' || 
                             selectedReport.status.toLowerCase() === 'submitted' ||
                             selectedReport.status.toLowerCase() === 'in progress' ||
                             selectedReport.status.toLowerCase() === 'completed'">
                <button 
                  @click="rejectFromModal"
                  class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                >
                  Reject
                </button>
                <button 
                  @click="approveFromModal"
                  class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                >
                  Approve
                </button>
              </template>
              
              <!-- Show status info if already approved/rejected -->
              <template v-else-if="selectedReport.status.toLowerCase() === 'approved' || 
                                   selectedReport.status.toLowerCase() === 'rejected'">
                <div class="text-sm text-gray-600 flex items-center">
                  <span>Already reviewed as: </span>
                  <span class="ml-2 inline-flex px-3 py-1 text-xs font-semibold rounded-full" 
                        :class="getStatusBadgeClass(selectedReport.status)">
                    {{ selectedReport.status }}
                  </span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { apiClient } from "@/axiosConfig.js";

// Determine route prefix based on URL path
const routePrefix = window.location.pathname.includes('/superadmin/') ? 'superadmin' : 'admin';

// Props from parent (stats if passed)
const props = defineProps({ stats: Object });

// State
const showLeaveForm = ref(false);
const leaveRequests = ref([]);
const loading = ref(false);
const selectedFilter = ref('all');
const searchQuery = ref('');
const formErrors = ref({});
const notifications = ref([]); // Replace alerts with notifications
const approvalComments = ref(''); // For approval modal

// Form model
const leaveForm = ref({
  leave_type: '',
  start_date: '',
  end_date: '',
  reason: '',
  attachment: null,
});

// Leave type options
const leaveTypes = [
  'Annual Leave',
  'Sick Leave',
  'Emergency Leave',
  'Maternity Leave',
  'Paternity Leave',
  'Study Leave',
  'Compassionate Leave',
  'Medical Leave',
];

// Status filters
const statusFilters = [
  { value: 'all', label: 'All Requests' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'declined', label: 'Declined' },
];

// Computed: filter + search
const filteredRequests = computed(() => {
  return leaveRequests.value
    .filter((req) => {
      if (selectedFilter.value === 'all') return true;
      return req.status && req.status.toLowerCase() === selectedFilter.value.toLowerCase();
    })
    .filter((req) => {
      if (!searchQuery.value) return true;
      const q = searchQuery.value.toLowerCase();
      return (
        (req.employee_name || '').toLowerCase().includes(q) ||
        (req.department || '').toLowerCase().includes(q) ||
        (req.leave_type || '').toLowerCase().includes(q)
      );
    });
});

// Computed: number of days
const numberOfDays = computed(() => {
  if (!leaveForm.value.start_date || !leaveForm.value.end_date) return 0;
  const start = new Date(leaveForm.value.start_date);
  const end = new Date(leaveForm.value.end_date);
  if (end < start) return 0;
  return Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
});

// --- Notification System (Replace alerts) ---
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

// --- Methods ---
function toggleLeaveForm() {
  showLeaveForm.value = !showLeaveForm.value;
  if (!showLeaveForm.value) resetForm();
}

function resetForm() {
  leaveForm.value = { leave_type: '', start_date: '', end_date: '', reason: '', attachment: null };
  formErrors.value = {};
}

function validateForm() {
  const errors = {};
  if (!leaveForm.value.leave_type) errors.leave_type = 'Leave type is required';
  if (!leaveForm.value.start_date) errors.start_date = 'Start date is required';
  if (!leaveForm.value.end_date) errors.end_date = 'End date is required';
  if (leaveForm.value.start_date && leaveForm.value.end_date &&
    new Date(leaveForm.value.start_date) > new Date(leaveForm.value.end_date)) {
    errors.end_date = 'End date must be after start date';
  }
  if (!leaveForm.value.reason || leaveForm.value.reason.trim().length < 10) {
    errors.reason = 'Reason must be at least 10 characters';
  }
  formErrors.value = errors;
  return Object.keys(errors).length === 0;
}

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (file && file.size > 5 * 1024 * 1024) { // 5MB limit
    showNotification('File size must be less than 5MB', 'error');
    event.target.value = ''; // Clear the input
    return;
  }
  leaveForm.value.attachment = file || null;
}

// FIXED: Better error handling, no alerts
async function fetchLeaveRequests() {
  if (loading.value) return; // Prevent concurrent requests

  loading.value = true;
  try {
    console.log('🔄 Fetching leave requests...');
    const res = await apiClient.get(`/${routePrefix}/leave-requests`);

    console.log('📦 API Response:', res.data);

    // Handle nested data structure from API
    const data = res.data.data || res.data;
    leaveRequests.value = Array.isArray(data) ? data : [];

    console.log('✅ Leave requests loaded:', leaveRequests.value.length);

    if (leaveRequests.value.length > 0) {
      console.log('📋 Sample request:', leaveRequests.value[0]);
    }

  } catch (err) {
    console.error('❌ Error fetching leave requests:', err);
    console.error('Error details:', err.response?.data);

    showNotification(
      err.response?.data?.message || 'Failed to load leave requests. Please try again.',
      'error'
    );

    // Don't clear existing data on error
    if (leaveRequests.value.length === 0) {
      leaveRequests.value = [];
    }
  } finally {
    loading.value = false;
  }
}

// FIXED: Better error handling, no alerts
async function submitLeaveRequest() {
  if (!validateForm() || loading.value) return;

  loading.value = true;
  try {
    const formData = new FormData();
    Object.entries(leaveForm.value).forEach(([k, v]) => {
      if (v !== null && v !== '') formData.append(k, v);
    });
    formData.append('number_of_days', numberOfDays.value);
    formData.append('superadmin', 'pending');

    console.log('🔄 Submitting leave request...');
    await apiClient.post(`/${routePrefix}/leave-requests`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    showNotification('Leave request submitted successfully!', 'success');
    toggleLeaveForm();
    
    // Refresh the list
    await fetchLeaveRequests();
    
  } catch (err) {
    console.error('❌ Error submitting leave request:', err);
    const errorMessage = err.response?.data?.message || 'Failed to submit leave request';
    showNotification(errorMessage, 'error');
  } finally {
    loading.value = false;
  }
}

// FIXED: Better error handling, no alerts
async function updateRequestStatus(id, status, comments = '') {
  if (loading.value) return;

  loading.value = true;
  try {
    // Capitalize status to match controller expectations (Approved/Declined)
    const capitalizedStatus = status.charAt(0).toUpperCase() + status.slice(1);
    console.log(`🔄 Updating request ${id} to ${capitalizedStatus}...`);
    await apiClient.put(`/${routePrefix}/leave-requests/${id}`, { status: capitalizedStatus, comments });
    
    // Update local state immediately for better UX
    const requestIndex = leaveRequests.value.findIndex(r => r.id === id);
    if (requestIndex !== -1) {
      leaveRequests.value[requestIndex] = {
        ...leaveRequests.value[requestIndex],
        status,
        comments,
        updated_at: new Date().toISOString()
      };
    }
    
    showNotification(`Leave request ${status} successfully!`, 'success');
    
  } catch (err) {
    console.error('❌ Error updating status:', err);
    showNotification('Failed to update leave request', 'error');
    
    // Refresh data on error to ensure consistency
    await fetchLeaveRequests();
  } finally {
    loading.value = false;
  }
}

// FIXED: Use custom modal instead of prompt
const approvalModal = ref({ show: false, requestId: null, type: '' });

function showApprovalModal(id, type) {
  approvalModal.value = { show: true, requestId: id, type };
  approvalComments.value = '';
}

function handleApprovalSubmit(comments) {
  const { requestId, type } = approvalModal.value;
  approvalModal.value = { show: false, requestId: null, type: '' };
  updateRequestStatus(requestId, type, comments);
}

function approveRequest(id) {
  showApprovalModal(id, 'approved');
}

function declineRequest(id) {
  showApprovalModal(id, 'declined');
}

// FIXED: Better download handling
async function downloadAttachment(attachmentPath) {
  if (!attachmentPath || loading.value) return;

  try {
    // Extract just the filename from the full path (e.g., "leave_attachments/file.pdf" -> "file.pdf")
    const filename = attachmentPath.split('/').pop();
    console.log('🔄 Downloading attachment:', filename);
    const res = await apiClient.get(`/${routePrefix}/leave-requests/download/${encodeURIComponent(filename)}`, {
      responseType: 'blob',
      timeout: 30000 // 30 second timeout for downloads
    });
    
    // Create download link
    const url = URL.createObjectURL(res.data);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.style.display = 'none';
    
    // Trigger download
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
    // Cleanup
    setTimeout(() => URL.revokeObjectURL(url), 1000);
    
    showNotification('Download started', 'success');
    
  } catch (err) {
    console.error('❌ Download failed:', err);
    showNotification('Failed to download attachment', 'error');
  }
}

function formatDate(date) {
  if (!date) return 'N/A';
  try {
    return new Date(date).toLocaleDateString();
  } catch {
    return 'Invalid Date';
  }
}

function getStatusBadgeClass(status) {
  if (!status) return 'bg-gray-100 text-gray-800';
  const statusLower = status.toLowerCase();
  return {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    declined: 'bg-red-100 text-red-800',
  }[statusLower] || 'bg-gray-100 text-gray-800';
}

// Lifecycle - Add error boundary
onMounted(async () => {
  try {
    console.log('🔄 Leave Management component mounted');
    await fetchLeaveRequests();
  } catch (err) {
    console.error('❌ Error during component mount:', err);
    showNotification('Failed to initialize leave management', 'error');
  }
});
</script>

<template>
  <div class="p-6 bg-white shadow rounded-lg relative">
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

    <!-- Approval Modal -->
    <div v-if="approvalModal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
      <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">
          {{ approvalModal.type === 'approved' ? 'Approve Request' : 'Decline Request' }}
        </h3>
        <textarea 
          v-model="approvalComments"
          :placeholder="approvalModal.type === 'approved' ? 'Approval comments (optional)' : 'Please provide reason for decline'"
          class="w-full p-3 border rounded-lg mb-4"
          rows="3"
        ></textarea>
        <div class="flex justify-end space-x-3">
          <button 
            @click="approvalModal.show = false"
            class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            @click="handleApprovalSubmit(approvalComments)"
            class="px-4 py-2 text-white rounded-lg"
            :class="approvalModal.type === 'approved' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
          >
            {{ approvalModal.type === 'approved' ? 'Approve' : 'Decline' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-semibold text-gray-900">Leave Management</h3>
      <button @click="toggleLeaveForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        {{ showLeaveForm ? 'Cancel' : 'Apply for Leave' }}
      </button>
    </div>
    
    <!-- Admin Leave Application Form (Expandable) -->
    <div v-if="showLeaveForm" class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50">
      <h4 class="text-lg font-medium text-gray-900 mb-4">Apply for Leave</h4>
      <p class="text-sm text-blue-600 mb-4">
        <i class="fas fa-info-circle"></i>
        Your leave request will be sent to the superadmin for approval.
      </p>
      
      <form @submit.prevent="submitLeaveRequest" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Leave Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type *</label>
            <select 
              v-model="leaveForm.leave_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': formErrors.leave_type }"
            >
              <option value="">Select Leave Type</option>
              <option v-for="type in leaveTypes" :key="type" :value="type">{{ type }}</option>
            </select>
            <p v-if="formErrors.leave_type" class="text-red-500 text-xs mt-1">{{ formErrors.leave_type }}</p>
          </div>

          <!-- Start Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
            <input 
              type="date" 
              v-model="leaveForm.start_date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': formErrors.start_date }"
            >
            <p v-if="formErrors.start_date" class="text-red-500 text-xs mt-1">{{ formErrors.start_date }}</p>
          </div>

          <!-- End Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
            <input 
              type="date" 
              v-model="leaveForm.end_date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': formErrors.end_date }"
            >
            <p v-if="formErrors.end_date" class="text-red-500 text-xs mt-1">{{ formErrors.end_date }}</p>
          </div>

          <!-- Number of Days (Auto-calculated) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Days</label>
            <input 
              type="number" 
              :value="numberOfDays"
              readonly
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
            >
          </div>
        </div>

        <!-- Reason -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
          <textarea 
            v-model="leaveForm.reason"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': formErrors.reason }"
            placeholder="Please provide a detailed reason for your leave request..."
          ></textarea>
          <p v-if="formErrors.reason" class="text-red-500 text-xs mt-1">{{ formErrors.reason }}</p>
        </div>

        <!-- Attachment -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (Optional)</label>
          <input 
            type="file" 
            @change="handleFileUpload"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
          <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 5MB)</p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pt-4">
          <button 
            type="button"
            @click="toggleLeaveForm"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            type="submit"
            :disabled="loading"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Submitting...' : 'Submit Request' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 mb-4">
      <input 
        v-model="searchQuery" 
        placeholder="Search..." 
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <select 
        v-model="selectedFilter" 
        class="sm:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option v-for="f in statusFilters" :key="f.value" :value="f.value">{{ f.label }}</option>
      </select>
    </div>

    <!-- Requests Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Employee
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Leave Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Start Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              End Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Days
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
          <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ request.employee_name || 'Unknown' }}</div>
              <div class="text-sm text-gray-500">{{ request.department || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.leave_type }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(request.start_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(request.end_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.number_of_days }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeClass(request.status)">
                {{ request.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button
                  v-if="request.status && request.status.toLowerCase() === 'pending'"
                  @click="approveRequest(request.id)"
                  class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50"
                >
                  Approve
                </button>
                <button
                  v-if="request.status && request.status.toLowerCase() === 'pending'"
                  @click="declineRequest(request.id)"
                  class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50"
                >
                  Decline
                </button>
                <button
                  v-if="request.attachment"
                  @click="downloadAttachment(request.attachment)"
                  class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50"
                >
                  Download
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="filteredRequests.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📋</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No leave requests found</h3>
        <p class="text-gray-500">
          {{ searchQuery || selectedFilter !== 'all' ? 'Try adjusting your filters' : 'Leave requests will appear here when submitted' }}
        </p>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
      <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="text-blue-600 font-medium">Loading...</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.table-container { 
  position: relative; 
}

@media (max-width: 768px) {
  .table-container { 
    overflow-x: auto; 
  }
}

/* Loading overlay improvements */
.bg-opacity-75 {
  background-color: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(1px);
}

/* Notification animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

/* Modal backdrop */
.modal-backdrop {
  backdrop-filter: blur(2px);
}

/* Button hover effects */
button:hover {
  transform: translateY(-1px);
  transition: all 0.2s ease;
}

button:active {
  transform: translateY(0);
}

/* Table row hover effects */
tbody tr:hover {
  background-color: #f9fafb;
  transition: background-color 0.2s ease;
}

/* Form input focus effects */
input:focus,
select:focus,
textarea:focus {
  box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.1);
  transition: box-shadow 0.2s ease;
}

/* Status badge animations */
.status-badge {
  transition: all 0.2s ease;
}

/* File input styling */
input[type="file"] {
  cursor: pointer;
}

input[type="file"]:hover {
  background-color: #f9fafb;
}

/* Loading spinner */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive table */
@media (max-width: 640px) {
  .table-responsive {
    font-size: 0.875rem;
  }
  
  .table-responsive th,
  .table-responsive td {
    padding: 0.5rem;
  }
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>


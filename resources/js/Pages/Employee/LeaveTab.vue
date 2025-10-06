<template>
  <div class="max-w-6xl mx-auto p-6 bg-white shadow rounded-lg space-y-8">
    <h2 class="text-2xl font-bold mb-6">Leave Request Form</h2>

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ errorMessage }}
    </div>

    <!-- Leave Form -->
    <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-4">
      <!-- Employee Info (auto-filled) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input v-model="form.employee_name" placeholder="Employee Name" disabled class="input bg-gray-100"/>
        <input v-model="form.employee_id" placeholder="Employee ID" disabled class="input bg-gray-100"/>
        <input v-model="form.department" placeholder="Department" disabled class="input bg-gray-100"/>
        <input v-model="form.job_title" placeholder="Job Title" disabled class="input bg-gray-100"/>
        <input v-model="form.contact" placeholder="Contact" disabled class="input bg-gray-100"/>
      </div>

      <!-- Leave Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <select v-model="form.leave_type" required class="input">
          <option disabled value="">Select Leave Type</option>
          <option>Annual Leave</option>
          <option>Sick Leave</option>
          <option>Emergency Leave</option>
          <option>Maternity Leave</option>
          <option>Paternity Leave</option>
          <option>Study Leave</option>
          <option>Compassionate Leave</option>
          <option>Medical Leave</option>
        </select>

        <input type="date" v-model="form.start_date" :min="today" @change="calculateDays" required class="input"/>
        <input type="date" v-model="form.end_date" :min="form.start_date || today" @change="calculateDays" required class="input"/>
      </div>

      <textarea v-model="form.reason" placeholder="Reason (minimum 10 characters)" required minlength="10" class="input w-full"></textarea>

      <!-- Number of Days (calculated) -->
      <div class="text-gray-700">
        Number of Days: <strong>{{ form.number_of_days || 0 }}</strong>
      </div>

      <!-- Optional Attachment -->
      <div>
        <label class="block mb-2">Supporting Document (Optional - PDF, DOC, DOCX, JPG, PNG, max 5MB)</label>
        <input type="file" @change="handleFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="input"/>
      </div>

      <!-- Employee Acknowledgement -->
      <div>
        <label class="inline-flex items-center">
          <input type="checkbox" v-model="form.employee_acknowledgement" class="mr-2"/>
          I acknowledge the correctness of this request
        </label>
      </div>

      <button type="submit" :disabled="loading" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 disabled:opacity-50">
        {{ loading ? 'Submitting...' : 'Submit Leave Request' }}
      </button>
    </form>

    <!-- Employee Leave Requests Table -->
    <div class="mt-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">My Leave Requests</h3>
        <button @click="fetchLeaveRequests" :disabled="fetchingRequests" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
          {{ fetchingRequests ? 'Loading...' : 'Refresh' }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="fetchingRequests" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>

      <!-- Table -->
      <div v-else-if="leaveRequests.length > 0" class="bg-white border-2 border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="request in leaveRequests" :key="request.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.leave_type }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(request.start_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(request.end_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.number_of_days }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="statusClass(request.status)">
                    {{ request.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ request.comments || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button
                    v-if="request.status === 'Pending' || request.status === 'pending'"
                    @click="cancelRequest(request.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Cancel
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
        <div class="text-gray-400 text-5xl mb-4">📋</div>
        <p class="text-gray-500">No leave requests yet. Submit your first leave request above.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3'
import axios from 'axios'

// Configure axios to include CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
}
axios.defaults.withCredentials = true

export default {
  props: {
    user: { type: Object, required: true }
  },
  data() {
    return {
      loading: false,
      fetchingRequests: false,
      successMessage: '',
      errorMessage: '',
      today: new Date().toISOString().split('T')[0],
      leaveRequests: [],
      form: {
        employee_name: this.user.name,
        employee_id: this.user.employee_id || '',
        department: this.user.department || '',
        job_title: this.user.role || '',
        contact: this.user.email || '', 
        leave_type: '',
        start_date: '',
        end_date: '',
        reason: '',
        number_of_days: 0,
        attachment: null,
        employee_acknowledgement: false,
      }
    }
  },
  mounted() {
    this.fetchLeaveRequests()
  },
  methods: {
    async fetchLeaveRequests() {
      this.fetchingRequests = true
      try {
        const response = await axios.get('/employee/leave-requests')
        if (response.data.success) {
          this.leaveRequests = response.data.data || []
        }
      } catch (error) {
        console.error('Error fetching leave requests:', error)
        this.errorMessage = 'Failed to load leave requests'
        setTimeout(() => { this.errorMessage = '' }, 5000)
      } finally {
        this.fetchingRequests = false
      }
    },

    handleFile(event) {
      const file = event.target.files[0]
      if (file) {
        // Validate file size
        if (file.size > 5 * 1024 * 1024) {
          this.errorMessage = 'File size must be less than 5MB'
          event.target.value = ''
          setTimeout(() => { this.errorMessage = '' }, 5000)
          return
        }
        this.form.attachment = file
      }
    },

    calculateDays() {
      if (this.form.start_date && this.form.end_date) {
        const start = new Date(this.form.start_date)
        const end = new Date(this.form.end_date)
        const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
        this.form.number_of_days = diff > 0 ? diff : 0
      }
    },

    async submitForm() {
      // Clear messages
      this.successMessage = ''
      this.errorMessage = ''

      // Validation
      if (!this.form.leave_type || !this.form.start_date || !this.form.end_date || !this.form.reason) {
        this.errorMessage = 'Please fill in all required fields.'
        return
      }

      if (this.form.reason.length < 10) {
        this.errorMessage = 'Reason must be at least 10 characters long.'
        return
      }

      this.loading = true
      try {
        const fd = new FormData()
        fd.append('leave_type', this.form.leave_type)
        fd.append('start_date', this.form.start_date)
        fd.append('end_date', this.form.end_date)
        fd.append('reason', this.form.reason)

        if (this.form.attachment) {
          fd.append('attachment', this.form.attachment)
        }

        const response = await axios.post('/employee/leave-requests/submit', fd, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.success) {
          this.successMessage = 'Leave request submitted successfully!'
          
          // Reset form
          this.form.leave_type = ''
          this.form.start_date = ''
          this.form.end_date = ''
          this.form.reason = ''
          this.form.number_of_days = 0
          this.form.attachment = null
          this.form.employee_acknowledgement = false

          // Reset file input
          const fileInput = document.querySelector('input[type="file"]')
          if (fileInput) fileInput.value = ''

          // Refresh leave requests
          await this.fetchLeaveRequests()

          // Clear success message after 5 seconds
          setTimeout(() => { this.successMessage = '' }, 5000)
        }
      } catch (error) {
        console.error('Submission failed:', error)
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors
          const messages = Object.values(errors).flat().join(', ')
          this.errorMessage = `Validation error: ${messages}`
        } else if (error.response?.data?.message) {
          this.errorMessage = error.response.data.message
        } else {
          this.errorMessage = 'Failed to submit leave request. Please try again.'
        }
      } finally {
        this.loading = false
      }
    },

    async cancelRequest(id) {
      if (!confirm('Are you sure you want to cancel this leave request?')) {
        return
      }

      try {
        const response = await axios.delete(`/employee/leave-requests/${id}`)
        if (response.data.success) {
          this.successMessage = 'Leave request cancelled successfully!'
          await this.fetchLeaveRequests()
          setTimeout(() => { this.successMessage = '' }, 5000)
        }
      } catch (error) {
        console.error('Cancel failed:', error)
        this.errorMessage = error.response?.data?.message || 'Failed to cancel leave request'
        setTimeout(() => { this.errorMessage = '' }, 5000)
      }
    },

    formatDate(date) {
      if (!date) return '-'
      try {
        return new Date(date).toLocaleDateString('en-US', {
          year: 'numeric', month: 'short', day: 'numeric'
        })
      } catch {
        return date
      }
    },

    statusClass(status) {
      const statusLower = (status || '').toLowerCase()
      return {
        'approved': 'bg-green-100 text-green-800',
        'declined': 'bg-red-100 text-red-800',
        'pending': 'bg-yellow-100 text-yellow-800',
      }[statusLower] || 'bg-gray-100 text-gray-800'
    }
  }
}
</script>

<style>
.input {
  border: 1px solid #ccc;
  padding: 0.5rem;
  border-radius: 0.375rem;
  width: 100%;
}
</style>

<template>
  <div class="p-6 space-y-6">
    <!-- Header Section -->
    <header class="flex flex-col md:flex-row justify-between md:items-center gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Employee Attendance</h2>
        <p class="text-gray-600">Monitor and track employee attendance</p>
      </div>

      <!-- Date Selector -->
      <div class="flex items-center space-x-4">
        <label for="date-select" class="text-sm font-medium text-gray-700">Date:</label>
        <input
          id="date-select"
          type="date"
          v-model="selectedDate"
          @change="refreshData"
          class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <button
          @click="setToday"
          class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
        >
          Today
        </button>
      </div>
    </header>

    <!-- Alerts -->
    <transition name="fade">
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 flex justify-between items-center">
        <span class="text-red-600 text-sm">{{ error }}</span>
        <button @click="clearError" class="text-red-600 hover:text-red-800">×</button>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 flex justify-between items-center">
        <span class="text-green-600 text-sm">{{ successMessage }}</span>
        <button @click="clearSuccess" class="text-green-600 hover:text-green-800">×</button>
      </div>
    </transition>

    <!-- Admin Attendance Section -->
    <section class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6 space-y-4">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="text-lg font-semibold text-indigo-900">Your Attendance</h3>
          <p class="text-sm text-indigo-700">{{ formatDisplayDate(selectedDate) }}</p>
        </div>
        <div class="text-right">
          <div class="text-2xl font-bold text-indigo-600">
            {{ formatMinutesToHM(adminAttendance.working_hours) }}
          </div>
          <div class="text-sm text-indigo-700">Hours Worked</div>
        </div>
      </div>

      <!-- Attendance Actions Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Status + Sign In/Out -->
        <div class="bg-white rounded-lg p-4 border border-indigo-100 flex flex-col justify-between">
          <div>
            <p class="text-sm text-gray-600">Status</p>
            <span
              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1"
              :class="getStatusClass(adminAttendance.status)"
            >
              {{ adminAttendance.status || 'Not Signed In' }}
            </span>
          </div>

          <div class="mt-4">
            <!-- Sign In Button - Only show if not signed in yet -->
            <button
              v-if="canSignIn"
              @click="signIn"
              :disabled="attendanceLoading"
              class="w-full px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50 transition"
            >
              {{ attendanceLoading ? 'Signing In...' : 'Sign In' }}
            </button>

            <!-- Sign Out Button - Only show if signed in but not signed out -->
            <button
              v-else-if="canSignOut"
              @click="signOut"
              :disabled="attendanceLoading"
              class="w-full px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50 transition"
            >
              {{ attendanceLoading ? 'Signing Out...' : 'Sign Out' }}
            </button>

            <!-- Day Complete Message -->
            <div v-else-if="isDayComplete" class="text-center">
              <span class="text-sm text-gray-500 font-medium">Day Complete</span>
              <p class="text-xs text-gray-400 mt-1">Signed out at {{ adminAttendance.check_out_time }}</p>
            </div>

            <!-- Fallback -->
            <span v-else class="text-sm text-gray-500">Loading...</span>
          </div>
        </div>

        <!-- Sign In Time -->
        <div class="bg-white rounded-lg p-4 border border-indigo-100">
          <p class="text-sm text-gray-600">Sign In Time</p>
          <p class="text-lg font-medium text-gray-900">
            {{ adminAttendance.check_in_time || '-' }}
          </p>
        </div>

        <!-- Sign Out Time -->
        <div class="bg-white rounded-lg p-4 border border-indigo-100">
          <p class="text-sm text-gray-600">Sign Out Time</p>
          <p class="text-lg font-medium text-gray-900">
            {{ adminAttendance.check_out_time || '-' }}
          </p>
        </div>

        <!-- Break Controls -->
        <div class="bg-white rounded-lg p-4 border border-indigo-100">
          <p class="text-sm text-gray-600 mb-2">Break Status</p>

          <!-- Currently On Break -->
          <template v-if="isOnBreak">
            <p class="text-sm text-orange-600 font-medium mb-2">On Break</p>
            <p class="text-xs text-gray-500 mb-2">Started: {{ adminAttendance.current_break_start }}</p>
            <button
              @click="endBreak"
              :disabled="attendanceLoading"
              class="w-full px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700 disabled:opacity-50 transition"
            >
              {{ attendanceLoading ? 'Ending...' : 'End Break' }}
            </button>
          </template>

          <!-- Can Start Break -->
          <template v-else-if="canTakeBreak">
            <button
              @click="startBreak"
              :disabled="attendanceLoading"
              class="w-full px-3 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 disabled:opacity-50 transition"
            >
              {{ attendanceLoading ? 'Starting...' : 'Start Break' }}
            </button>
            <p class="mt-2 text-xs text-gray-500">
              Total Break: {{ formatMinutesToHM(adminAttendance.total_break_time) }}
            </p>
          </template>

          <!-- Cannot Take Break -->
          <template v-else>
            <p class="text-sm text-gray-500">
              {{ getBreakStatusMessage() }}
            </p>
            <p v-if="adminAttendance.total_break_time > 0" class="mt-1 text-xs text-gray-400">
              Total Break: {{ formatMinutesToHM(adminAttendance.total_break_time) }}
            </p>
          </template>
        </div>
      </div>
    </section>

    <!-- Rest of your template remains the same -->
    <!-- Search & Table -->
    <section class="bg-white rounded-lg shadow overflow-hidden">
      <header class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">
          Employee Attendance for {{ formatDisplayDate(selectedDate) }}
        </h3>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search employees..."
          class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </header>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th v-for="heading in ['Employee','Department','Status','Sign In','Sign Out','Break','Hours Worked']"
                  :key="heading"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ heading }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading attendance data...</td>
            </tr>
            <tr v-else-if="paginatedEmployees.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">No employees found</td>
            </tr>
            <tr v-else v-for="employee in paginatedEmployees" :key="employee.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ employee.name }}</p>
                  <p class="text-sm text-gray-500">{{ employee.email }}</p>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ employee.department }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusClass(employee.status)"
                >
                  {{ employee.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.check_in_time || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.check_out_time || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <template v-if="employee.break_status === 'on_break'">
                  <span class="text-orange-600 text-xs">On Break</span>
                </template>
                <template v-else-if="employee.total_break_time > 0">
                  {{ formatMinutesToHM(employee.total_break_time) }}
                </template>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatMinutesToHM(employee.working_hours) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <footer class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-gray-200">
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-500">
            Showing {{ startItem + 1 }}—{{ endItem }} of {{ filteredEmployees.length }}
          </span>
          
          <!-- Records per page selector -->
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Show:</label>
            <select 
              v-model="itemsPerPage" 
              @change="currentPage = 1"
              class="border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="30">30</option>
              <option :value="40">40</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            <span class="text-sm text-gray-600">per page</span>
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1" 
            class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50 hover:bg-gray-300 transition"
          >
            Prev
          </button>
          <span class="px-3 py-1 text-sm text-gray-600">
            Page {{ currentPage }} of {{ Math.ceil(filteredEmployees.length / itemsPerPage) || 1 }}
          </span>
          <button 
            @click="nextPage" 
            :disabled="endItem >= filteredEmployees.length" 
            class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50 hover:bg-gray-300 transition"
          >
            Next
          </button>
        </div>
      </footer>
    </section>

    <!-- Export/Actions -->
    <footer class="flex justify-between items-center">
      <span class="text-sm text-gray-500">Showing {{ employees.length }} employees</span>
      <div class="flex space-x-2">
        <button @click="exportToCSV" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
          Export CSV
        </button>
        <button @click="refreshData" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 disabled:opacity-50">
          {{ loading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { makeRequest } from "@/axiosConfig.js";

const employees = ref([])
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const loading = ref(false)
const attendanceLoading = ref(false)
const error = ref('')
const successMessage = ref('')

const adminAttendance = ref({
  check_in_time: null,
  check_out_time: null,
  current_break_start: null,
  break_status: 'none',
  total_break_time: 0,
  working_hours: 0,
  status: 'Not Signed In'
})

const selectedDate = ref(new Date().toISOString().split('T')[0])

// Computed properties for better state management
const canSignIn = computed(() => {
  return !adminAttendance.value.check_in_time && !adminAttendance.value.check_out_time
})

const canSignOut = computed(() => {
  return adminAttendance.value.check_in_time && 
         !adminAttendance.value.check_out_time && 
         adminAttendance.value.break_status !== 'on_break'
})

const isDayComplete = computed(() => {
  return adminAttendance.value.check_in_time && adminAttendance.value.check_out_time
})

const isOnBreak = computed(() => {
  return adminAttendance.value.break_status === 'on_break'
})

const canTakeBreak = computed(() => {
  return adminAttendance.value.check_in_time && 
         !adminAttendance.value.check_out_time && 
         adminAttendance.value.break_status !== 'on_break'
})

const isToday = computed(() => {
  return selectedDate.value === new Date().toISOString().split('T')[0]
})

function getBreakStatusMessage() {
  if (!adminAttendance.value.check_in_time) {
    return 'Sign in first'
  } else if (adminAttendance.value.check_out_time) {
    return 'Day Complete'
  } else if (adminAttendance.value.break_status === 'on_break') {
    return 'Currently on break'
  } else {
    return 'Available'
  }
}

async function fetchAttendanceData() {
  loading.value = true
  error.value = ''
  try {
    const res = await makeRequest({
      method: 'GET',
      url: '/admin/attendance/employees',
      params: { date: selectedDate.value }
    })
    employees.value = res.data.employees || []
  } catch (err) {
    console.error('Error fetching attendance data:', err)
    if (err.response?.status === 401) {
      error.value = 'Session expired. Please login again.'
      setTimeout(() => window.location.href = '/login', 2000)
      return
    }
    error.value = err.response?.data?.message || err.message || 'Failed to fetch attendance data'
    employees.value = []
  } finally {
    loading.value = false
  }
}

async function fetchAdminAttendance() {
  try {
    const res = await makeRequest({
      method: 'GET',
      url: '/admin/attendance/my-attendance',
      params: { date: selectedDate.value }
    })
    
    // Reset to default state first
    adminAttendance.value = {
      check_in_time: null,
      check_out_time: null,
      current_break_start: null,
      break_status: 'none',
      total_break_time: 0,
      working_hours: 0,
      status: 'Not Signed In'
    }
    
    // Then update with fetched data
    if (res.data.attendance) {
      adminAttendance.value = { ...adminAttendance.value, ...res.data.attendance }
    }
    
    updateAdminStatus()
  } catch (err) {
    console.error('Error fetching admin attendance:', err)
    if (err.response?.status === 401) {
      error.value = 'Session expired. Please login again.'
      setTimeout(() => window.location.href = '/login', 2000)
      return
    }
    error.value = err.response?.data?.message || err.message || 'Failed to fetch your attendance data'
  }
}

async function handleAttendanceAction(endpoint, defaultMessage) {
  attendanceLoading.value = true
  error.value = ''
  successMessage.value = ''
  
  try {
    const res = await makeRequest({ 
      method: 'POST', 
      url: endpoint, 
      data: { date: selectedDate.value } 
    })
    
    console.log('✅ Response received from:', endpoint)
    console.log('📦 Full response data:', res.data)
    
    if (res.data.attendance) {
      adminAttendance.value = { ...adminAttendance.value, ...res.data.attendance }
      console.log('💾 Updated adminAttendance:', adminAttendance.value)
    }
    
    updateAdminStatus()
    successMessage.value = res.data.message || defaultMessage
    
    // Refresh data to ensure consistency
    setTimeout(() => {
      fetchAdminAttendance()
    }, 500)
    
  } catch (err) {
    console.error(`Error in ${endpoint}:`, err)
    console.error('Response data:', err.response?.data)
    
    if (err.response?.status === 401) {
      error.value = 'Session expired. Please login again.'
      setTimeout(() => window.location.href = '/login', 2000)
      return
    }
    
    error.value = err.response?.data?.message || err.response?.data?.error || err.message || 'Attendance action failed'
  } finally {
    attendanceLoading.value = false
  }
}

const signIn = () => handleAttendanceAction('/admin/attendance/sign-in', 'Successfully signed in')
const signOut = () => handleAttendanceAction('/admin/attendance/sign-out', 'Successfully signed out')
const startBreak = () => handleAttendanceAction('/admin/attendance/start-break', 'Break started successfully')
const endBreak = () => handleAttendanceAction('/admin/attendance/end-break', 'Break ended successfully')

function updateAdminStatus() {
  const a = adminAttendance.value
  if (!a.check_in_time) {
    a.status = 'Not Signed In'
  } else if (a.break_status === 'on_break') {
    a.status = 'On Break'
  } else if (a.check_out_time) {
    a.status = 'Signed Out'
  } else {
    a.status = 'Present'
  }
}

function clearError() { 
  error.value = '' 
}

function clearSuccess() { 
  successMessage.value = '' 
}

const filteredEmployees = computed(() =>
  employees.value.filter(e =>
    e.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    e.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
)

const startItem = computed(() => (currentPage.value - 1) * itemsPerPage.value)
const endItem = computed(() => {
  const end = startItem.value + itemsPerPage.value
  return Math.min(end, filteredEmployees.value.length)
})
const paginatedEmployees = computed(() =>
  filteredEmployees.value.slice(startItem.value, endItem.value)
)

function prevPage() { 
  if (currentPage.value > 1) currentPage.value-- 
}

function nextPage() { 
  if (endItem.value < filteredEmployees.value.length) currentPage.value++ 
}

function setToday() { 
  selectedDate.value = new Date().toISOString().split('T')[0]
  refreshData() 
}

function refreshData() {
  // Clear current state
  adminAttendance.value = {
    check_in_time: null,
    check_out_time: null,
    current_break_start: null,
    break_status: 'none',
    total_break_time: 0,
    working_hours: 0,
    status: 'Not Signed In'
  }
  
  // Fetch fresh data
  fetchAttendanceData()
  fetchAdminAttendance()
}

function exportToCSV() {
  try {
    const headers = ['Employee', 'Email', 'Department', 'Status', 'Sign In', 'Sign Out', 'Break Time', 'Hours Worked']
    
    const rows = filteredEmployees.value.map(emp => [
      emp.name,
      emp.email,
      emp.department,
      emp.status,
      emp.check_in_time || '-',
      emp.check_out_time || '-',
      formatMinutesToHM(emp.total_break_time),
      formatMinutesToHM(emp.working_hours)
    ])
    
    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `attendance_${selectedDate.value}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    successMessage.value = 'CSV exported successfully'
  } catch (err) {
    console.error('Error exporting CSV:', err)
    error.value = 'Failed to export CSV'
  }
}

function getStatusClass(status) {
  if (typeof status === 'object' && status.status) {
    status = status.status
  }
  return {
    'Present': 'bg-green-100 text-green-800',
    'Absent': 'bg-red-100 text-red-800',
    'On Break': 'bg-yellow-100 text-yellow-800',
    'Signed Out': 'bg-gray-100 text-gray-800',
    'Not Signed In': 'bg-gray-100 text-gray-600',
    'Late': 'bg-orange-100 text-orange-800'
  }[status] || 'bg-gray-100 text-gray-800'
}

function formatDisplayDate(d) {
  try {
    return new Date(d).toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch (e) {
    console.warn('Invalid date format:', d)
    return d
  }
}

function formatMinutesToHM(minutes) {
  if (!minutes || minutes <= 0) return '0m'
  if (minutes < 60) return `${minutes}m`
  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60
  return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`
}

onMounted(() => {
  refreshData()
})

onUnmounted(() => {
  employees.value = []
  adminAttendance.value = {
    check_in_time: null,
    check_out_time: null,
    current_break_start: null,
    break_status: 'none',
    total_break_time: 0,
    working_hours: 0,
    status: 'Not Signed In'
  }
  searchQuery.value = ''
  currentPage.value = 1
  error.value = ''
  successMessage.value = ''
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>


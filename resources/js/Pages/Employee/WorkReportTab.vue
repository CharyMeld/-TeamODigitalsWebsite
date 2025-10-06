<template>
  <div class="work-report-container">
    <div class="bg-white rounded-lg shadow-sm">
      <!-- Header -->
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-900 flex items-center">
          📋 My Assigned Tasks
        </h2>
        <p class="text-gray-600 mt-1">View and complete your assigned tasks</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-6 text-center">
        <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Loading tasks...
        </div>
      </div>

      <!-- Success/Error Messages -->
      <div v-if="successMessage" class="mx-6 mt-6">
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="mx-6 mt-6">
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tasks List -->
      <div class="p-6">
        <div v-if="!loading && tasks.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">📋</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks assigned</h3>
          <p class="text-gray-500">No tasks assigned at the moment.</p>
        </div>

        <div v-else-if="!loading" class="space-y-4">
          <div v-for="task in tasks" :key="task.id" class="border border-gray-200 rounded-lg overflow-hidden">
            <!-- Task Card Header -->
            <div class="p-4 bg-gray-50 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <h3 class="text-lg font-medium text-gray-900">{{ task.task_title }}</h3>
                  <div class="mt-2 flex items-center space-x-6 text-sm text-gray-500">
                    <span class="flex items-center">
                      <span class="font-medium">Priority:</span>
                      <span :class="getPriorityClass(task.task_priority)" class="ml-1 px-2 py-1 rounded-full text-xs font-medium">
                        {{ task.task_priority }}
                      </span>
                    </span>
                    <span class="flex items-center">
                      <span class="font-medium">Deadline:</span>
                      <span class="ml-1">{{ formatDate(task.deadline) }}</span>
                    </span>
                    <span class="flex items-center">
                      <span class="font-medium">Type:</span>
                      <span class="ml-1">{{ task.task_type }}</span>
                    </span>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <a v-if="task.attachment" 
                     :href="task.attachment" 
                     target="_blank"
                     class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    📎 View Attachment
                  </a>
                  <button @click="toggleTaskCompletion(task.id)"
                          :class="expandedTaskId === task.id ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition-colors">
                    {{ expandedTaskId === task.id ? '❌ Cancel' : '📝 Complete Task' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Task Description -->
            <div class="p-4 bg-white">
              <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Task Description:</h4>
                <p class="text-gray-700 whitespace-pre-wrap">{{ task.task_description }}</p>
              </div>
            </div>

            <!-- Expandable Task Completion Form -->
            <transition name="slide-down">
              <div v-if="expandedTaskId === task.id" class="border-t border-gray-200 bg-gray-50">
                <div class="p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">📥 Complete Task Report</h4>
                  
                  <form @submit.prevent="submitTaskReport(task)" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- Files Worked On -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                          Files Worked On <span class="text-red-500">*</span>
                        </label>
                        <input v-model="reportForm.files_worked_on" 
                               type="text" 
                               required
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                      </div>

                      <!-- File Names or IDs -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File Names or IDs</label>
                        <input v-model="reportForm.file_names_or_ids" 
                               type="text"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                      </div>

                      <!-- Goals Met -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                          Goals Met <span class="text-red-500">*</span>
                        </label>
                        <select v-model="reportForm.goals_met" 
                                required
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                          <option value="">-- Select --</option>
                          <option value="Completed">Completed</option>
                          <option value="Partial">Partial</option>
                          <option value="Not Completed">Not Completed</option>
                        </select>
                      </div>

                     

                      <!-- Collaborators -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Collaborators</label>
                        <input v-model="reportForm.collaborators" 
                               type="text"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                      </div>
                    </div>

                    <!-- Issues Encountered -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-3">Issues Encountered</label>
                      <div class="flex flex-wrap gap-4">
                        <label v-for="issue in issueOptions" :key="issue" class="flex items-center">
                          <input v-model="reportForm.issues_encountered" 
                                 :value="issue"
                                 type="checkbox"
                                 class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                          <span class="ml-2 text-sm text-gray-700">{{ issue }}</span>
                        </label>
                      </div>
                    </div>

                    
                    <!-- Unfinished Task -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Unfinished Task</label>
                      <textarea v-model="reportForm.unfinished_task"
                                rows="2"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

        

                    <!-- File Upload -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Attach Report File (optional)</label>
                      <input ref="fileInput"
                             type="file"
                             @change="handleFileUpload"
                             class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                      <button type="button" 
                              @click="cancelTaskCompletion"
                              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                      </button>
                      <button type="submit" 
                              :disabled="submitting"
                              :class="submitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                              class="px-6 py-2 border border-transparent rounded-md text-sm font-medium text-white transition-colors">
                        <span v-if="submitting" class="flex items-center">
                          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          Submitting...
                        </span>
                        <span v-else>✅ Submit Report</span>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

// Configure axios to include CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
}
axios.defaults.withCredentials = true

const page = usePage()

// Reactive data
const loading = ref(true)
const submitting = ref(false)
const tasks = ref([])
const expandedTaskId = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

// Form data
const reportForm = reactive({
  files_worked_on: '',
  file_names_or_ids: '',
  goals_met: '',
  task_difficulty: '',
  collaborators: '',
  issues_encountered: [],
  mood_status: '',
  learning_summary: '',
  unfinished_task: '',
  tags: '',
  report_file: null
})

// Issue options
const issueOptions = ['DUMP FILES', 'TORN FILES', 'WRONG NAME', 'WRONG SORTING']

// Methods
const fetchTasks = async () => {
  try {
    loading.value = true
    console.log('Fetching employee tasks...')
    
    // CHANGED: Call the employee-specific endpoint instead of admin endpoint
    const response = await axios.get(route('employee.api.my-tasks'))
    
    console.log('API Response:', response.data) // Debug line
    tasks.value = response.data.tasks || []
    console.log('Tasks loaded:', tasks.value) // Debug line
    
    if (!response.data.success) {
      errorMessage.value = response.data.message || 'Failed to load tasks'
    }
    
  } catch (error) {
    console.error('Error fetching tasks:', error)
    console.log('Error response:', error.response?.data) // Debug line
    errorMessage.value = error.response?.data?.message || 'Failed to load tasks. Please try again.'
  } finally {
    loading.value = false
  }
}

const toggleTaskCompletion = (taskId) => {
  if (expandedTaskId.value === taskId) {
    expandedTaskId.value = null
    resetForm()
  } else {
    expandedTaskId.value = taskId
    resetForm()
  }
}

const cancelTaskCompletion = () => {
  expandedTaskId.value = null
  resetForm()
}

const resetForm = () => {
  Object.keys(reportForm).forEach(key => {
    if (key === 'issues_encountered') {
      reportForm[key] = []
    } else {
      reportForm[key] = key === 'report_file' ? null : ''
    }
  })
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  reportForm.report_file = file
}

const submitTaskReport = async (task) => {
  try {
    submitting.value = true
    errorMessage.value = ''
    
    const formData = new FormData()
    formData.append('id', task.id)
    Object.keys(reportForm).forEach(key => {
      if (key === 'issues_encountered') {
        formData.append(key, reportForm[key].join(','))
      } else if (key === 'report_file' && reportForm[key]) {
        formData.append(key, reportForm[key])
      } else if (reportForm[key] !== null && reportForm[key] !== '') {
        formData.append(key, reportForm[key])
      }
    })
    
    const response = await axios.post(route('employee.tasks.complete'), formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    if (response.data.success) {
      successMessage.value = '✅ Task report submitted successfully.'
      expandedTaskId.value = null
      resetForm()
      // Remove completed task from list
      tasks.value = tasks.value.filter(t => t.id !== task.id)

      // Refresh tasks from backend to ensure consistency
      setTimeout(() => {
        fetchTasks()
      }, 1000)
    } else {
      errorMessage.value = response.data.message || 'Failed to submit task report.'
    }
  } catch (error) {
    console.error('Error submitting task report:', error)
    errorMessage.value = 'Failed to submit task report. Please try again.'
  } finally {
    submitting.value = false
  }
}

const getPriorityClass = (priority) => {
  const classes = {
    'High': 'bg-red-100 text-red-800',
    'Medium': 'bg-yellow-100 text-yellow-800',
    'Low': 'bg-green-100 text-green-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Clear messages after 5 seconds
const clearMessages = () => {
  setTimeout(() => {
    successMessage.value = ''
    errorMessage.value = ''
  }, 5000)
}

// Watch for message changes to auto-clear
import { watch } from 'vue'
watch([successMessage, errorMessage], clearMessages)

onMounted(() => {
  fetchTasks()
})
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
  max-height: 1000px;
  opacity: 1;
}

.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}

.work-report-container {
  min-height: 400px;
}
</style>

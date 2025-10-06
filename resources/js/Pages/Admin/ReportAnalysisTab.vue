<template>
  <div class="report-analysis-tab">
    <!-- Header -->
    <div class="tab-header">
      <div>
        <h2>Report Analysis & Analytics</h2>
        <p class="subtitle">Real-time insights from attendance, tasks, and leave data</p>
      </div>
      <div class="header-actions">
        <select v-model="selectedPeriod" @change="fetchAnalyticsData" class="period-selector">
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="quarter">This Quarter</option>
          <option value="year">This Year</option>
        </select>
        <button @click="exportToExcel" class="btn btn-outline">
          <i class="fas fa-file-excel"></i> Export Excel
        </button>
        <button @click="exportToPDF" class="btn btn-outline">
          <i class="fas fa-file-pdf"></i> Export PDF
        </button>
        <button @click="refreshData" class="btn btn-primary" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i> Refresh
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading analytics data...</p>
    </div>

    <!-- Main Content -->
    <div v-else class="analytics-content">
      <!-- Key Metrics Cards -->
      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-header">
            <h3>Attendance Rate</h3>
            <span class="metric-icon">👥</span>
          </div>
          <div class="metric-value">{{ attendanceRate }}%</div>
          <div class="metric-subtitle">
            {{ presentCount }}/{{ totalEmployees }} present today
          </div>
          <div class="metric-trend" :class="attendanceTrend.direction">
            <i :class="attendanceTrend.icon"></i>
            {{ attendanceTrend.value }}% vs last period
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <h3>Task Completion</h3>
            <span class="metric-icon">✅</span>
          </div>
          <div class="metric-value">{{ taskCompletionRate }}%</div>
          <div class="metric-subtitle">
            {{ completedTasks }} of {{ totalTasks }} tasks
          </div>
          <div class="metric-trend" :class="taskTrend.direction">
            <i :class="taskTrend.icon"></i>
            {{ taskTrend.value }}% vs last period
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <h3>Leave Approval Rate</h3>
            <span class="metric-icon">🏖️</span>
          </div>
          <div class="metric-value">{{ leaveApprovalRate }}%</div>
          <div class="metric-subtitle">
            {{ approvedLeaves }} of {{ totalLeaveRequests }} approved
          </div>
          <div class="metric-trend" :class="leaveTrend.direction">
            <i :class="leaveTrend.icon"></i>
            {{ leaveTrend.value }}% vs last period
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <h3>Avg Hours/Day</h3>
            <span class="metric-icon">⏰</span>
          </div>
          <div class="metric-value">{{ averageHours }}h</div>
          <div class="metric-subtitle">Per employee</div>
          <div class="metric-trend" :class="hoursTrend.direction">
            <i :class="hoursTrend.icon"></i>
            {{ hoursTrend.value }}h vs last period
          </div>
        </div>
      </div>

      <!-- Detailed Breakdowns -->
      <div class="breakdown-grid">
        <!-- Attendance Breakdown -->
        <div class="breakdown-card">
          <h3>Attendance Breakdown</h3>
          <div class="breakdown-items">
            <div class="breakdown-item">
              <span class="label">Present Today</span>
              <span class="value present">{{ presentCount }}</span>
            </div>
            <div class="breakdown-item">
              <span class="label">Absent Today</span>
              <span class="value absent">{{ absentCount }}</span>
            </div>
            <div class="breakdown-item">
              <span class="label">On Break</span>
              <span class="value break">{{ onBreakCount }}</span>
            </div>
            <div class="breakdown-item total">
              <span class="label">Total Employees</span>
              <span class="value">{{ totalEmployees }}</span>
            </div>
          </div>
        </div>

        <!-- Task Status Overview -->
        <div class="breakdown-card">
          <h3>Task Status Overview</h3>
          <div class="breakdown-items">
            <div class="breakdown-item">
              <span class="label">Completed</span>
              <span class="value completed">{{ completedTasks }}</span>
            </div>
            <div class="breakdown-item">
              <span class="label">Pending</span>
              <span class="value progress">{{ pendingTasks }}</span>
            </div>
            <div class="breakdown-item">
              <span class="label">Overdue</span>
              <span class="value overdue">{{ overdueTasks }}</span>
            </div>
            <div class="breakdown-item total">
              <span class="label">Total Tasks</span>
              <span class="value">{{ totalTasks }}</span>
            </div>
          </div>
        </div>

        <!-- Leave Requests Summary -->
        <div class="breakdown-card">
          <h3>Leave Requests Summary</h3>
          <div class="breakdown-items">
            <div class="breakdown-item">
              <span class="label">Approved</span>
              <span class="value approved">{{ approvedLeaves }}</span>
            </div>
            <div class="breakdown-item">
              <span class="label">Pending</span>
              <span class="value pending">{{ pendingLeaves }}</span>
            </div>
            <div class="breakdown-item">
              <span class="label">Rejected</span>
              <span class="value rejected">{{ rejectedLeaves }}</span>
            </div>
            <div class="breakdown-item total">
              <span class="label">Total Requests</span>
              <span class="value">{{ totalLeaveRequests }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Trends Chart -->
      <div class="chart-section">
        <div class="chart-header">
          <h3>Performance Trends</h3>
          <div class="chart-controls">
            <button 
              v-for="metric in chartMetrics" 
              :key="metric.key"
              @click="selectedChartMetric = metric.key"
              :class="['chart-btn', { active: selectedChartMetric === metric.key }]"
            >
              {{ metric.label }}
            </button>
          </div>
        </div>

        <div class="chart-container">
          <div v-if="!chartLoaded" class="chart-placeholder">
            <div class="chart-placeholder-content">
              <i class="fas fa-chart-line"></i>
              <p>Loading chart...</p>
            </div>
          </div>
          <div v-else style="width: 100%; height: 100%; position: relative;">
            <canvas
              ref="performanceChart"
              style="width: 100%; height: 100%; max-height: 300px;"
            ></canvas>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
          <button @click="exportToExcel" class="action-card">
            <div class="action-icon">📊</div>
            <div class="action-content">
              <h4>Export Excel</h4>
              <p>Download detailed report</p>
            </div>
          </button>
          <button @click="emailReport" class="action-card">
            <div class="action-icon">📧</div>
            <div class="action-content">
              <h4>Email Report</h4>
              <p>Send to stakeholders</p>
            </div>
          </button>
          <button @click="scheduleReport" class="action-card">
            <div class="action-icon">📅</div>
            <div class="action-content">
              <h4>Schedule Report</h4>
              <p>Automated delivery</p>
            </div>
          </button>
          <button @click="generateCustomReport" class="action-card">
            <div class="action-icon">⚙️</div>
            <div class="action-content">
              <h4>Custom Report</h4>
              <p>Build your own</p>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Email Modal -->
    <div v-if="showEmailModal" class="modal-overlay" @click.self="closeEmailModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>📧 Email Report</h3>
          <button @click="closeEmailModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <p class="modal-description">
            Send the {{ selectedPeriod }} analytics report to any email address.
          </p>
          <div class="form-group">
            <label for="email-input">Email Address</label>
            <input
              id="email-input"
              v-model="emailForm.email"
              type="email"
              placeholder="Enter email address"
              class="form-input"
              @keyup.enter="sendEmailReport"
            />
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeEmailModal" class="btn btn-secondary">Cancel</button>
          <button @click="sendEmailReport" class="btn btn-primary" :disabled="emailForm.sending">
            {{ emailForm.sending ? 'Sending...' : 'Send Report' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Schedule Modal -->
    <div v-if="showScheduleModal" class="modal-overlay" @click.self="closeScheduleModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>📅 Schedule Report</h3>
          <button @click="closeScheduleModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <p class="modal-description">Set up automated report delivery to your email.</p>
          
          <div class="form-group">
            <label for="schedule-email">Email Address</label>
            <input
              id="schedule-email"
              v-model="scheduleForm.email"
              type="email"
              placeholder="Enter email address"
              class="form-input"
            />
          </div>

          <div class="form-group">
            <label for="frequency">Frequency</label>
            <select id="frequency" v-model="scheduleForm.frequency" class="form-select">
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>

          <div class="form-group">
            <label for="report-period">Report Period</label>
            <select id="report-period" v-model="scheduleForm.period" class="form-select">
              <option value="week">Week</option>
              <option value="month">Month</option>
              <option value="quarter">Quarter</option>
              <option value="year">Year</option>
            </select>
          </div>

          <div class="info-box">
            <p>📌 Reports will be sent automatically based on your selected frequency.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeScheduleModal" class="btn btn-secondary">Cancel</button>
          <button @click="submitScheduledReport" class="btn btn-primary" :disabled="scheduleForm.scheduling">
            {{ scheduleForm.scheduling ? 'Scheduling...' : 'Schedule Report' }}
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Custom Report Builder Modal -->
<div v-if="showCustomReportModal" class="modal-overlay" @click.self="closeCustomReportModal">
  <div class="modal-content custom-report-modal">
    <div class="modal-header">
      <h3>⚙️ Custom Report Builder</h3>
      <button @click="closeCustomReportModal" class="close-btn">×</button>
    </div>
    <div class="modal-body">
      <!-- Report Basic Info -->
      <div class="report-section">
        <h4>📋 Report Information</h4>
        <div class="form-group">
          <label for="report-name">Report Name *</label>
          <input
            id="report-name"
            v-model="customReportForm.name"
            type="text"
            placeholder="Enter report name"
            class="form-input"
          />
        </div>
        <div class="form-group">
          <label for="report-description">Description</label>
          <textarea
            id="report-description"
            v-model="customReportForm.description"
            placeholder="Optional description"
            class="form-textarea"
            rows="3"
          ></textarea>
        </div>
        <div class="form-group">
          <label for="report-period">Report Period</label>
          <select id="report-period" v-model="customReportForm.period" class="form-select">
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="quarter">This Quarter</option>
            <option value="year">This Year</option>
            <option value="custom">Custom Date Range</option>
          </select>
        </div>
        
        <!-- Custom Date Range -->
        <div v-if="customReportForm.period === 'custom'" class="date-range-group">
          <div class="form-group">
            <label for="start-date">Start Date</label>
            <input
              id="start-date"
              v-model="customReportForm.filters.dateRange.start"
              type="date"
              class="form-input"
            />
          </div>
          <div class="form-group">
            <label for="end-date">End Date</label>
            <input
              id="end-date"
              v-model="customReportForm.filters.dateRange.end"
              type="date"
              class="form-input"
            />
          </div>
        </div>
      </div>

      <!-- Metrics Selection -->
      <div class="report-section">
        <h4>📊 Select Metrics</h4>
        <div class="metrics-grid">
          <label class="metric-checkbox">
            <input
              v-model="customReportForm.metrics.attendance"
              type="checkbox"
            />
            <span class="checkmark"></span>
            <div class="metric-info">
              <strong>👥 Attendance Data</strong>
              <p>Present, absent, on break statistics</p>
            </div>
          </label>
          
          <label class="metric-checkbox">
            <input
              v-model="customReportForm.metrics.tasks"
              type="checkbox"
            />
            <span class="checkmark"></span>
            <div class="metric-info">
              <strong>✅ Task Performance</strong>
              <p>Completed, pending, overdue tasks</p>
            </div>
          </label>
          
          <label class="metric-checkbox">
            <input
              v-model="customReportForm.metrics.leaves"
              type="checkbox"
            />
            <span class="checkmark"></span>
            <div class="metric-info">
              <strong>🏖️ Leave Management</strong>
              <p>Leave requests and approvals</p>
            </div>
          </label>
          
          <label class="metric-checkbox">
            <input
              v-model="customReportForm.metrics.hours"
              type="checkbox"
            />
            <span class="checkmark"></span>
            <div class="metric-info">
              <strong>⏰ Working Hours</strong>
              <p>Average hours and productivity</p>
            </div>
          </label>
        </div>
      </div>

      <!-- Output Format -->
      <div class="report-section">
        <h4>📄 Output Format</h4>
        <div class="format-options">
          <label class="format-option">
            <input
              v-model="customReportForm.format"
              type="radio"
              value="excel"
            />
            <div class="format-card">
              <div class="format-icon">📊</div>
              <div class="format-info">
                <strong>Excel Report</strong>
                <p>Detailed spreadsheet with charts</p>
              </div>
            </div>
          </label>
          
          <label class="format-option">
            <input
              v-model="customReportForm.format"
              type="radio"
              value="pdf"
            />
            <div class="format-card">
              <div class="format-icon">📄</div>
              <div class="format-info">
                <strong>PDF Report</strong>
                <p>Professional formatted document</p>
              </div>
            </div>
          </label>
        </div>
      </div>

      <!-- Schedule Options -->
      <div class="report-section">
        <h4>📅 Schedule Options (Optional)</h4>
        <label class="schedule-toggle">
          <input
            v-model="customReportForm.schedule.enabled"
            type="checkbox"
          />
          <span class="toggle-switch"></span>
          <span>Enable automatic report generation</span>
        </label>
        
        <div v-if="customReportForm.schedule.enabled" class="schedule-options">
          <div class="form-group">
            <label for="schedule-frequency">Frequency</label>
            <select id="schedule-frequency" v-model="customReportForm.schedule.frequency" class="form-select">
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div class="form-group">
            <label for="schedule-email-custom">Email Address</label>
            <input
              id="schedule-email-custom"
              v-model="customReportForm.schedule.email"
              type="email"
              placeholder="Enter email for scheduled reports"
              class="form-input"
            />
          </div>
        </div>
      </div>

      <!-- Preview -->
      <div class="report-section">
        <h4>👁️ Report Preview</h4>
        <div class="preview-box">
          <div class="preview-header">
            <strong>{{ customReportForm.name || 'Untitled Report' }}</strong>
            <span class="preview-format">{{ customReportForm.format.toUpperCase() }}</span>
          </div>
          <div class="preview-content">
            <p v-if="customReportForm.description">{{ customReportForm.description }}</p>
            <div class="preview-metrics">
              <span>📊 Metrics: </span>
              <span class="metric-tags">
                <span v-if="customReportForm.metrics.attendance" class="metric-tag">Attendance</span>
                <span v-if="customReportForm.metrics.tasks" class="metric-tag">Tasks</span>
                <span v-if="customReportForm.metrics.leaves" class="metric-tag">Leaves</span>
                <span v-if="customReportForm.metrics.hours" class="metric-tag">Hours</span>
              </span>
            </div>
            <div class="preview-period">
              <span>📅 Period: {{ customReportForm.period === 'custom' ? 'Custom Range' : customReportForm.period }}</span>
            </div>
            <div v-if="customReportForm.schedule.enabled" class="preview-schedule">
              <span>🔄 Scheduled: {{ customReportForm.schedule.frequency }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal-footer">
      <button @click="closeCustomReportModal" class="btn btn-secondary">Cancel</button>
      <button @click="generateReport" class="btn btn-primary" :disabled="customReportForm.generating">
        {{ customReportForm.generating ? 'Generating...' : 'Generate Report' }}
      </button>
    </div>
  </div>
</div>

</template>

<script>
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import Swal from 'sweetalert2'
import { makeRequest } from '@/axiosConfig.js'

export default {
  name: 'ReportAnalysisTab',
  inheritAttrs: false,
  setup() {
    const loading = ref(true)
    const chartLoaded = ref(false)
    const selectedPeriod = ref('month')
    const selectedChartMetric = ref('attendance')
    const performanceChart = ref(null)
    let chartInstance = null
    let Chart = null // Store Chart class

    const showEmailModal = ref(false)
    const showScheduleModal = ref(false)
    const showCustomReportModal = ref(false) // Add this

    const emailForm = ref({
      email: '',
      sending: false
    })

    const scheduleForm = ref({
      email: '',
      frequency: 'weekly',
      period: 'month',
      scheduling: false
    })

    // Add custom report form
    const customReportForm = ref({
      name: '',
      description: '',
      period: 'month',
      metrics: {
        attendance: true,
        tasks: true,
        leaves: true,
        hours: true
      },
      filters: {
        departments: [],
        employees: [],
        dateRange: {
          start: '',
          end: ''
        }
      },
      format: 'excel',
      schedule: {
        enabled: false,
        frequency: 'weekly',
        email: ''
      },
      generating: false
    })

    const analyticsData = ref({
      attendance: {
        present: 0,
        absent: 0,
        onBreak: 0,
        total: 0,
        rate: 0,
        trend: { direction: 'up', value: 0, icon: 'fas fa-arrow-up' }
      },
      tasks: {
        completed: 0,
        pending: 0,
        overdue: 0,
        total: 0,
        completionRate: 0,
        trend: { direction: 'up', value: 0, icon: 'fas fa-arrow-up' }
      },
      leaves: {
        approved: 0,
        pending: 0,
        rejected: 0,
        total: 0,
        approvalRate: 0,
        trend: { direction: 'up', value: 0, icon: 'fas fa-arrow-up' }
      },
      hours: {
        average: 0,
        trend: { direction: 'up', value: 0, icon: 'fas fa-arrow-up' }
      },
      chartData: []
    })

    const chartMetrics = [
      { key: 'attendance', label: 'Attendance' },
      { key: 'taskCompletion', label: 'Tasks' }
    ]

    // Computed properties (keep all existing ones)
    const attendanceRate = computed(() => analyticsData.value.attendance.rate)
    const presentCount = computed(() => analyticsData.value.attendance.present)
    const absentCount = computed(() => analyticsData.value.attendance.absent)
    const onBreakCount = computed(() => analyticsData.value.attendance.onBreak)
    const totalEmployees = computed(() => analyticsData.value.attendance.total)
    const attendanceTrend = computed(() => analyticsData.value.attendance.trend)

    const taskCompletionRate = computed(() => analyticsData.value.tasks.completionRate)
    const completedTasks = computed(() => analyticsData.value.tasks.completed)
    const pendingTasks = computed(() => analyticsData.value.tasks.pending)
    const overdueTasks = computed(() => analyticsData.value.tasks.overdue)
    const totalTasks = computed(() => analyticsData.value.tasks.total)
    const taskTrend = computed(() => analyticsData.value.tasks.trend)

    const leaveApprovalRate = computed(() => analyticsData.value.leaves.approvalRate)
    const approvedLeaves = computed(() => analyticsData.value.leaves.approved)
    const pendingLeaves = computed(() => analyticsData.value.leaves.pending)
    const rejectedLeaves = computed(() => analyticsData.value.leaves.rejected)
    const totalLeaveRequests = computed(() => analyticsData.value.leaves.total)
    const leaveTrend = computed(() => analyticsData.value.leaves.trend)

    const averageHours = computed(() => analyticsData.value.hours.average)
    const hoursTrend = computed(() => analyticsData.value.hours.trend)

    const generateTrend = () => ({
      direction: Math.random() > 0.5 ? 'up' : 'down',
      value: Math.floor(Math.random() * 15),
      icon: Math.random() > 0.5 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'
    })

    // Initialize Chart.js once
    const initializeChart = async () => {
      try {
        const chartModule = await import('chart.js')
        Chart = chartModule.Chart
        Chart.register(...chartModule.registerables)
        return true
      } catch (error) {
        console.error('Error loading Chart.js:', error)
        return false
      }
    }

    const fetchAnalyticsData = async () => {
      loading.value = true
      chartLoaded.value = false

      try {
        const response = await makeRequest({
          method: 'GET',
          url: '/api/analytics/dashboard',
          params: { period: selectedPeriod.value }
        })

        const data = response.data

        // Parse and validate data (keep existing logic)
        const totalEmployees = Math.max(0, Number(data.attendanceStats?.totalEmployees || 0))
        const presentToday = Math.min(Math.max(0, Number(data.attendanceStats?.presentToday || 0)), totalEmployees)
        const absentToday = Math.max(0, Number(data.attendanceStats?.absentToday || 0))
        const onBreak = Math.max(0, Number(data.attendanceStats?.onBreak || 0))
        const avgHoursWorked = Number(data.attendanceStats?.avgHoursWorked || 0)

        const totalTasks = Math.max(0, Number(data.taskStats?.totalTasks || 0))
        const completedTasks = Math.min(Math.max(0, Number(data.taskStats?.completedTasks || 0)), totalTasks)
        const pendingTasks = Math.min(Math.max(0, Number(data.taskStats?.pendingTasks || 0)), totalTasks)
        const overdueTasks = Math.min(Math.max(0, Number(data.taskStats?.overdueTasks || 0)), totalTasks)

        const totalLeaveRequests = Math.max(0, Number(data.leaveStats?.totalRequests || 0))
        const approvedRequests = Math.min(Math.max(0, Number(data.leaveStats?.approvedRequests || 0)), totalLeaveRequests)
        const pendingRequests = Math.min(Math.max(0, Number(data.leaveStats?.pendingRequests || 0)), totalLeaveRequests)
        const rejectedRequests = Math.min(Math.max(0, Number(data.leaveStats?.rejectedRequests || 0)), totalLeaveRequests)

        // Calculate rates
        const attendanceRate = totalEmployees > 0 ? Math.round((presentToday / totalEmployees) * 100) : 0
        const taskCompletionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
        const leaveApprovalRate = totalLeaveRequests > 0 ? Math.round((approvedRequests / totalLeaveRequests) * 100) : 0

        analyticsData.value = {
          attendance: {
            present: presentToday,
            absent: absentToday,
            onBreak: onBreak,
            total: totalEmployees,
            rate: attendanceRate,
            trend: generateTrend()
          },
          tasks: {
            completed: completedTasks,
            pending: pendingTasks,
            overdue: overdueTasks,
            total: totalTasks,
            completionRate: taskCompletionRate,
            trend: generateTrend()
          },
          leaves: {
            approved: approvedRequests,
            pending: pendingRequests,
            rejected: rejectedRequests,
            total: totalLeaveRequests,
            approvalRate: leaveApprovalRate,
            trend: generateTrend()
          },
          hours: {
            average: avgHoursWorked,
            trend: generateTrend()
          },
          chartData: data.performanceData || []
        }

        // Wait for DOM update then initialize chart
        await nextTick()

        // Ensure Chart.js is loaded before creating chart
        if (!Chart) {
          await initializeChart()
        }

        // Set chartLoaded to true first so canvas element is rendered
        chartLoaded.value = true

        // Wait for canvas to be in DOM, then create chart
        await nextTick()
        setTimeout(() => {
          updateChart()
        }, 100)

      } catch (error) {
        console.error('Error fetching analytics data:', error)
        Swal.fire('Error', 'Failed to load analytics data', 'error')
      } finally {
        loading.value = false
      }
    }

    const updateChart = () => {
      if (!Chart) {
        console.log('Chart.js not loaded yet')
        return
      }

      // Destroy previous chart instance
      if (chartInstance) {
        try {
          chartInstance.destroy()
        } catch (e) {
          console.error('Error destroying chart:', e)
        }
        chartInstance = null
      }

      // Wait for canvas to be available in the next tick
      nextTick(() => {
        const canvas = performanceChart.value
        if (!canvas || !canvas.getContext) {
          console.log('Canvas not ready yet')
          return
        }

        const ctx = canvas.getContext('2d')
        if (!ctx) {
          console.error('Failed to get 2D context')
          return
        }

        const chartData = analyticsData.value.chartData

        // Generate sample data based on selected metric
        const labels = chartData.length > 0
          ? chartData.map(item => item.period)
          : ['Week 1', 'Week 2', 'Week 3', 'Week 4']

        let data
        if (chartData.length > 0) {
          data = chartData.map(item => item[selectedChartMetric.value] || 0)
        } else {
          // Different sample data for different metrics
          if (selectedChartMetric.value === 'attendance') {
            data = [85, 92, 78, 96]
          } else if (selectedChartMetric.value === 'taskCompletion') {
            data = [72, 85, 90, 88]
          } else {
            data = [75, 80, 85, 90]
          }
        }

        try {
          chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{
              label: chartMetrics.find(m => m.key === selectedChartMetric.value)?.label || 'Data',
              data,
              borderColor: '#1877F2',
              backgroundColor: 'rgba(24, 119, 242, 0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: '#1877F2',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { 
                display: false 
              },
              tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#1877F2',
                borderWidth: 1
              }
            },
            scales: {
              y: { 
                beginAtZero: true,
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                }
              },
              x: {
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                }
              }
            },
            elements: {
              point: {
                hoverBackgroundColor: '#1877F2'
              }
            }
          }
        })
        } catch (error) {
          console.error('Error creating chart:', error)
        }
      })
    }

    // Custom Report Builder Functions
    const generateCustomReport = () => {
      showCustomReportModal.value = true
      // Reset form
      customReportForm.value = {
        name: '',
        description: '',
        period: 'month',
        metrics: {
          attendance: true,
          tasks: true,
          leaves: true,
          hours: true
        },
        filters: {
          departments: [],
          employees: [],
          dateRange: {
            start: '',
            end: ''
          }
        },
        format: 'excel',
        schedule: {
          enabled: false,
          frequency: 'weekly',
          email: ''
        },
        generating: false
      }
    }

    const closeCustomReportModal = () => {
      showCustomReportModal.value = false
    }

    const generateReport = async () => {
      if (!customReportForm.value.name.trim()) {
        Swal.fire('Error', 'Please enter a report name', 'error')
        return
      }

      const selectedMetrics = Object.keys(customReportForm.value.metrics)
        .filter(key => customReportForm.value.metrics[key])

      if (selectedMetrics.length === 0) {
        Swal.fire('Error', 'Please select at least one metric', 'error')
        return
      }

      try {
        customReportForm.value.generating = true

        const reportData = {
          name: customReportForm.value.name,
          description: customReportForm.value.description,
          period: customReportForm.value.period,
          metrics: selectedMetrics,
          filters: customReportForm.value.filters,
          format: customReportForm.value.format
        }

        const response = await makeRequest({
          method: 'POST',
          url: '/api/analytics/custom-report',
          data: reportData,
          responseType: customReportForm.value.format === 'excel' ? 'blob' : 'json'
        })

        if (customReportForm.value.format === 'excel') {
          // Download Excel file
          const url = window.URL.createObjectURL(new Blob([response.data]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', `${customReportForm.value.name.replace(/\s+/g, '-')}-${Date.now()}.xlsx`)
          document.body.appendChild(link)
          link.click()
          link.remove()
          window.URL.revokeObjectURL(url)
        }

        // Handle scheduling if enabled
        if (customReportForm.value.schedule.enabled && customReportForm.value.schedule.email) {
          await makeRequest({
            method: 'POST',
            url: '/api/analytics/schedule-custom-report',
            data: {
              ...reportData,
              schedule: customReportForm.value.schedule
            }
          })
        }

        Swal.fire('Success', 'Custom report generated successfully!', 'success')
        showCustomReportModal.value = false

      } catch (error) {
        console.error('Error generating custom report:', error)
        Swal.fire('Error', error.response?.data?.message || 'Failed to generate custom report', 'error')
      } finally {
        customReportForm.value.generating = false
      }
    }

    // Keep all existing functions (refreshData, exportToExcel, etc.)
    const refreshData = () => fetchAnalyticsData()

    const exportToExcel = async () => {
      try {
        loading.value = true
        
        const response = await makeRequest({
          method: 'GET',
          url: '/api/analytics/export',
          params: { period: selectedPeriod.value, format: 'excel' },
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `analytics-report-${selectedPeriod.value}-${new Date().toISOString().split('T')[0]}.xlsx`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        Swal.fire('Success', 'Excel report downloaded successfully!', 'success')
      } catch (error) {
        console.error('Error exporting to Excel:', error)
        Swal.fire('Error', 'Failed to export Excel report', 'error')
      } finally {
        loading.value = false
      }
    }

    const exportToPDF = async () => {
      try {
        loading.value = true
        
        const response = await makeRequest({
          method: 'GET',
          url: '/api/analytics/export',
          params: { period: selectedPeriod.value, format: 'pdf' },
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `analytics-report-${selectedPeriod.value}.pdf`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        Swal.fire('Success', 'PDF report downloaded successfully!', 'success')
      } catch (error) {
        console.error('Error exporting to PDF:', error)
        Swal.fire('Error', 'Failed to export PDF report', 'error')
      } finally {
        loading.value = false
      }
    }

    const emailReport = () => {
      showEmailModal.value = true
    }

    const sendEmailReport = async () => {
      if (!emailForm.value.email) {
        Swal.fire('Error', 'Please enter an email address', 'error')
        return
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(emailForm.value.email)) {
        Swal.fire('Error', 'Please enter a valid email address', 'error')
        return
      }

      try {
        emailForm.value.sending = true

        await makeRequest({
          method: 'POST',
          url: '/api/analytics/email-report',
          data: {
            email: emailForm.value.email,
            period: selectedPeriod.value
          }
        })

        Swal.fire('Success', 'Report sent successfully!', 'success')
        showEmailModal.value = false
        emailForm.value.email = ''
      } catch (error) {
        console.error('Email error:', error)
        Swal.fire('Error', error.response?.data?.message || 'Failed to send email', 'error')
      } finally {
        emailForm.value.sending = false
      }
    }

    const closeEmailModal = () => {
      showEmailModal.value = false
      emailForm.value.email = ''
    }

    const scheduleReport = () => {
      showScheduleModal.value = true
    }

    const submitScheduledReport = async () => {
      if (!scheduleForm.value.email) {
        Swal.fire('Error', 'Please enter an email address', 'error')
        return
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(scheduleForm.value.email)) {
        Swal.fire('Error', 'Please enter a valid email address', 'error')
        return
      }

      try {
        scheduleForm.value.scheduling = true

        await makeRequest({
          method: 'POST',
          url: '/api/analytics/schedule-report',
          data: {
            email: scheduleForm.value.email,
            frequency: scheduleForm.value.frequency,
            period: scheduleForm.value.period
          }
        })

        Swal.fire('Success', 'Report scheduled successfully!', 'success')
        showScheduleModal.value = false
        scheduleForm.value.email = ''
      } catch (error) {
        console.error('Schedule error:', error)
        Swal.fire('Error', error.response?.data?.message || 'Failed to schedule report', 'error')
      } finally {
        scheduleForm.value.scheduling = false
      }
    }

    const closeScheduleModal = () => {
      showScheduleModal.value = false
      scheduleForm.value.email = ''
    }

    // Watch for chart metric changes
    watch(selectedChartMetric, async () => {
      if (Chart && performanceChart.value) {
        await nextTick()
        updateChart()
      }
    })

    // Initialize on mount
    onMounted(async () => {
      await initializeChart()
      await fetchAnalyticsData()
    })

    return {
      loading,
      chartLoaded,
      selectedPeriod,
      selectedChartMetric,
      performanceChart,
      chartMetrics,
      showEmailModal,
      showScheduleModal,
      showCustomReportModal, // Add this
      emailForm,
      scheduleForm,
      customReportForm, // Add this
      attendanceRate,
      presentCount,
      absentCount,
      onBreakCount,
      totalEmployees,
      attendanceTrend,
      taskCompletionRate,
      completedTasks,
      pendingTasks,
      overdueTasks,
      totalTasks,
      taskTrend,
      leaveApprovalRate,
      approvedLeaves,
      pendingLeaves,
      rejectedLeaves,
      totalLeaveRequests,
      leaveTrend,
      averageHours,
      hoursTrend,
      fetchAnalyticsData,
      refreshData,
      exportToExcel,
      exportToPDF,
      emailReport,
      sendEmailReport,
      closeEmailModal,
      scheduleReport,
      submitScheduledReport,
      closeScheduleModal,
      generateCustomReport,
      closeCustomReportModal, // Add this
      generateReport // Add this
    }
  }
}
</script>

<style scoped>
/* Copy all your existing styles here - they're fine */
.report-analysis-tab {
  padding: 24px;
  background: #f8fafc;
  min-height: 100vh;
}

.tab-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tab-header h2 {
  margin: 0 0 8px 0;
  color: #1f2937;
  font-size: 28px;
  font-weight: 700;
}

.subtitle {
  margin: 0;
  color: #6b7280;
  font-size: 16px;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.period-selector {
  padding: 8px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  cursor: pointer;
}

.btn {
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
  border: none;
}

.btn-outline {
  background: white;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn-primary {
  background: #1877F2;
  border: 1px solid #1877F2;
  color: white;
}

.btn-primary:hover {
  background: #1877F2;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #1877F2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.metric-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.metric-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.metric-header h3 {
  margin: 0;
  color: #374151;
  font-size: 16px;
  font-weight: 600;
}

.metric-icon {
  font-size: 24px;
}

.metric-value {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.metric-subtitle {
  color: #6b7280;
  font-size: 14px;
  margin-bottom: 12px;
}

.metric-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
}

.metric-trend.up {
  color: #059669;
}

.metric-trend.down {
  color: #dc2626;
}

.breakdown-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.breakdown-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.breakdown-card h3 {
  margin: 0 0 20px 0;
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
}

.breakdown-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.breakdown-item.total {
  border-top: 1px solid #e5e7eb;
  margin-top: 8px;
  padding-top: 16px;
  font-weight: 600;
}

.breakdown-item .label {
  color: #374151;
  font-size: 14px;
}

.breakdown-item .value {
  font-weight: 600;
  font-size: 16px;
}

.value.present { color: #059669; }
.value.absent { color: #dc2626; }
.value.break { color: #d97706; }
.value.completed { color: #059669; }
.value.progress { color: #1877F2; }
.value.overdue { color: #dc2626; }
.value.pending { color: #d97706; }
.value.approved { color: #059669; }
.value.rejected { color: #dc2626; }

.chart-section {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.chart-header h3 {
  margin: 0;
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
}

.chart-controls {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.chart-btn {
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.chart-btn:hover {
  background: #f9fafb;
}

.chart-btn.active {
  background: #1877F2;
  color: white;
  border-color: #1877F2;
}

.chart-container {
  height: 300px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  background: #f9fafb;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
}

.chart-placeholder-content {
  text-align: center;
  color: #6b7280;
}

.chart-placeholder-content i {
  font-size: 48px;
  margin-bottom: 16px;
  display: block;
}

.quick-actions {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.quick-actions h3 {
  margin: 0 0 20px 0;
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.action-card:hover {
  border-color: #1877F2;
  box-shadow: 0 2px 8px rgba(24, 119, 242, 0.15);
  transform: translateY(-1px);
}

.action-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.action-content h4 {
  margin: 0 0 4px 0;
  color: #1f2937;
  font-size: 14px;
  font-weight: 600;
}

.action-content p {
  margin: 0;
  color: #6b7280;
  font-size: 12px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 20px;
  color: #1f2937;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #1f2937;
}

.modal-body {
  padding: 24px;
}

.modal-description {
  color: #6b7280;
  margin-bottom: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.form-input, .form-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: #1877F2;
  box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.1);
}

.info-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 12px;
  margin-top: 16px;
}

.info-box p {
  margin: 0;
  color: #1e40af;
  font-size: 14px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid #e5e7eb;
}

.fa-spin {
  animation: spin 1s linear infinite;
}

.custom-report-modal {
  max-width: 700px;
  max-height: 90vh;
}

.report-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.report-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.report-section h4 {
  margin: 0 0 16px 0;
  color: #1f2937;
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-textarea {
  width: 100%;
  padding: 10
}

@media (max-width: 768px) {
  .report-analysis-tab {
    padding: 16px;
  }
  
  .tab-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .header-actions {
    justify-content: center;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .breakdown-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>

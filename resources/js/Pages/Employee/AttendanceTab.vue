<template>
  <div class="p-4">
    <!-- Flash messages -->
    <div v-if="$page.props.flash?.success" class="mb-3 p-3 bg-green-100 text-green-800 rounded">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="mb-3 p-3 bg-red-100 text-red-800 rounded">
      {{ $page.props.flash.error }}
    </div>

    <!-- Status / Time -->
    <div class="flex items-center justify-between mb-4">
      <div>
        <div class="text-2xl font-bold">{{ formattedTime }}</div>
        <div class="text-sm text-gray-600">{{ todayReadable }}</div>
      </div>
      <div class="text-right">
        <div class="text-sm text-gray-500">Status</div>
        <div :class="statusClass" class="font-semibold">{{ statusText }}</div>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
      <button
        :disabled="!canSignIn || loading"
        @click.prevent="signIn"
        class="py-3 px-4 rounded text-white transition-colors"
        :class="canSignIn && !loading ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
      >
        <span v-if="loading && activeAction === 'signin'">Processing...</span>
        <span v-else>Sign In</span>
      </button>

      <button
        :disabled="!canStartBreak || loading"
        @click.prevent="startBreak"
        class="py-3 px-4 rounded text-white transition-colors"
        :class="canStartBreak && !loading ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-gray-300 cursor-not-allowed'"
      >
        <span v-if="loading && activeAction === 'startbreak'">Processing...</span>
        <span v-else>Start Break</span>
      </button>

      <button
        :disabled="!canEndBreak || loading"
        @click.prevent="endBreak"
        class="py-3 px-4 rounded text-white transition-colors"
        :class="canEndBreak && !loading ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
      >
        <span v-if="loading && activeAction === 'endbreak'">Processing...</span>
        <span v-else>End Break</span>
      </button>

      <button
        :disabled="!canSignOut || loading"
        @click.prevent="signOut"
        class="py-3 px-4 rounded text-white transition-colors"
        :class="canSignOut && !loading ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'"
      >
        <span v-if="loading && activeAction === 'signout'">Processing...</span>
        <span v-else>Sign Out</span>
      </button>
    </div>

    <!-- Today's summary -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
          <div class="text-sm text-gray-500">Check In</div>
          <div class="font-medium">{{ formatTime(attendanceLocal?.check_in_time) }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Check Out</div>
          <div class="font-medium">{{ formatTime(attendanceLocal?.check_out_time) }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Break Start</div>
          <div class="font-medium">{{ formatTime(attendanceLocal?.current_break_start) }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Break Status</div>
          <div class="font-medium">
            {{ attendanceLocal?.break_status === 'on_break' ? 'On Break' : 'Not on Break' }}
          </div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t flex justify-between">
        <div>
          <div class="text-sm text-gray-500">Total Break Time</div>
          <div class="font-medium text-orange-600">
            {{ attendanceLocal?.total_break_time ?? '0 minutes' }}
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Hours Worked</div>
          <div class="font-medium text-green-600">
            {{ attendanceLocal?.working_hours ?? 'Calculating...' }} hours
          </div>
        </div>
      </div>
    </div>

    <!-- History -->
    <div class="bg-white p-4 rounded shadow">
      <h3 class="text-lg font-semibold mb-3">Recent Attendance</h3>
      <div v-if="historyLocal.length === 0" class="text-gray-500 text-center py-4">
        No attendance records found
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left">
              <th class="px-3 py-2">Date</th>
              <th class="px-3 py-2">Check In</th>
              <th class="px-3 py-2">Check Out</th>
              <th class="px-3 py-2">Break Status</th>
              <th class="px-3 py-2">Hours</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rec in historyLocal" :key="rec.id" class="border-t">
              <td class="px-3 py-2">{{ formatDate(rec.date) }}</td>
              <td class="px-3 py-2">{{ formatTime(rec.check_in_time) }}</td>
              <td class="px-3 py-2">{{ formatTime(rec.check_out_time) }}</td>
              <td class="px-3 py-2">
                {{ rec.break_status === 'on_break' ? 'On Break' : 'Completed' }}
              </td>
              <td class="px-3 py-2">{{ rec.working_hours ?? 'N/A' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  user: { type: Object, default: null },
  attendance: { type: Object, default: null },
  history: { type: Array, default: () => [] }
})

const page = usePage()
const attendanceLocal = ref(null)
const historyLocal = ref([])
const loading = ref(false)
const activeAction = ref(null)

const currentTime = ref(new Date())
let timer = null

function initializeData() {
  attendanceLocal.value = props.attendance || page.props.attendance || null
  historyLocal.value = props.history || page.props.history || []
}

onMounted(() => {
  initializeData()
  timer = setInterval(() => (currentTime.value = new Date()), 1000)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})

const isOnBreak = computed(() =>
  attendanceLocal.value && attendanceLocal.value.break_status === 'on_break'
)

const canSignIn = computed(() =>
  !attendanceLocal.value || !attendanceLocal.value.check_in_time
)

const canStartBreak = computed(() =>
  attendanceLocal.value &&
  attendanceLocal.value.check_in_time &&
  attendanceLocal.value.break_status !== 'on_break' &&
  !attendanceLocal.value.check_out_time
)

const canEndBreak = computed(() =>
  attendanceLocal.value &&
  attendanceLocal.value.break_status === 'on_break' &&
  !attendanceLocal.value.check_out_time
)

const canSignOut = computed(() =>
  attendanceLocal.value &&
  attendanceLocal.value.check_in_time &&
  !attendanceLocal.value.check_out_time &&
  !isOnBreak.value
)

const statusText = computed(() => page.props.status ?? 'Unknown')
const statusClass = computed(() => {
  switch (statusText.value) {
    case 'Present': return 'text-green-600'
    case 'Late': return 'text-orange-600'
    case 'On Break': return 'text-yellow-600'
    case 'Signed Out': return 'text-red-600'
    default: return 'text-gray-600'
  }
})

function formatTime(dt) {
  if (!dt) return '-'
  try {
    const d = new Date(dt)
    if (isNaN(d.getTime())) return '-'
    return d.toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: '2-digit' })
  } catch { return '-' }
}

function formatDate(d) {
  if (!d) return '-'
  try {
    const dd = new Date(d)
    if (isNaN(dd.getTime())) return '-'
    return dd.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })
  } catch { return '-' }
}

async function postAction(url, actionName) {
  if (loading.value) return
  loading.value = true
  activeAction.value = actionName

  router.post(url, {}, {
    forceFormData: true, 
    onSuccess: (page) => {
      attendanceLocal.value = page.props.attendance || null
      historyLocal.value = page.props.history || []
    },
    onFinish: () => {
      loading.value = false
      activeAction.value = null
    },
    preserveScroll: true
  })
}

function signIn() { postAction(route('employee.attendance.signin'), 'signin') }
function startBreak() { postAction(route('employee.attendance.startbreak'), 'startbreak') }
function endBreak() { postAction(route('employee.attendance.endbreak'), 'endbreak') }
function signOut() { postAction(route('employee.attendance.signout'), 'signout') }
</script>


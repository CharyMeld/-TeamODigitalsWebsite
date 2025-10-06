<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { router, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

// --- Safe axios defaults (CSRF + credentials) ---
axios.defaults.withCredentials = true;
axios.defaults.headers.common['Accept'] = 'application/json';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// Reactive state
const stats = ref({});
const recentUsers = ref([]);
const logs = ref([]);
const systemInfo = ref({});
const loading = ref(false);

// Quick actions (Ziggy route() is executed at build/runtime)
// Quick actions
const quickActions = [
  {
    title: "Add New User",
    icon: "user-plus",
    routeName: "superadmin.users.create", // matches Route::get('/create')->name('create')
    tooltip: "Create a new user account",
  },
  {
    title: "Manage Users",
    icon: "users",
    routeName: "superadmin.users.index", // matches Route::get('/')->name('index')
    tooltip: "View and manage all users",
  },
  {
    title: "Add Menu Item",
    icon: "plus-circle",
    routeName: "superadmin.menu-items.create-data", // matches Route::get('/create-data')->name('create-data')
    tooltip: "Add a new menu item",
  },
  {
    title: "System Settings",
    icon: "settings",
    routeName: "superadmin.settings.index", // matches Route::get('/')->name('index') under settings
    tooltip: "Modify system settings",
  },
  {
    title: "Finance Reports",
    icon: "bar-chart",
    routeName: "superadmin.finance.reports", // matches Route::get('/reports')->name('reports')
    tooltip: "View financial reports",
  },
  {
    title: "Access Control",
    icon: "shield",
    routeName: "superadmin.access.index", // matches Route::get('/')->name('index') under access-control
    tooltip: "Manage roles and permissions",
  },
];



// Helper: safe GET request with timeout and JSON verification
async function safeGet(url, opts = {}) {
  const controller = new AbortController();
  const timeout = opts.timeout ?? 5000; // default 5s
  const timeoutId = setTimeout(() => controller.abort(), timeout);

  try {
    const response = await axios.get(url, {
      signal: controller.signal,
      headers: { Accept: 'application/json' },
    });

    // If server responded with HTML (redirect to login), content-type won't be json
    const contentType = (response.headers['content-type'] || '').toLowerCase();
    if (!contentType.includes('application/json')) {
      console.warn(`Non-JSON response from ${url} (content-type=${contentType})`);
      return null;
    }

    return response.data;
  } catch (err) {
    // Normalize errors
    if (err.name === 'AbortError') {
      console.warn(`Request to ${url} aborted (timeout)`);
    } else if (err.response) {
      console.warn(`Request to ${url} failed with status ${err.response.status}`);
    } else {
      console.warn(`Request to ${url} failed:`, err.message);
    }
    return null;
  } finally {
    clearTimeout(timeoutId);
  }
}

onMounted(async () => {
  loading.value = true;

  try {
    // Fetch developer stats safely (API must return JSON). If it returns null we skip using it.
    const data = await safeGet('/api/dashboard/stats/developer', { timeout: 7000 });
    if (data && typeof data === 'object') {
      // support two shapes: { stats: {...}, recentUsers: [...], system: {...} }
      stats.value = data.stats ?? data;
      recentUsers.value = data.recentUsers ?? [];
      systemInfo.value = data.system ?? {};
    } else {
      // fallback to empty safe defaults
      stats.value = {};
      recentUsers.value = [];
      systemInfo.value = {};
    }

    // logs endpoint
    const logsData = await safeGet('/api/dashboard/stats/developer/logs');
    logs.value = Array.isArray(logsData) ? logsData : (logsData?.data ?? []);
  } catch (err) {
    console.error('Unexpected error in developer dashboard onMounted:', err);
  } finally {
    loading.value = false;
  }

  if (window.feather) feather.replace();
});

// Navigation helper: avoid navigating directly to API endpoints or causing full reloads
const navigateTo = (target) => {
  if (!target) return;

  // If target looks like a full URL
  if (typeof target === 'string' && (target.startsWith('http://') || target.startsWith('https://'))) {
    window.location.href = target;
    return;
  }

  // If using Ziggy route helper, target will be an absolute path starting with '/'
  if (typeof target === 'string') {
    // Prevent accidental navigation to API endpoints
    if (target.startsWith('/api/')) {
      // maybe open in new tab or show message — here we avoid visiting
      console.warn('navigateTo: prevented navigating to API endpoint', target);
      return;
    }

    // Use Inertia router for SPA navigation
    try {
      router.visit(target);
    } catch (e) {
      // fallback to full navigation
      window.location.href = target;
    }
    return;
  }

  // If target is an object with href
  if (target && target.href) {
    navigateTo(target.href);
  }
};

const openDevelopmentConsole = () => {
  // leave as interactive dev helper
  alert('Opening Development Console...');
};

const editProfile = () => {
  alert('Opening Profile Editor...');
};
</script>

<template>
  <AppLayout title="Developer Dashboard">
    <div class="space-y-6">
      <!-- Profile Link Section -->
      <div class="flex justify-end mb-4">
        <Link :href="route('profile.show')" class="text-blue-600">View My Profile</Link>
      </div>

      <!-- Superadmin-like Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="(value, key) in stats"
          :key="key"
          class="bg-white overflow-hidden shadow rounded-lg p-5"
        >
          <div class="flex items-center">
            <i data-feather="activity" class="h-8 w-8 text-blue-500"></i>
            <div class="ml-5 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  {{ key }}
                </dt>
                <dd class="text-lg font-medium text-gray-900">{{ value }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="action in quickActions"
            :key="action.title"
            @click="() => navigateTo(action.route)"
            class="group relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-4 text-center hover:border-gray-400"
          >
            <i
              :data-feather="action.icon"
              class="mx-auto h-8 w-8 text-gray-400 group-hover:text-gray-600"
            ></i>
            <span class="mt-2 block text-sm font-medium text-gray-900">{{ action.title }}</span>
          </button>
        </div>
      </div>

      <!-- System Overview & Recent Users -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Overview -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">System Overview</h3>
          <ul class="text-sm text-gray-700 space-y-2">
            <li>PHP Version: {{ systemInfo.php_version }}</li>
            <li>Laravel Version: {{ systemInfo.laravel_version }}</li>
            <li>DB Connection: {{ systemInfo.db_connection }}</li>
            <li>Cache Driver: {{ systemInfo.cache_driver }}</li>
          </ul>
        </div>

        <!-- Recent Users -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Users</h3>
          <div v-if="recentUsers.length > 0" class="space-y-3">
            <div
              v-for="user in recentUsers.slice(0, 5)"
              :key="user.id"
              class="flex justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="text-sm font-medium">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
              <p class="text-xs text-gray-400">{{ user.created_at }}</p>
            </div>
          </div>
          <p v-else class="text-gray-500">No recent users</p>
        </div>
      </div>

      <!-- Developer Exclusive: Activity Logs -->
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Logs</h3>
        <ul class="space-y-2 text-sm text-gray-700">
          <li v-for="log in logs" :key="log.id">
            {{ log.time }} -
            <strong>{{ log.user }}</strong>: {{ log.description }}
          </li>
        </ul>
      </div>

      <!-- Developer Exclusive: Tools & Profile -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Development Tools -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900">Development Tools</h3>
          <button
            @click="openDevelopmentConsole"
            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            <i data-feather="code" class="inline mr-2 h-4 w-4"></i>
            Development Console
          </button>
        </div>

        <!-- Profile Management -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900">Profile Management</h3>
          <button
            @click="editProfile"
            class="mt-4 px-4 py-2 bg-gray-100 rounded-md hover:bg-gray-200"
          >
            <i data-feather="user" class="inline mr-2 h-4 w-4"></i>
            Edit Profile
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>


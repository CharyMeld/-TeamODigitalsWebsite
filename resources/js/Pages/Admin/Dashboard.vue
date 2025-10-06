<script setup>
import { ref, markRaw, onMounted } from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import DynamicSidebar from "@/Components/DynamicSidebar.vue";
import { apiClient } from "@/axiosConfig.js";

// Admin tabs (markRaw prevents reactive wrapping)
import ProfileTab from "@/Pages/Admin/ProfileTab.vue";
import AssignTaskTab from "@/Pages/Admin/AssignTaskTab.vue";
import AttendanceTab from "@/Pages/Admin/AttendanceTab.vue";
import WorkReportTab from "@/Pages/Admin/WorkReportTab.vue";
import LeaveTab from "@/Pages/Admin/LeaveTab.vue";
import ReportAnalysisTab from "@/Pages/Admin/ReportAnalysisTab.vue";
import ManageUsersTab from "@/Pages/Admin/ManageUsersTab.vue";

const page = usePage();
const props = defineProps({ title: String });

// Dashboard stats
const stats = ref({
  totalUsers: 0,
  financeReports: 0,
  systemSettings: true // default to active
});

// Tab definitions
const tabs = [
  { name: "Profile", component: markRaw(ProfileTab), info: "User profile details and account settings." },
  { name: "Assign Task", component: markRaw(AssignTaskTab), info: "Assign tasks to employees." },
  { name: "Attendance", component: markRaw(AttendanceTab), info: "Track employee attendance records." },
  { name: "Work Report", component: markRaw(WorkReportTab), info: "Review submitted work reports." },
  { name: "Leave", component: markRaw(LeaveTab), info: "Manage employee leave requests." },
  { name: "Report Analysis", component: markRaw(ReportAnalysisTab), info: "Analyze performance and reports." },
  { name: "Manage Users", component: markRaw(ManageUsersTab), info: "Add, edit, delete, and view user profiles." },
];

const activeTab = ref(tabs[0]);

const switchTab = (tab) => {
  activeTab.value = tab;
};

// Fetch dashboard stats
async function fetchDashboardStats() {
  try {
    console.log('🔄 Fetching dashboard stats...');

    // Try the new dedicated profile stats endpoint first
    try {
      const response = await apiClient.get('/dashboard/profile-stats');
      console.log('📦 Profile stats response:', response.data);

      if (response.data.success) {
        stats.value.totalUsers = response.data.totalUsers || 0;
        stats.value.financeReports = response.data.financeReports || 0;
        stats.value.systemSettings = response.data.systemSettings !== false;

        console.log('✅ Dashboard stats loaded from profile-stats:', stats.value);
        return; // Exit early on success
      }
    } catch (err) {
      console.warn('⚠️ Profile stats endpoint failed:', err.response?.data || err.message);
    }

    // Fallback 1: Try dashboard data endpoint
    try {
      const response = await apiClient.get('/dashboard/data');
      const data = response.data.data || response.data;

      if (data.stats) {
        stats.value.totalUsers = data.stats.users || 0;
        stats.value.financeReports = data.stats.orders || 0;
        stats.value.systemSettings = true;

        console.log('✅ Dashboard stats loaded from data endpoint:', stats.value);
        return;
      }
    } catch (err) {
      console.warn('⚠️ Dashboard data endpoint failed:', err.response?.data || err.message);
    }

    // Fallback 2: Try admin stats endpoint
    try {
      const adminStatsResponse = await apiClient.get('/dashboard/admin-stats');
      const adminData = adminStatsResponse.data;

      stats.value.totalUsers = adminData.total_employees || 0;
      stats.value.financeReports = 0;
      stats.value.systemSettings = true;

      console.log('✅ Dashboard stats loaded from admin-stats:', stats.value);
    } catch (err) {
      console.warn('⚠️ All stat endpoints failed. Using defaults.');
      stats.value.totalUsers = 0;
      stats.value.financeReports = 0;
      stats.value.systemSettings = true;
    }

  } catch (error) {
    console.error('❌ Fatal error fetching dashboard stats:', error);
    // Keep default values on error
    stats.value.totalUsers = 0;
    stats.value.financeReports = 0;
    stats.value.systemSettings = true;
  }
}

// ✅ Fixed logout
const logout = () => {
  router.post(route("logout")); // Inertia handles CSRF + redirect
};

onMounted(() => {
  console.log('📊 Admin Dashboard mounted');
  fetchDashboardStats();
});
</script>

<template>
  <div>
    <Head :title="title" />
    <Banner />

    <div class="min-h-screen bg-gray-100 flex">
      <!-- Dynamic Sidebar -->
      <DynamicSidebar />

      <!-- Main Content -->
      <div class="md:pl-64 flex flex-col flex-1">
        <!-- Top Bar -->
        <div
          class="fixed top-0 right-0 left-64 z-10 h-14 bg-gray-300 shadow flex items-center justify-between px-4"
        >
          <!-- Tabs -->
          <div class="flex space-x-2">
            <button
              v-for="tab in tabs"
              :key="tab.name"
              @click="switchTab(tab)"
              class="px-3 py-1 rounded-md text-sm font-medium transition"
              :class="[
                activeTab.name === tab.name
                  ? 'bg-blue-600 text-white shadow'
                  : 'bg-gray-400 text-white hover:bg-blue-500',
              ]"
            >
              {{ tab.name }}
            </button>
          </div>

          <!-- User dropdown -->
          <Dropdown align="right" width="48">
            <template #trigger>
              <button
                type="button"
                class="inline-flex items-center px-3 py-1 text-sm rounded-md bg-gray-400 text-white hover:bg-gray-500"
              >
                {{ page.props.auth.user?.name || "Guest" }}
                <svg
                  class="ml-2 -mr-0.5 h-4 w-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                  />
                </svg>
              </button>
            </template>

            <template #content>
              <div class="block px-4 py-2 text-xs text-gray-400">
                Manage Account
              </div>
              <div class="border-t border-gray-200" />

              <!-- ✅ No need for <form>, just call logout -->
              <DropdownLink as="button" @click="logout">
                Log Out
              </DropdownLink>
            </template>
          </Dropdown>
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6 mt-14">
          <!-- Render active tab with stats prop -->
          <component :is="activeTab.component" :stats="stats" />
        </main>
      </div>
    </div>
  </div>
</template>


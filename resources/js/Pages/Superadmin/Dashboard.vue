<script setup>
import { ref, markRaw, onMounted } from "vue";
import { router, Link, Head, usePage } from "@inertiajs/vue3";
import DynamicSidebar from "@/Components/DynamicSidebar.vue";
import Banner from "@/Components/Banner.vue";
import { apiClient } from "@/axiosConfig.js"; // Use configured axios

// Import Admin tabs
import ProfileTab from "@/Pages/Superadmin/ProfileTab.vue"; // Use Superadmin ProfileTab with quick actions
import AssignTaskTab from "@/Pages/Admin/AssignTaskTab.vue";
import AttendanceTab from "@/Pages/Admin/AttendanceTab.vue";
import WorkReportTab from "@/Pages/Admin/WorkReportTab.vue";
import LeaveTab from "@/Pages/Admin/LeaveTab.vue";
import ReportAnalysisTab from "@/Pages/Admin/ReportAnalysisTab.vue";
import ManageUsersTab from "@/Pages/Admin/ManageUsersTab.vue";

// Import Superadmin-specific tabs
import BlogManagerTab from "@/Pages/Superadmin/BlogManagerTab.vue";
import JobVacanciesTab from "@/Pages/Superadmin/JobVacanciesTab.vue";

const page = usePage();
const props = defineProps({
  user: Object,
  roles: Array,
  title: String,
});

// Dashboard state
const stats = ref({
  totalUsers: 0,
  totalRoles: 0,
  totalMenuItems: 0,
  systemStatus: "Loading...",
  activeAdmins: 0,
  totalEmployees: 0,
  pendingApprovals: 0,
});
const recentUsers = ref([]);
const isLoading = ref(true);
const hasError = ref(false);

// Tab definitions with all admin tabs + superadmin tabs
const tabs = [
  { name: "Profile", component: markRaw(ProfileTab), info: "User profile details and account settings." },
  { name: "Assign Task", component: markRaw(AssignTaskTab), info: "Assign tasks to employees." },
  { name: "Attendance", component: markRaw(AttendanceTab), info: "Track employee attendance records." },
  { name: "Work Report", component: markRaw(WorkReportTab), info: "Review submitted work reports." },
  { name: "Leave", component: markRaw(LeaveTab), info: "Manage employee leave requests." },
  { name: "Report Analysis", component: markRaw(ReportAnalysisTab), info: "Analyze performance and reports." },
  { name: "Manage Users", component: markRaw(ManageUsersTab), info: "Add, edit, delete, and view user profiles." },
  { name: "Blog Manager", component: markRaw(BlogManagerTab), info: "Manage blog posts and content." },
  { name: "Job Vacancies", component: markRaw(JobVacanciesTab), info: "Post and manage job vacancies." },
];

const activeTab = ref(tabs[0]);

const switchTab = (tab) => {
  activeTab.value = tab;
};

// Quick actions with URLs
const quickActions = [
  {
    title: "Add New User",
    icon: "user-plus",
    url: "/superadmin/users/create",
    tooltip: "Create a new user account",
  },
  {
    title: "Manage Users",
    icon: "users",
    url: "/superadmin/users",
    tooltip: "View and manage all users",
  },
  {
    title: "Access Control",
    icon: "shield",
    url: "/superadmin/access-control",
    tooltip: "Manage roles and permissions",
  },
  {
    title: "System Settings",
    icon: "settings",
    url: "/superadmin/settings",
    tooltip: "Modify system settings",
  },
  {
    title: "Menu Management",
    icon: "menu",
    url: "/superadmin/menu-items",
    tooltip: "Manage navigation menus",
  },
  {
    title: "Finance Reports",
    icon: "bar-chart",
    url: "/superadmin/finance/reports",
    tooltip: "View financial reports",
  },
];


// Safe navigation using Ziggy or action
const navigateTo = (action) => {
  // If it's a function, execute it
  if (typeof action === 'function') {
    action();
    return;
  }

  // Otherwise treat as route name
  try {
    // Check if Ziggy is available
    if (typeof route !== "function") {
      console.error("Ziggy route() function not available");
      return;
    }

    // Check if route exists
    if (!window.Ziggy || !window.Ziggy.routes[action]) {
      console.error(`Route "${action}" not found in Ziggy routes`);
      console.log('Available routes:', Object.keys(window.Ziggy?.routes || {}));
      return;
    }

    const url = route(action);
    router.visit(url);
  } catch (error) {
    console.error('Navigation error:', error.message);
  }
};

// Logout function
const logout = () => {
  router.post(route("logout"));
};

// Fetch dashboard stats
const fetchStats = async () => {
  isLoading.value = true;
  hasError.value = false;

  try {
    const { data } = await apiClient.get("/superadmin/dashboard/stats");
    stats.value = {
      totalUsers: data.totalUsers || 0,
      totalRoles: data.totalRoles || 0,
      totalMenuItems: data.totalMenuItems || 0,
      systemStatus: data.systemStatus || "Operational",
      activeAdmins: data.activeAdmins || 0,
      totalEmployees: data.totalEmployees || 0,
      pendingApprovals: data.pendingApprovals || 0,
    };
    recentUsers.value = data.recentUsers || [];
  } catch (error) {
    console.error("Failed to fetch stats:", error);
    stats.value.systemStatus = "Error Loading";
    hasError.value = true;
  } finally {
    isLoading.value = false;
    if (window.feather?.replace) window.feather.replace();
  }
};

onMounted(() => {
  fetchStats();
});
</script>

<template>
  <div>
    <Head :title="title || 'Superadmin Dashboard'" />
    <Banner />

    <div class="min-h-screen bg-gray-100 flex">
      <!-- Dynamic Sidebar -->
      <DynamicSidebar />

      <!-- Main Content -->
      <div class="md:pl-64 flex flex-col flex-1">
        <!-- Top Bar with Tabs -->
        <div class="fixed top-0 right-0 left-64 z-10 h-14 bg-gray-300 shadow flex items-center justify-between px-4">
          <!-- Tabs -->
          <div class="flex space-x-2 overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.name"
              @click="switchTab(tab)"
              class="px-3 py-1 rounded-md text-sm font-medium transition whitespace-nowrap"
              :class="[
                activeTab.name === tab.name
                  ? 'bg-blue-600 text-white shadow'
                  : 'bg-gray-400 text-white hover:bg-blue-500',
              ]"
            >
              {{ tab.name }}
            </button>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">{{ page.props.auth?.user?.name || 'Superadmin' }}</span>
            <button
              @click="logout"
              class="px-3 py-1 bg-red-500 text-white text-sm rounded-md hover:bg-red-600 transition"
            >
              Logout
            </button>
          </div>
        </div>

        <!-- Tab Content Area -->
        <div class="pt-14 flex-1 overflow-y-auto">
          <!-- Tab Info Banner -->
          <div class="bg-blue-50 border-l-4 border-blue-400 p-4 m-4">
            <p class="text-sm text-blue-700">{{ activeTab.info }}</p>
          </div>

          <!-- Dynamic Tab Component -->
          <component
            :is="activeTab.component"
            :quickActions="activeTab.name === 'Profile' ? quickActions : undefined"
          />
        </div>
      </div>
    </div>
  </div>
</template>


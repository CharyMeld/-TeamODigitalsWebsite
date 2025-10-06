<script setup>
import { ref, computed } from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

// Employee tabs
import ProfileTab from "@/Pages/Employee/ProfileTab.vue";
import AttendanceTab from "@/Pages/Employee/AttendanceTab.vue";
import WorkReportTab from "@/Pages/Employee/WorkReportTab.vue";
import LeaveTab from "@/Pages/Employee/LeaveTab.vue";

// Props
const props = defineProps({
  title: { type: String, default: "Employee Dashboard" },
  user: { type: Object, required: true },
  attendance: { type: Object, default: null },
  history: { type: Array, default: () => [] },
  leaveRequests: { type: Array, default: () => [] },
  flash: { type: Object, default: () => ({}) }
});

const page = usePage();
const activeTab = ref("Profile");

// Tabs definitions
const tabs = [
  { name: "Profile", component: ProfileTab, info: "View your profile information." },
  { name: "Attendance", component: AttendanceTab, info: "Check your attendance records." },
  { name: "Work Report", component: WorkReportTab, info: "View your submitted work reports." },
  { name: "Leave", component: LeaveTab, info: "Manage your leave requests." },
];

// Map active tab to component
const activeTabComponent = computed(() => {
  const tab = tabs.find(t => t.name === activeTab.value);
  return tab ? tab.component : ProfileTab;
});

// Current tab info for sidebar
const currentTabInfo = computed(() => {
  const tab = tabs.find(t => t.name === activeTab.value);
  return tab ? tab.info : "";
});

// Switch tab
const switchTab = (tab) => {
  activeTab.value = tab.name;
};

// Flash messages
const flashSuccess = ref(props.flash.success || null);
const flashError = ref(props.flash.error || null);

// Logout
const logout = () => router.post(route("logout"));
</script>

<template>
  <div>
    <Head :title="props.title" />
    <Banner />

    <div class="min-h-screen bg-gray-100 flex">
      <!-- Sidebar -->
      <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-gray-200">
        <div class="flex flex-col items-center pt-6">
          <!-- Logo -->
          <Link :href="route('dashboard')">
            <ApplicationMark class="block h-28 w-auto" />
          </Link>

          <!-- Current tab info -->
          <div class="mt-6 p-4 text-center text-gray-700">
            {{ currentTabInfo }}
          </div>
        </div>
      </div>

      <!-- Main content -->
      <div class="md:pl-64 flex flex-col flex-1">
        <!-- Top bar -->
        <div class="fixed top-0 right-0 left-64 z-10 h-14 bg-gray-300 shadow flex items-center justify-between px-4">
          <!-- Tabs -->
          <div class="flex space-x-4">
            <button
              v-for="tab in tabs"
              :key="tab.name"
              @click="switchTab(tab)"
              class="px-3 py-1 rounded-md text-sm font-medium"
              :class="[
                activeTab === tab.name
                  ? 'bg-gray-500 text-white'
                  : 'bg-gray-400 text-white hover:bg-gray-500',
              ]"
            >
              {{ tab.name }}
            </button>
          </div>

          <!-- User dropdown -->
          <div class="flex items-center">
            <Dropdown align="right" width="48">
              <template #trigger>
                <span class="inline-flex rounded-md">
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1 text-sm rounded-md bg-gray-400 text-white hover:bg-gray-500"
                  >
                    {{ props.user?.name || "Guest" }}
                    <svg class="ml-2 -mr-0.5 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                  </button>
                </span>
              </template>
              <template #content>
                <div class="block px-8 py-5 text-xs text-gray-400">Manage Account</div>
                <div class="border-t border-gray-200" />
                <form @submit.prevent="logout">
                  <DropdownLink as="button">Log Out</DropdownLink>
                </form>
              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Flash messages -->
        <div class="mt-16 p-6 max-w-7xl mx-auto w-full">
          <div v-if="flashSuccess" class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ flashSuccess }}
          </div>
          <div v-if="flashError" class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ flashError }}
          </div>
        </div>

        <!-- Tab content -->
        <main class="flex-1 p-6 mt-6 max-w-7xl mx-auto w-full">
          <transition name="fade" mode="out-in">
            <component
              :is="activeTabComponent"
              :key="activeTab"
              :user="props.user"
              :attendance="props.attendance"
              :history="props.history"
              :leave-requests="props.leaveRequests"
            />
          </transition>
        </main>
      </div>
    </div>
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>


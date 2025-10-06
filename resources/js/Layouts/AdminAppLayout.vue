<script setup>
import { ref } from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { post } from "@/axiosConfig.js";
import { logout } from "@/utils/logout.js";

// Admin tabs
import ProfileTab from "@/Pages/Admin/ProfileTab.vue";
import AssignTaskTab from "@/Pages/Admin/AssignTaskTab.vue";
import AttendanceTab from "@/Pages/Admin/AttendanceTab.vue";
import WorkReportTab from "@/Pages/Admin/WorkReportTab.vue";
import LeaveTab from "@/Pages/Admin/LeaveTab.vue";
import ReportAnalysisTab from "@/Pages/Admin/ReportAnalysisTab.vue";

const page = usePage();
const props = defineProps({ title: String });

const currentTab = ref("Profile");

const tabs = {
  Profile: ProfileTab,
  "Assign Task": AssignTaskTab,
  Attendance: AttendanceTab,
  "Work Report": WorkReportTab,
  Leave: LeaveTab,
  "Report Analysis": ReportAnalysisTab,
};

const handleLogout = async () => {
  try {
    // Call Laravel's logout route
    await post("/logout");

    // Clear local storage/session storage (if you're storing tokens)
    localStorage.removeItem("authToken");

    // Redirect to login
    router.visit("/login");
  } catch (error) {
    console.error("Logout failed:", error);
  }
};

</script>

<template>
  <div>
    <Head :title="title" />
    <Banner />

    <div class="min-h-screen bg-gray-100 flex">
      <!-- Sidebar -->
      <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-gray-200">
        <div class="flex flex-col items-center pt-6">
          <Link :href="route('admin.dashboard')">
            <ApplicationMark class="block h-20 w-auto" />
          </Link>
          <div class="mt-6 p-4 text-center text-gray-700">
            {{ currentTab }}
          </div>
        </div>
      </div>

      <!-- Main -->
      <div class="md:pl-64 flex flex-col flex-1">
        <!-- Top Bar -->
        <div class="sticky top-0 z-10 h-14 bg-gray-300 shadow flex items-center justify-between px-4">
          <div class="flex space-x-4">
            <button
              v-for="(comp, name) in tabs"
              :key="name"
              @click="currentTab = name"
              class="px-3 py-1 rounded-md text-sm font-medium bg-gray-400 hover:bg-gray-500 text-white"
            >
              {{ name }}
            </button>
          </div>

        
          <!-- User Menu -->
        <Dropdown align="right" width="48">
          <template #trigger>
            <button type="button" class="px-3 py-1 text-sm rounded-md bg-gray-400 text-white">
              {{ page.props.auth.user?.name || "Admin" }}
            </button>
          </template>

          <template #content>
            <DropdownLink as="button" @click="handleLogout">
              Log Out
            </DropdownLink>
          </template>
        </Dropdown>
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6">
          <component :is="tabs[currentTab]" />
        </main>
      </div>
    </div>
  </div>
</template>


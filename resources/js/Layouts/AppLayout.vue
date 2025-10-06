<script setup>
import { ref, computed } from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const page = usePage();
const props = defineProps({
  title: String,
  tabs: {
    type: Array,
    default: () => []
  }
});

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);

// Current tab info (will switch when user clicks)
const currentTabInfo = ref("Welcome! Select a tab from the top bar.");



const switchTab = (tab) => {
  currentTabInfo.value = tab.content;
};

const logout = () => router.post(route("logout"));
</script>

<template>
  <div>
    <Head :title="title" />
    <Banner />

    <div class="min-h-screen bg-gray-100 flex">
      <!-- Sidebar (Logo + Info) -->
      <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-gray-200">
        <div class="flex flex-col items-center pt-6">
          <!-- Logo -->
          <Link :href="route('dashboard')">
            <ApplicationMark class="block h-30 w-auto" />
          </Link>

          <!-- Dynamic Info -->
          <div class="mt-6 p-4 text-center text-gray-700">
            {{ currentTabInfo }}
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="md:pl-64 flex flex-col flex-1">
        <!-- Top Bar -->
        <div class="sticky top-0 z-10 flex-shrink-0 h-14 bg-gray-300 shadow flex items-center justify-between px-4">
          <!-- Left: Tabs -->
          <div v-if="tabs && tabs.length > 0" class="flex space-x-4">
            <button
              v-for="tab in tabs"
              :key="tab.name"
              @click="switchTab(tab)"
              class="px-3 py-1 rounded-md text-sm font-medium bg-gray-400 hover:bg-gray-500 text-white"
            >
              {{ tab.name }}
            </button>
          </div>
          <div v-else></div>

          <!-- Right: User dropdown -->
          <div class="flex items-center">
            <Dropdown align="right" width="48">
              <template #trigger>
                <span class="inline-flex rounded-md">
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
                </span>
              </template>
              <template #content>
                <div class="block px-4 py-2 text-xs text-gray-400">
                  Manage Account
                </div>
                <div class="border-t border-gray-200" />
                <form @submit.prevent="logout">
                  <DropdownLink as="button">Log Out</DropdownLink>
                </form>
              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>


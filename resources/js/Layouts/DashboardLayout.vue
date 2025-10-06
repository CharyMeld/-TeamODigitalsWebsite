<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
    title: String,
});

const sidebarOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

// Computed properties for menu organization
const parentMenus = computed(() => {
    if (!$page.props.menus) return [];
    return $page.props.menus
        .filter(menu => menu.parent_id === null)
        .sort((a, b) => a.sort_order - b.sort_order);
});

const getChildMenus = (parentId) => {
    if (!$page.props.menus) return [];
    return $page.props.menus
        .filter(menu => menu.parent_id === parentId)
        .sort((a, b) => a.sort_order - b.sort_order);
};
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-gray-100 flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow-lg">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 border-b border-gray-200">
                    <Link :href="route('dashboard')">
                        <ApplicationMark class="block h-9 w-auto" />
                    </Link>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-8">
                    <div class="px-4">
                        <ul class="space-y-2">
                            <li v-for="menu in parentMenus" :key="menu.id">
                                <!-- Parent menu with route -->
                                <Link v-if="menu.route" 
                                      :href="menu.route" 
                                      class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200"
                                      :class="{ 'bg-blue-100 text-blue-900': route().current() === menu.route.replace('/', '') }">
                                    <i :class="'fas fa-' + menu.icon" class="w-5 h-5 mr-3"></i>
                                    {{ menu.name }}
                                </Link>
                                
                                <!-- Parent menu without route (dropdown) -->
                                <div v-else>
                                    <div class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200">
                                        <i :class="'fas fa-' + menu.icon" class="w-5 h-5 mr-3"></i>
                                        {{ menu.name }}
                                    </div>
                                    
                                    <!-- Child menus -->
                                    <ul v-if="getChildMenus(menu.id).length > 0" class="ml-6 mt-2 space-y-1">
                                        <li v-for="child in getChildMenus(menu.id)" :key="child.id">
                                            <Link :href="child.route" 
                                                  class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-800 transition-colors duration-200"
                                                  :class="{ 'bg-blue-50 text-blue-800': route().current() === child.route.replace('/', '') }">
                                                <i :class="'fas fa-' + child.icon" class="w-4 h-4 mr-3"></i>
                                                {{ child.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col">
                <!-- Top Navigation -->
                <nav class="bg-white border-b border-gray-200 h-16">
                    <div class="px-4 sm:px-6 lg:px-8 h-full">
                        <div class="flex justify-between items-center h-full">
                            <!-- Mobile menu button -->
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>

                            <!-- User Dropdown -->
                            <div class="relative" v-if="$page.props.auth.user">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                            {{ $page.props.auth.user?.name || 'Guest' }}
                                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">Manage Account</div>
                                        <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
                                        <div class="border-t border-gray-200" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Log Out</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

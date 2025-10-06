<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { route } from 'ziggy-js';

defineProps({
    title: String,
});

const sidebarCollapsed = ref(false);
const openMenu = ref(null); // track which parent is open

// Parent menus
const parentMenus = computed(() => {
    if (!$page.props.menus) return [];
    return $page.props.menus
        .filter(menu => menu.parent_id === null)
        .sort((a, b) => a.sort_order - b.sort_order);
});

// Child menus
const getChildMenus = (parentId) => {
    if (!$page.props.menus) return [];
    return $page.props.menus
        .filter(menu => menu.parent_id === parentId)
        .sort((a, b) => a.sort_order - b.sort_order);
};

// Sidebar toggle
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    if (sidebarCollapsed.value) {
        openMenu.value = null; // collapse everything
    }
};

// Expand/Collapse parent
const toggleMenu = (id) => {
    openMenu.value = openMenu.value === id ? null : id;
};
</script>

<template>
    <div>
        <Head :title="title" />

        <div class="min-h-screen bg-gray-100 flex">
            <!-- Sidebar -->
            <div :class="[
                'bg-white shadow-lg transition-all duration-300',
                sidebarCollapsed ? 'w-20' : 'w-64'
            ]">
                <!-- Logo & Toggle -->
                <div class="flex items-center justify-between h-16 border-b border-gray-200 px-4">
                    <Link :href="route('dashboard')" v-show="!sidebarCollapsed">
                        <ApplicationMark class="block h-9 w-auto" />
                    </Link>
                    <button @click="toggleSidebar" 
                            class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  :d="sidebarCollapsed ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7'"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-8">
                    <div class="px-4">
                        <ul class="space-y-2">
                            <li v-for="menu in parentMenus" :key="menu.id">
                                <!-- Parent with route -->
                                <Link v-if="menu.route" 
                                      :href="menu.route" 
                                      :class="[
                                        'flex items-center py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                                        sidebarCollapsed ? 'px-2 justify-center' : 'px-4',
                                        route().current() === menu.route.replace('/', '') 
                                            ? 'bg-blue-100 text-blue-900' 
                                            : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                                      ]"
                                      :title="sidebarCollapsed ? menu.name : ''">
                                    <i :class="['fas fa-' + menu.icon, sidebarCollapsed ? 'w-5 h-5' : 'w-5 h-5 mr-3']"></i>
                                    <span v-show="!sidebarCollapsed">{{ menu.name }}</span>
                                </Link>

                                <!-- Parent without route (collapsible) -->
                                <div v-else>
                                    <div @click="toggleMenu(menu.id)"
                                         :class="[
                                            'flex items-center justify-between py-2 text-sm font-medium cursor-pointer rounded-lg transition-colors duration-200',
                                            sidebarCollapsed ? 'px-2 justify-center' : 'px-4',
                                            openMenu === menu.id ? 'bg-gray-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                                         ]"
                                         :title="sidebarCollapsed ? menu.name : ''">
                                        <div class="flex items-center">
                                            <i :class="['fas fa-' + menu.icon, sidebarCollapsed ? 'w-5 h-5' : 'w-5 h-5 mr-3']"></i>
                                            <span v-show="!sidebarCollapsed">{{ menu.name }}</span>
                                        </div>
                                        <i v-show="!sidebarCollapsed" class="fas" 
                                           :class="openMenu === menu.id ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </div>

                                    <!-- Children -->
                                    <ul v-if="getChildMenus(menu.id).length > 0 && !sidebarCollapsed && openMenu === menu.id"
                                        class="ml-6 mt-2 space-y-1">
                                        <li v-for="child in getChildMenus(menu.id)" :key="child.id">
                                            <Link :href="child.route" 
                                                  class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                                                  :class="route().current() === child.route.replace('/', '') 
                                                            ? 'bg-blue-50 text-blue-800' 
                                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800'">
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

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <header v-if="$slots.header" class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-full mx-auto py-4 px-6">
                        <slot name="header" />
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>


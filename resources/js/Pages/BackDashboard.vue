<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Import role-specific dashboard components
import DeveloperDashboard from './Developer/Dashboard.vue';
import SuperadminDashboard from './Superadmin/Dashboard.vue';
import AdminDashboard from './Admin/Dashboard.vue';
import EmployeeDashboard from './Employee/Dashboard.vue';

// Props from Inertia
const props = defineProps({
    user: Object,
    roles: Array,
    menuItems: Array
});

// Reactive state
const user = ref(props.user);
const roles = ref(props.roles ?? []);
const menuItems = ref(props.menuItems ?? []);

// Stats state
const stats = ref(null);
const statsLoading = ref(false);
const statsError = ref(null);

// Decide which dashboard component to render
const currentDashboard = computed(() => {
    if (roles.value?.includes('developer')) return DeveloperDashboard;
    if (roles.value?.includes('superadmin')) return SuperadminDashboard;
    if (roles.value?.includes('admin')) return AdminDashboard;
    if (roles.value?.includes('employee')) return EmployeeDashboard;
    return EmployeeDashboard; // fallback
});

// Primary role for display purposes
const primaryRole = computed(() => {
    if (roles.value?.includes('developer')) return 'Developer';
    if (roles.value?.includes('superadmin')) return 'Super Admin';
    if (roles.value?.includes('admin')) return 'Admin';
    if (roles.value?.includes('employee')) return 'Employee';
    return 'User';
});

// Fetch stats only if role requires them
async function fetchStats() {
    const role = primaryRole.value.toLowerCase().replace(' ', '');
    if (role !== 'developer' && role !== 'superadmin') {
        stats.value = null; // no stats for admin/employee
        return;
    }

    statsLoading.value = true;
    statsError.value = null;

    try {
        const { data } = await axios.get(`/api/${role}/dashboard/stats`);
        stats.value = data;
    } catch (err) {
        console.error(`Failed to load ${role} stats:`, err);
        statsError.value = 'Failed to load stats';
    } finally {
        statsLoading.value = false;
    }
}

// Dropdown toggle
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const chevron = button.querySelector('[data-feather="chevron-down"]');
    if (!dropdown) return;

    const isHidden = !dropdown.style.display || dropdown.style.display === "none";
    dropdown.style.display = isHidden ? "block" : "none";
    if (chevron) chevron.style.transform = isHidden ? "rotate(180deg)" : "rotate(0deg)";
}

// Expand active menu
function expandActiveMenu() {
    const currentUrl = window.location.href;
    const menuLinks = document.querySelectorAll(".menu-item a[href]");
    
    menuLinks.forEach(link => {
        if (link.href === currentUrl) {
            link.classList.add("bg-blue-100", "text-blue-700", "border-r-2", "border-blue-500");
            let parent = link.closest(".dropdown-content");
            while (parent) {
                parent.style.display = "block";
                const button = parent.previousElementSibling;
                if (button) {
                    const chevron = button.querySelector('[data-feather="chevron-down"]');
                    if (chevron) chevron.style.transform = "rotate(180deg)";
                }
                parent = parent.parentElement.closest(".dropdown-content");
            }
        }
    });
}

// On mount
onMounted(() => {
    if (window.feather) feather.replace();
    expandActiveMenu();
    fetchStats(); // only runs if developer or superadmin
});
</script>

<template>
  <!-- Render the current role dashboard -->
  <component :is="currentDashboard" :stats="stats" />
</template>


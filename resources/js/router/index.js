import { createRouter, createWebHistory } from 'vue-router';

// Static base dashboard
import Dashboard from '@/Pages/Dashboard.vue';

// Lazy-loaded role-specific dashboards
const SuperAdminDashboard = () => import('@/Pages/Superadmin/Dashboard.vue');
const AdminDashboard = () => import('@/Pages/Admin/Dashboard.vue');
const DeveloperDashboard = () => import('@/Pages/Developer/Dashboard.vue');
const EmployeeDashboard = () => import('@/Pages/Employee/Dashboard.vue');

// Auth pages
const Login = () => import('@/Pages/Auth/Login.vue');

// Public routes
const publicRoutes = [
  { path: '/login', name: 'login', component: Login },
];

// Protected dashboard routes
const dashboardRoutes = [
  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/dashboard/superadmin', name: 'superadmin.dashboard', component: SuperAdminDashboard, meta: { requiresAuth: true } },
  { path: '/dashboard/admin', name: 'admin.dashboard', component: AdminDashboard, meta: { requiresAuth: true } },
  { path: '/dashboard/developer', name: 'developer.dashboard', component: DeveloperDashboard, meta: { requiresAuth: true } },
  { path: '/dashboard/employee', name: 'employee.dashboard', component: EmployeeDashboard, meta: { requiresAuth: true } },
];

// Merge all routes
const routes = [...publicRoutes, ...dashboardRoutes];

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Optional: Global auth guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token'); // or your auth logic
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'login' });
  }
  next();
});

export default router;


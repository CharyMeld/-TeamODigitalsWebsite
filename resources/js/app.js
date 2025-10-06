// app.js
import './bootstrap';
import './axios-setup';
import '../css/app.css';

 

import { createApp, h } from 'vue';
import { createInertiaApp, router as inertiaRouter } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import { apiClient } from "@/axiosConfig.js";

// =============================
// APP CONFIG
// =============================
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// =============================
// SAFE LOGIN REDIRECT
// =============================
const authToken = localStorage.getItem("auth_token") || localStorage.getItem("api_token");
const currentPath = window.location.pathname;

let redirected = false;

if (!redirected && authToken && currentPath === "/login") {
    redirected = true;
    console.log("✅ User already logged in, redirecting to dashboard...");
    inertiaRouter.visit("/dashboard", { replace: true });
}

// =============================
// AXIOS GLOBAL CONFIG
// =============================
apiClient.defaults.headers.common['Accept'] = 'application/json';
apiClient.defaults.withCredentials = true;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    apiClient.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

apiClient.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401 && window.location.pathname !== '/login') {
            console.warn('⚠️ Unauthorized, redirecting to login...');
            inertiaRouter.visit('/login', { replace: true });
        }
        return Promise.reject(error);
    }
);

// =============================
// INERTIA APP INIT
// =============================
createInertiaApp({
    title: title => `${title} - ${appName}`,
    resolve: name =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue', { eager: true })
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});


// resources/js/bootstrap.js
import axios from 'axios';
window.axios = axios;

// Basic axios defaults
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import and configure axios from our config file
//import { configureGlobalAxios } from './axiosConfig.js';
import { apiClient } from "@/axiosConfig.js";

// Configure global axios defaults
//configureGlobalAxios();

// Ensure CSRF token is available
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.warn(
        'CSRF token not found. Make sure <meta name="csrf-token" content="{{ csrf_token() }}"> is in your HTML head.'
    );
}

// Optional: Laravel Echo / Pusher setup
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher-channels.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });


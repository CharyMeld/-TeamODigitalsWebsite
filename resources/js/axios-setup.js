// resources/js/axios-setup.js or bootstrap.js
import axios from 'axios';

// Set default config
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

// Get Sanctum token from localStorage (if you're storing it)
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Response interceptor to handle 401 errors
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Clear all auth data
            localStorage.clear();
            sessionStorage.clear();
            delete axios.defaults.headers.common['Authorization'];

            // Redirect to login
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default axios;

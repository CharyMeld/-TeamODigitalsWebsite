// composables/useAuth.js or utils/auth.js
import axios from 'axios';
import { router } from '@inertiajs/vue3';

export const useAuth = () => {
    const logout = async () => {
        try {
            // Call the logout endpoint
            await axios.post('/logout', {}, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            // Clear all local storage
            localStorage.clear();
            sessionStorage.clear();

            // Clear axios default headers
            delete axios.defaults.headers.common['Authorization'];

            // Redirect using Inertia
            router.visit('/login', {
                method: 'get',
                replace: true,
                onSuccess: () => {
                    // Force reload to clear any cached state
                    window.location.href = '/login';
                }
            });
        } catch (error) {
            console.error('Logout error:', error);
            
            // Even if logout fails, clear local data and redirect
            localStorage.clear();
            sessionStorage.clear();
            delete axios.defaults.headers.common['Authorization'];
            window.location.href = '/login';
        }
    };

    return {
        logout
    };
};

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/public.css',
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                'resources/views/**',   // watch Blade views
                'resources/js/**',      // watch JS/Vue files
                'resources/css/**',     // watch CSS files
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },

    optimizeDeps: {
        include: ['vue-select'],
        exclude: ['vue-select/dist/vue-select.css'],
    },

    server: {
        host: '0.0.0.0',             // allow Docker container access
        port: 5176,                   // ✅ changed port to avoid conflicts
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
        watch: {
            usePolling: true,          // polling for Docker
            interval: 100,
            ignored: [
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/**',       // prevent reload loops
            ],
        },
    },
});

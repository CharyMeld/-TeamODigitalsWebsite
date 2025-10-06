import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                blue: {
                    50: '#E7F3FF',
                    100: '#D4E9FF',
                    200: '#A9D3FF',
                    300: '#7EBDFF',
                    400: '#53A7FF',
                    500: '#1877F2',
                    600: '#1877F2',
                    700: '#1565D8',
                    800: '#1153BE',
                    900: '#0D41A4',
                },
            },
        },
    },

    plugins: [forms, typography],
};

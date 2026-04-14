import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gold: {
                    50: '#fcf9ef',
                    100: '#f8f0d4',
                    200: '#eede9a',
                    300: '#e3c860',
                    400: '#d4af37',
                    500: '#c5a028',
                    600: '#a78620',
                    700: '#8a6d1a',
                    800: '#705616',
                    900: '#5c4715',
                },
                mansa: {
                    black: '#0a0a0a',
                    surface: '#141414',
                    card: '#fafafa',
                },
            },
        },
    },

    plugins: [forms],
};

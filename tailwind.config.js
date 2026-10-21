import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#F7F0FF',
                    100: '#EFE0FF',
                    200: '#DDBDFF',
                    300: '#C38AFF',
                    400: '#A44DFF',
                    500: '#8A1AFF',
                    600: '#6A00D9',
                    700: '#5C00BD',
                    800: '#4D009E',
                    900: '#410085',
                    950: '#280052',
                },
            },
        },
    },

    plugins: [forms],
};

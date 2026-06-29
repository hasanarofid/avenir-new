import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#EDF4E7',
                    100: '#DBEACE',
                    200: '#B7D49E',
                    300: '#94BF6D',
                    400: '#70A93D',
                    500: '#4C940C',
                    600: '#3D760A',
                    700: '#2E5907',
                    800: '#1E3B05',
                    900: '#0F1E02',
                    950: '#080F01',
                },
                emerald: {
                    50: '#EDF4E7',
                    100: '#DBEACE',
                    200: '#B7D49E',
                    300: '#94BF6D',
                    400: '#70A93D',
                    500: '#4C940C',
                    600: '#3D760A',
                    700: '#2E5907',
                    800: '#1E3B05',
                    900: '#0F1E02',
                    950: '#080F01',
                },
                green: {
                    50: '#EDF4E7',
                    100: '#DBEACE',
                    200: '#B7D49E',
                    300: '#94BF6D',
                    400: '#70A93D',
                    500: '#4C940C',
                    600: '#3D760A',
                    700: '#2E5907',
                    800: '#1E3B05',
                    900: '#0F1E02',
                    950: '#080F01',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                roboto: ['Roboto', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};

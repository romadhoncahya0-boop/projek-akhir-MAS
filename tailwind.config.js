import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', '"Segoe UI"', 'system-ui', '-apple-system', 'Roboto', ...defaultTheme.fontFamily.sans],
                heading: ['Inter', '"Segoe UI"', 'system-ui', '-apple-system', 'Roboto', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                DEFAULT: '10px',
                sm: '6px',
                md: '10px',
                lg: '16px',
                xl: '18px',
                '2xl': '22px',
            },
            boxShadow: {
                soft: 'none',
            },
            colors: {
                navy: '#5470E0',
                deep: '#4560CF',
                institute: '#6F88F2',
                brand: {
                    DEFAULT: '#5470E0',
                    dark: '#4560CF',
                },
                softblue: '#3F4A85',
                canvas: '#2F3659',
                ink: '#E6E9F7',
                muted: '#A4ACD1',
                line: '#474F82',
                success: '#2FA66A',
                warning: '#D9921E',
                danger: '#E5484D',
                info: '#6F88F2',
                sun: '#9DB0FF',
            },
        },
    },

    plugins: [forms],
};

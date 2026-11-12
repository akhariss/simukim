import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class', '[data-theme="dark"]'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Outfit', 'Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', "'Segoe UI'", 'Roboto', "'Helvetica Neue'", 'Arial', 'sans-serif'],
                wordmark: ['Plus Jakarta Sans', 'Sora', 'sans-serif'],
            },
            colors: {
                slate: {
                    50: '#f1f4f8',   // Soft canvas in light mode (eliminates harsh white glare)
                    100: '#e3e8f0',  // Soft input & button surface
                    200: '#d3dce8',  // Refined soft border
                    300: '#b8c5d6',
                    400: '#8c9eb5',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e2838',  // Matte dark border & dark hover
                    900: '#141c28',  // Matte dark card & sidebar surface (anti-judol)
                    950: '#0d121c',  // Deep matte canvas background in dark mode
                },
                mahad: {
                    primary: 'var(--primary)',
                    'primary-light': 'var(--primary-light)',
                    accent: 'var(--accent)',
                    base: 'var(--bg-base)',
                    surface: 'var(--bg-surface)',
                    border: 'var(--border-color)',
                    main: 'var(--text-main)',
                    muted: 'var(--text-muted)',
                }
            },
            boxShadow: {
                '2xs': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            }
        },
    },

    plugins: [forms],
};

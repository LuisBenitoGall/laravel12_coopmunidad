import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import plugin from 'tailwindcss/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                title: ['Inter', 'sans-serif'],
            },
            colors: {
                primary:    '#B28C63',       
                secondary:  '#EADBC8',   
                info:       '#58625C',      // Verde oscuro
                muted:      '#6b7280',      // Gray 500
                accent:     '#0ea5e9',      // Sky 500
                danger:     '#ef4444',      // Red 500
                success:    '#22c55e',      // Green 500
            },
            borderRadius: {
                xl: '1rem',
                '2xl': '1.5rem',
                custom: '0.7rem'
            },
            fontSize: {
                h1: ['2.25rem',     { lineHeight: '2.5rem' }],
                h2: ['1.875rem',    { lineHeight: '2.25rem' }],
                h3: ['1.5rem',      { lineHeight: '2rem' }],
                h4: ['1.25rem',     { lineHeight: '1.75rem' }],
            },
            spacing: {
                '72': '18rem',
                '84': '21rem',
                '96': '24rem',
            },
            boxShadow: {
                'card': '0 1px 3px rgba(0, 0, 0, 0.1)',
                'soft': '0 4px 6px rgba(0, 0, 0, 0.05)',
            },
        },
    },

    plugins: [
        forms,
        plugin(function ({ addBase, theme }) {
            addBase({
                ':root': {
                    '--color-primary': theme('colors.primary'),
                    '--color-secondary': theme('colors.secondary'),
                    '--color-muted': theme('colors.muted'),
                    '--color-accent': theme('colors.accent'),
                    '--color-danger': theme('colors.danger'),
                    '--color-success': theme('colors.success'),

                    '--radius-normal': '0.25rem',

                    '--transition-time': '0.4s',
                },
            });
        }),
    ],
};

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/design-system.css',
                'resources/css/themes/bootstrap-gradient-theme.css',
                'resources/css/themes/dark-mode-fixes.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/chart-setup.js',
                'resources/js/sweetalert-setup.js',
                'resources/js/components/employee-form-scripts.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        cors: true,
    },
});

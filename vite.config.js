import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
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
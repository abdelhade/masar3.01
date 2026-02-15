import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
<<<<<<< HEAD
                'resources/css/app.css',
=======
                'resources/css/design-system.css',
                'resources/css/themes/bootstrap-gradient-theme.css',
                'resources/css/app.css', 
>>>>>>> 91245a54c1948306a1bf54f903f9cec32a3a2f0e
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
import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/design-system.css',
                'resources/css/themes/bootstrap-gradient-theme.css',
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/components/employee-form-scripts.js'
                // ✅ Alpine components moved to inline Blade templates for proper timing
            ],
            refresh: true,
        }),
    ],
    server: {
        cors: true,
    },
});
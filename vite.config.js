import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/default.css',
                'resources/css/custom-style.css',
                'resources/css/custom-theme/theme-purple.css',
                
                'resources/js/app.js',
                'resources/js/common.js',
                'resources/js/datatable_service.js',
                'resources/js/project-script.js',
                'resources/js/post-script.js',
                'resources/js/category-script.js',
                'resources/js/validator-script.js'
            ],
            refresh: true,
        }),
    ],
});

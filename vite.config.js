import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
const fileInputs = [];

const themes = fs.readdirSync('resources/views', {withFileTypes: true})
    .filter(dirent => dirent.isDirectory() && dirent.name !== 'vendor')
    .map(dirent => dirent.name);

themes.forEach(theme => {
    const dashboardScssPath = `resources/views/${theme}/scss/dashboard.scss`;
    const frontendScssPath = `resources/views/${theme}/scss/frontend.scss`;

    fs.existsSync(dashboardScssPath) && fileInputs.push(dashboardScssPath);
    fs.existsSync(frontendScssPath) && fileInputs.push(frontendScssPath);
});

export default defineConfig({
    plugins: [
        laravel({
            input: fileInputs,
            refresh: ['app/**/*.php', 'resources/views/**/*.php'],
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                assetFileNames: `assets/[name]-[hash].[ext]`,
            }
        }
    },
});
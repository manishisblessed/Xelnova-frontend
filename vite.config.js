import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    server: {
        hmr: true,
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                "resources/js/app.js",           // Admin (Vue/Inertia)
                "resources/js/marketplace.js",   // Frontend (Alpine.js)
                "resources/css/admin.css",       // Admin styles
                "resources/css/frontend.css"     // Marketplace + Seller styles
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});

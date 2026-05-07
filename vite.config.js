import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            // input: "resources/js/app.js",
            input: ["resources/css/app.css", "resources/js/app.js"], // DOIT ÊTRE IDENTIQUE
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
    server: {
        host: "0.0.0.0",
        hmr: {
            host: "slain-malt-haste.ngrok-free.dev",
            protocol: "wss", // Crucial : utilise les WebSockets sécurisés
        },
        // Force l'utilisation du HTTPS pour les liens générés
        https: false, // On laisse false ici car ngrok gère le certificat
    },
});

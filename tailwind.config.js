import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/js/**/*.js",
    ],

    theme: {
        extend: {
            // Palette Ya Consulting — fidèle aux interfaces fournies
            colors: {
                primary: {
                    50: "#eff3ff",
                    100: "#dbe1ff",
                    200: "#c7d3ff",
                    300: "#a0b2fe",
                    400: "#7a8ffc",
                    500: "#536af7",
                    600: "#0053db", // ← couleur principale
                    700: "#0048c1",
                    800: "#003798",
                    900: "#002b7a",
                    DEFAULT: "#0053db",
                },
                surface: {
                    DEFAULT: "#f7f9fb",
                    low: "#f0f4f7",
                    container: "#e8eff3",
                    high: "#e1e9ee",
                    highest: "#d9e4ea",
                    lowest: "#ffffff",
                },
            },
            // Polices
            fontFamily: {
                headline: ["Syne", "sans-serif"],
                body: ["DM Sans", "sans-serif"],
                mono: ["JetBrains Mono", "monospace"],
            },
            // Animations personnalisées
            animation: {
                "fade-in": "fadeIn 0.3s ease-out",
                "slide-up": "slideUp 0.4s ease-out",
                "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { opacity: "0", transform: "translateY(12px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
            },
        },
    },

    fontFamily: {
        sans: ["Figtree", ...defaultTheme.fontFamily.sans],
    },

    // plugins: [forms, typography],
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};

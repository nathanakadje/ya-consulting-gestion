<template>
    <!-- resources/js/Components/NavItem.vue -->
    <Link
        :href="item.route"
        :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 group relative',
            isActive
                ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-semibold shadow-sm'
                : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800/60',
            collapsed ? 'justify-center' : '',
        ]"
    >
        <!-- Icône -->
        <span
            :class="[
                'material-symbols-outlined text-[22px] flex-shrink-0 transition-all',
                isActive
                    ? 'text-primary-600 dark:text-primary-400'
                    : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300',
            ]"
            :style="isActive ? 'font-variation-settings: \'FILL\' 1' : ''"
        >
            {{ item.icon }}
        </span>

        <!-- Label (caché si sidebar collapsed) -->
        <span
            v-show="!collapsed"
            class="text-sm whitespace-nowrap overflow-hidden transition-all duration-200"
        >
            {{ item.label }}
        </span>

        <!-- Indicateur actif (barre à gauche) -->
        <div
            v-if="isActive"
            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-600 rounded-r-full"
        ></div>

        <!-- Tooltip quand collapsed -->
        <div
            v-if="collapsed"
            class="absolute left-full ml-3 px-3 py-1.5 bg-gray-900 dark:bg-gray-700 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-lg z-50"
        >
            {{ item.label }}
            <!-- Flèche -->
            <div
                class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"
            ></div>
        </div>
    </Link>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    item: {
        type: Object,
        required: true,
        // { label, icon, route, exact? }
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

// Détermine si ce lien est actif selon l'URL courante
const isActive = computed(() => {
    const currentUrl = page.url;
    if (props.item.exact) {
        return currentUrl === props.item.route;
    }
    return currentUrl.startsWith(props.item.route);
});
</script>

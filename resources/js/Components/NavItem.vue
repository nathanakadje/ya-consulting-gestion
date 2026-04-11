<template>
    <!-- resources/js/Components/NavItem.vue -->
    <Link
        :href="item.route"
        :class="[
            'relative flex items-center rounded-xl transition-all duration-150 group overflow-hidden',
            collapsed
                ? 'justify-center px-0 py-[9px]'
                : 'gap-2.5 px-2.5 py-[8px]',
            isActive
                ? 'bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400'
                : 'text-gray-500 dark:text-gray-500 hover:text-gray-800 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04]',
        ]"
    >
        <!-- Barre active gauche -->
        <span
            v-if="isActive"
            class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-[18px] bg-primary-600 dark:bg-primary-400 rounded-r-full"
        ></span>

        <!-- Icône Material Symbols -->
        <span
            class="material-symbols-outlined flex-shrink-0 transition-all duration-150"
            :class="[
                collapsed ? 'text-[20px]' : 'text-[18px]',
                isActive
                    ? 'text-primary-600 dark:text-primary-400'
                    : 'text-gray-400 dark:text-gray-600 group-hover:text-gray-600 dark:group-hover:text-gray-300',
            ]"
            :style="isActive ? 'font-variation-settings:\'FILL\' 1' : ''"
            >{{ item.icon }}</span
        >

        <!-- Label (masqué si collapsed) -->
        <span
            v-show="!collapsed"
            class="text-[12.5px] font-semibold leading-none whitespace-nowrap"
            >{{ item.label }}</span
        >

        <!-- Tooltip (mode collapsed) -->
        <div
            v-if="collapsed"
            class="absolute left-full ml-3 px-2.5 py-1.5 bg-gray-900 dark:bg-gray-700 text-white text-[11px] font-semibold rounded-lg whitespace-nowrap shadow-lg pointer-events-none z-50 opacity-0 group-hover:opacity-100 -translate-x-1 group-hover:translate-x-0 transition-all duration-150"
        >
            {{ item.label }}
            <span
                class="absolute right-full top-1/2 -translate-y-1/2 border-[4px] border-transparent border-r-gray-900 dark:border-r-gray-700"
            ></span>
        </div>
    </Link>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    item: { type: Object, required: true },
    collapsed: { type: Boolean, default: false },
});

const page = usePage();

const isActive = computed(() => {
    const url = page.url;
    return props.item.exact
        ? url === props.item.route
        : url.startsWith(props.item.route);
});
</script>

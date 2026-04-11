<template>
    <!-- resources/js/Components/StatusBadge.vue -->
    <span
        :class="[
            'inline-flex items-center gap-[5px] px-2 py-[3px] rounded-full text-[10px] font-bold uppercase tracking-[0.08em]',
            badgeClass,
        ]"
    >
        <span
            v-if="status === 'en_cours'"
            class="w-[5px] h-[5px] rounded-full bg-current animate-pulse flex-shrink-0"
        ></span>
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    status: { type: String, required: true },
});

const map = {
    en_cours: {
        label: "En cours",
        class: "bg-primary-50 dark:bg-primary-600/10 text-primary-700 dark:text-primary-400",
    },
    termine: {
        label: "Terminé",
        class: "bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-400",
    },
    en_pause: {
        label: "En pause",
        class: "bg-amber-50 dark:bg-amber-600/10 text-amber-700 dark:text-amber-400",
    },
};

const label = computed(() => map[props.status]?.label ?? props.status);
const badgeClass = computed(
    () =>
        map[props.status]?.class ??
        "bg-gray-100 dark:bg-gray-800 text-gray-500",
);
</script>

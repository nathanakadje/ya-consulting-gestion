<template>
    <!-- resources/js/Components/StatusBadge.vue -->
    <span
        :class="[
            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide',
            badgeClass,
        ]"
    >
        <span
            v-if="status === 'en_cours'"
            class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"
        ></span>
        {{ statusLabel }}
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    status: { type: String, required: true },
    // 'en_cours' | 'termine' | 'en_pause'
});

const statusMap = {
    en_cours: {
        label: "En cours",
        class: "bg-primary-50 text-primary-700",
    },
    termine: {
        label: "Terminé",
        class: "bg-emerald-50 text-emerald-700",
    },
    en_pause: {
        label: "En pause",
        class: "bg-amber-50 text-amber-700",
    },
};

const statusLabel = computed(
    () => statusMap[props.status]?.label ?? props.status,
);
const badgeClass = computed(
    () => statusMap[props.status]?.class ?? "bg-gray-100 text-gray-600",
);
</script>

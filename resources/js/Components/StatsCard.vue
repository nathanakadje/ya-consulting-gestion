<template>
    <!-- resources/js/Components/StatCard.vue -->
    <div
        :class="[
            'bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm',
            'relative overflow-hidden group hover:shadow-md transition-shadow duration-200',
        ]"
    >
        <!-- Barre de couleur à gauche -->
        <div
            :class="[
                'absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl',
                barColor,
            ]"
        ></div>

        <div class="flex items-start justify-between pl-2">
            <div class="flex-1">
                <p
                    class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2"
                >
                    {{ label }}
                </p>
                <p
                    class="font-headline text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-none mb-2"
                >
                    {{ value }}
                </p>
                <div
                    v-if="trend"
                    class="flex items-center gap-1 text-xs font-medium"
                    :class="
                        trendUp
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-amber-500 dark:text-amber-400'
                    "
                >
                    <span class="material-symbols-outlined text-[14px]">
                        {{ trendUp ? "arrow_upward" : "arrow_downward" }}
                    </span>
                    {{ trend }}
                </div>
            </div>

            <!-- Icône -->
            <div
                :class="[
                    'w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0',
                    iconBg,
                ]"
            >
                <span
                    :class="[
                        'material-symbols-outlined text-[22px]',
                        iconColor,
                    ]"
                    >{{ icon }}</span
                >
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    icon: { type: String, required: true },
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    trend: { type: String, default: null },
    trendUp: { type: Boolean, default: true },
    color: { type: String, default: "primary" }, // primary | emerald | violet | amber | red
});

const colorMap = {
    primary: {
        bar: "bg-primary-600",
        iconBg: "bg-primary-50 dark:bg-primary-900/30",
        iconColor: "text-primary-600 dark:text-primary-400",
    },
    emerald: {
        bar: "bg-emerald-500",
        iconBg: "bg-emerald-50 dark:bg-emerald-900/30",
        iconColor: "text-emerald-600 dark:text-emerald-400",
    },
    violet: {
        bar: "bg-violet-500",
        iconBg: "bg-violet-50 dark:bg-violet-900/30",
        iconColor: "text-violet-600 dark:text-violet-400",
    },
    amber: {
        bar: "bg-amber-500",
        iconBg: "bg-amber-50 dark:bg-amber-900/30",
        iconColor: "text-amber-600 dark:text-amber-400",
    },
    red: {
        bar: "bg-red-500",
        iconBg: "bg-red-50 dark:bg-red-900/30",
        iconColor: "text-red-600 dark:text-red-400",
    },
};

const barColor = computed(
    () => colorMap[props.color]?.bar ?? colorMap.primary.bar,
);
const iconBg = computed(
    () => colorMap[props.color]?.iconBg ?? colorMap.primary.iconBg,
);
const iconColor = computed(
    () => colorMap[props.color]?.iconColor ?? colorMap.primary.iconColor,
);
</script>

<template>
    <!-- resources/js/Components/StatCard.vue -->
    <div
        class="bg-white dark:bg-[#111318] rounded-2xl p-5 border border-gray-100 dark:border-gray-800/70 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_rgba(0,0,0,0.03)] relative overflow-hidden hover:shadow-[0_2px_8px_rgba(0,0,0,0.06),0_8px_20px_rgba(0,0,0,0.04)] transition-shadow duration-200"
    >
        <!-- Barre de couleur gauche -->
        <div
            :class="[
                'absolute left-0 top-0 bottom-0 w-[3px] rounded-l-2xl',
                barColor,
            ]"
        ></div>

        <div class="flex items-start justify-between pl-2.5">
            <div class="flex-1 min-w-0">
                <p
                    class="text-[10.5px] font-bold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-600 mb-2.5"
                >
                    {{ label }}
                </p>
                <p
                    class="font-headline text-[22px] font-extrabold text-gray-900 dark:text-white tracking-tight leading-none mb-2"
                >
                    {{ value }}
                </p>
                <div
                    v-if="trend"
                    class="flex items-center gap-1 text-[11px] font-semibold"
                    :class="
                        trendUp
                            ? 'text-emerald-600 dark:text-emerald-500'
                            : 'text-amber-500 dark:text-amber-400'
                    "
                >
                    <span class="material-symbols-outlined text-[13px]">
                        {{ trendUp ? "arrow_upward" : "arrow_downward" }}
                    </span>
                    {{ trend }}
                </div>
            </div>

            <!-- Icône -->
            <div
                :class="[
                    'w-[38px] h-[38px] rounded-xl flex items-center justify-center flex-shrink-0',
                    iconBg,
                ]"
            >
                <span
                    :class="[
                        'material-symbols-outlined text-[19px]',
                        iconColor,
                    ]"
                    style="font-variation-settings: &quot;FILL&quot; 1"
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
    color: { type: String, default: "primary" },
});

const colorMap = {
    primary: {
        bar: "bg-primary-600",
        iconBg: "bg-primary-50 dark:bg-primary-600/10",
        iconColor: "text-primary-600 dark:text-primary-400",
    },
    emerald: {
        bar: "bg-emerald-500",
        iconBg: "bg-emerald-50 dark:bg-emerald-600/10",
        iconColor: "text-emerald-600 dark:text-emerald-400",
    },
    violet: {
        bar: "bg-violet-500",
        iconBg: "bg-violet-50 dark:bg-violet-600/10",
        iconColor: "text-violet-600 dark:text-violet-400",
    },
    amber: {
        bar: "bg-amber-500",
        iconBg: "bg-amber-50 dark:bg-amber-600/10",
        iconColor: "text-amber-600 dark:text-amber-400",
    },
    red: {
        bar: "bg-red-500",
        iconBg: "bg-red-50 dark:bg-red-600/10",
        iconColor: "text-red-600 dark:text-red-400",
    },
};

const c = computed(() => colorMap[props.color] ?? colorMap.primary);
const barColor = computed(() => c.value.bar);
const iconBg = computed(() => c.value.iconBg);
const iconColor = computed(() => c.value.iconColor);
</script>

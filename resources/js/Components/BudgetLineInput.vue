<template>
    <div>
        <div :class="['flex items-center gap-2 mb-1.5']">
            <span
                :class="['material-symbols-outlined text-[16px]', iconColor]"
                >{{ icon }}</span
            >
            <label
                class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >{{ label }}</label
            >
        </div>
        <div class="relative">
            <input
                type="number"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                min="0"
                step="1000"
                placeholder="0"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm pr-10 outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30 transition-all"
            />
            <span
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 font-bold"
                >XOF</span
            >
        </div>
    </div>
</template>
<script setup>
import { computed } from "vue";
const props = defineProps({
    modelValue: [String, Number],
    label: String,
    icon: { type: String, default: "category" },
    color: { type: String, default: "gray" },
});
defineEmits(["update:modelValue"]);
const colorMap = {
    blue: "text-blue-500",
    violet: "text-violet-500",
    amber: "text-amber-500",
    gray: "text-gray-400",
    emerald: "text-emerald-500",
};
const iconColor = computed(() => colorMap[props.color] ?? colorMap.gray);
</script>

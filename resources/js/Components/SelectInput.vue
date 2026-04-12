<template>
    <select
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        :class="[
            'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-all appearance-none cursor-pointer',
            'bg-white dark:bg-gray-800 text-gray-900 dark:text-white',
            error
                ? 'border-red-400 focus:ring-2 focus:ring-red-400/20'
                : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30',
        ]"
    >
        <slot />
    </select>
</template>
<script setup>
defineProps({ modelValue: [String, Number], error: String });
defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="flex items-center gap-1.5">
        <div :class="['w-2 h-2 rounded-full flex-shrink-0', color]"></div>
        <span class="text-[10px] text-gray-400 font-medium">{{ label }}</span>
    </div>
</template>
<script setup>
defineProps({ color: String, label: String });
</script>

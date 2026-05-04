<script setup lang="ts">
defineProps<{
    label: string
    value: string | number
    description?: string
    highlight?: boolean
}>()
</script>

<template>
    <div :class="['bg-white border rounded-2xl p-5', highlight ? 'border-amber-300 bg-amber-50' : 'border-gray-200']">
        <p class="text-sm text-gray-500 mb-1">{{ label }}</p>
        <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
        <p v-if="description" class="text-xs text-gray-400 mt-1">{{ description }}</p>
    </div>
</template>

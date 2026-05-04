<script setup lang="ts">
defineProps<{
    title: string
    subtitle?: string
}>()
</script>

<template>
    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.action">
            <slot name="action" />
        </div>
    </div>
</template>

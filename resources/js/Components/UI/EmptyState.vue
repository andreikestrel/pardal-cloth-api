<script setup lang="ts">
defineProps<{
    message: string
    linkLabel?: string
    linkHref?: string
}>()
</script>

<template>
    <div class="text-center py-16">
        <slot name="icon" />
        <p class="text-gray-500 text-sm mt-4">{{ message }}</p>
        <a v-if="linkHref && linkLabel" :href="linkHref"
            class="inline-block mt-4 text-sm font-medium text-gray-700 hover:underline">
            {{ linkLabel }} →
        </a>
    </div>
</template>

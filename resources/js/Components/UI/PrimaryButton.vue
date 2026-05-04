<script setup lang="ts">
defineProps<{
    type?: 'button' | 'submit'
    disabled?: boolean
    loading?: boolean
}>()
</script>

<template>
    <button
        :type="type ?? 'button'"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white rounded-xl disabled:opacity-50 transition-opacity hover:opacity-90"
        style="background-color: var(--color-primary)"
    >
        <span v-if="loading" class="mr-2 h-4 w-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
        <slot />
    </button>
</template>

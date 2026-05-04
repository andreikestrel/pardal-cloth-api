<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { PageProps } from '@/types'

const page = usePage<PageProps>()
const settings = computed(() => page.props.settings)

const themeVars = computed(() => ({
    '--color-primary':   settings.value.primary_color   || '#111827',
    '--color-secondary': settings.value.secondary_color || '#6b7280',
    '--color-accent':    settings.value.accent_color    || '#f59e0b',
}))
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4" :style="themeVars">
        <Link :href="route('home')" class="mb-8 flex flex-col items-center gap-2">
            <img v-if="settings.logo" :src="settings.logo" :alt="settings.company_name" class="h-10 w-auto" />
            <span v-else class="text-2xl font-semibold tracking-tight" :style="{ color: 'var(--color-primary)' }">
                {{ settings.company_name }}
            </span>
        </Link>

        <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <slot />
        </div>
    </div>
</template>

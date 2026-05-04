<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const page = usePage()

const tabs = [
    { label: 'Geral',      href: route('admin.settings.general') },
    { label: 'Pagamentos', href: route('admin.settings.payment') },
]
</script>

<template>
    <div class="flex gap-1 mb-6 border-b border-gray-200">
        <Link
            v-for="tab in tabs"
            :key="tab.href"
            :href="tab.href"
            :class="['px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors',
                page.url.startsWith(tab.href.replace(/^https?:\/\/[^/]+/, ''))
                    ? 'border-current text-gray-900'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']"
            :style="page.url.startsWith(tab.href.replace(/^https?:\/\/[^/]+/, ''))
                ? { borderColor: 'var(--color-primary)', color: 'var(--color-primary)' }
                : {}"
        >
            {{ tab.label }}
        </Link>
    </div>
</template>

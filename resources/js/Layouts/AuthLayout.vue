<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { PageProps } from '@/types'

const settings = computed(() => usePage<PageProps>().props.settings)
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
        <Link :href="route('home')" class="mb-8 text-2xl font-semibold tracking-tight text-gray-900">
            {{ settings.company_name }}
        </Link>

        <div class="w-full max-w-sm bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <slot />
        </div>
    </div>
</template>

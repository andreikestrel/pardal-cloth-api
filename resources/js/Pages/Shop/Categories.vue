<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Category } from '@/types'

defineProps<{ categories: Category[] }>()
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-10">
            <h1 class="text-2xl font-semibold mb-8">Categorias</h1>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('categories.show', category.slug)"
                    class="group rounded-2xl overflow-hidden border border-gray-200 hover:border-gray-400 transition-colors"
                >
                    <div class="aspect-video overflow-hidden bg-gray-100">
                        <img v-if="category.image_url" :src="category.image_url" :alt="category.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                    </div>
                    <div class="p-4">
                        <p class="font-medium text-gray-900">{{ category.name }}</p>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

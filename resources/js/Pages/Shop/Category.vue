<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Category, Product } from '@/types'

defineProps<{
    category: Category
    products: { data: Product[] }
}>()

function lowestPrice(product: Product): string {
    const prices = product.variations.map((v) => parseFloat(v.price))
    if (!prices.length) return '—'
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Math.min(...prices))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-10">
            <h1 class="text-2xl font-semibold mb-2">{{ category.name }}</h1>
            <p class="text-sm text-gray-500 mb-8">{{ products.data.length }} produtos</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                <Link v-for="product in products.data" :key="product.id"
                    :href="route('shop.show', product.slug)" class="group">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden bg-gray-50 mb-3 flex items-center justify-center p-4">
                        <img v-if="(product as any).cover_url" :src="(product as any).cover_url" :alt="product.name"
                            class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform" />
                    </div>
                    <p class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</p>
                    <p class="text-sm text-gray-500">A partir de {{ lowestPrice(product) }}</p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

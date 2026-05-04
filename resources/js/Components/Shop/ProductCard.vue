<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { Product } from '@/types'

defineProps<{ product: Product }>()

function lowestPrice(product: Product): string {
    const prices = product.variations.map((v) => parseFloat(v.price))
    if (!prices.length) return '—'
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Math.min(...prices))
}
</script>

<template>
    <Link :href="route('shop.show', product.slug)" class="group block">
        <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100 mb-3">
            <img
                v-if="product.images?.[0]"
                :src="product.images[0].url"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 text-sm">
                Sem imagem
            </div>
        </div>
        <p class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</p>
        <p class="text-sm text-gray-500 mt-0.5">A partir de {{ lowestPrice(product) }}</p>
    </Link>
</template>

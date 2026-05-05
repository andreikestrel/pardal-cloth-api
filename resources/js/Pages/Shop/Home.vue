<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeroCarousel from '@/Components/Shop/HeroCarousel.vue'
import type { Product, Category, Promotion } from '@/types'

interface Slide {
    id: string
    title: string | null
    subtitle: string | null
    link_url: string | null
    link_label: string | null
    image_url: string | null
}

defineProps<{
    slides: Slide[]
    featuredProducts: Product[]
    categories: Category[]
    activePromotions: Promotion[]
}>()

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function lowestPrice(product: Product): string {
    const prices = product.variations.map((v) => parseFloat(v.price))
    return prices.length ? formatCurrency(String(Math.min(...prices))) : 'R$ —'
}
</script>

<template>
    <AppLayout>
        <!-- Carousel (or fallback hero) -->
        <HeroCarousel v-if="slides.length" :slides="slides" />
        <section v-else class="bg-gray-900 text-white py-20 px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Moda com estilo</h1>
            <p class="text-gray-300 mb-8">As melhores peças para o seu guarda-roupa</p>
            <Link :href="route('shop.index')" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-medium hover:bg-gray-100">
                Ver coleção
            </Link>
        </section>

        <!-- Promotions banner -->
        <section v-if="activePromotions.length" class="bg-amber-50 border-b border-amber-200 py-3 px-4 text-center text-sm text-amber-800">
            <span v-for="(promo, i) in activePromotions" :key="promo.id">
                {{ promo.name }}<span v-if="i < activePromotions.length - 1" class="mx-2">·</span>
            </span>
        </section>

        <!-- Categories -->
        <section class="max-w-7xl mx-auto px-4 py-12">
            <h2 class="text-2xl font-semibold mb-6">Categorias</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('categories.show', category.slug)"
                    class="group rounded-xl overflow-hidden border border-gray-200 hover:border-gray-400 transition-colors"
                >
                    <img v-if="category.image_url" :src="category.image_url" :alt="category.name"
                        class="w-full h-32 object-cover group-hover:scale-105 transition-transform" />
                    <div class="p-3 text-sm font-medium text-gray-800">{{ category.name }}</div>
                </Link>
            </div>
        </section>

        <!-- Featured products -->
        <section class="max-w-7xl mx-auto px-4 pb-16">
            <h2 class="text-2xl font-semibold mb-6">Novidades</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                <Link
                    v-for="product in featuredProducts"
                    :key="product.id"
                    :href="route('shop.show', product.slug)"
                    class="group"
                >
                    <div class="aspect-[3/4] rounded-xl overflow-hidden bg-gray-100 mb-3">
                        <img v-if="product.images?.[0]" :src="product.images[0].url" :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                    </div>
                    <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                    <p class="text-sm text-gray-500">A partir de {{ lowestPrice(product) }}</p>
                </Link>
            </div>
        </section>
    </AppLayout>
</template>

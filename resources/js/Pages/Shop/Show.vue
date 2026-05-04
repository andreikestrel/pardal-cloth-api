<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useCart } from '@/Composables/useCart'
import type { Product, ProductVariation } from '@/types'

const props = defineProps<{
    product: Product
    related: Product[]
}>()

const { addItem } = useCart()

const selectedVariation = ref<ProductVariation | null>(null)
const quantity = ref(1)
const added = ref(false)

const sizes = computed(() => [...new Set(props.product.variations.map((v) => v.size))])
const selectedSize = ref<string | null>(null)

const colorsForSize = computed(() =>
    selectedSize.value
        ? props.product.variations.filter((v) => v.size === selectedSize.value)
        : []
)

function selectVariation(variation: ProductVariation) {
    selectedVariation.value = variation
}

function addToCart() {
    if (!selectedVariation.value) return

    addItem(selectedVariation.value.id, quantity.value)
    added.value = true
    setTimeout(() => (added.value = false), 2000)
}

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto px-4 py-10">
            <div class="flex flex-col md:flex-row gap-12">
                <!-- Image -->
                <div class="md:w-1/2">
                    <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100">
                        <img v-if="product.images?.[0]" :src="product.images[0].url" :alt="product.name"
                            class="w-full h-full object-cover" />
                    </div>
                </div>

                <!-- Info -->
                <div class="md:w-1/2">
                    <p class="text-sm text-gray-400 mb-2">
                        <Link :href="route('categories.show', product.category?.slug ?? '')">
                            {{ product.category?.name }}
                        </Link>
                    </p>
                    <h1 class="text-2xl font-semibold text-gray-900 mb-2">{{ product.name }}</h1>

                    <p v-if="selectedVariation" class="text-xl font-bold text-gray-900 mb-6">
                        {{ formatCurrency(selectedVariation.price) }}
                    </p>

                    <!-- Size picker -->
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Tamanho</p>
                        <div class="flex gap-2 flex-wrap">
                            <button
                                v-for="size in sizes"
                                :key="size"
                                @click="selectedSize = size; selectedVariation = null"
                                :class="['border rounded-lg px-3 py-1.5 text-sm transition-colors',
                                    selectedSize === size
                                        ? 'bg-gray-900 text-white border-gray-900'
                                        : 'border-gray-300 text-gray-700 hover:border-gray-600']"
                            >
                                {{ size }}
                            </button>
                        </div>
                    </div>

                    <!-- Color picker -->
                    <div v-if="selectedSize" class="mb-6">
                        <p class="text-sm font-medium text-gray-700 mb-2">Cor</p>
                        <div class="flex gap-2 flex-wrap">
                            <button
                                v-for="variation in colorsForSize"
                                :key="variation.id"
                                @click="selectVariation(variation)"
                                :disabled="variation.stock === 0"
                                :class="['border rounded-lg px-3 py-1.5 text-sm transition-colors',
                                    selectedVariation?.id === variation.id
                                        ? 'bg-gray-900 text-white border-gray-900'
                                        : 'border-gray-300 text-gray-700 hover:border-gray-600',
                                    variation.stock === 0 ? 'opacity-40 cursor-not-allowed line-through' : '']"
                            >
                                {{ variation.color }}
                            </button>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="flex items-center gap-3 mb-6">
                        <button @click="quantity = Math.max(1, quantity - 1)"
                            class="w-9 h-9 rounded-full border border-gray-300 text-lg hover:border-gray-600">−</button>
                        <span class="w-8 text-center text-sm font-medium">{{ quantity }}</span>
                        <button @click="quantity++"
                            class="w-9 h-9 rounded-full border border-gray-300 text-lg hover:border-gray-600">+</button>
                    </div>

                    <button
                        @click="addToCart"
                        :disabled="!selectedVariation"
                        :class="['w-full py-3 rounded-xl font-medium text-sm transition-colors',
                            added ? 'bg-green-600 text-white' : 'bg-gray-900 text-white hover:bg-gray-700',
                            !selectedVariation ? 'opacity-50 cursor-not-allowed' : '']"
                    >
                        {{ added ? 'Adicionado!' : 'Adicionar ao carrinho' }}
                    </button>

                    <p v-if="product.description" class="mt-8 text-sm text-gray-600 leading-relaxed">
                        {{ product.description }}
                    </p>
                </div>
            </div>

            <!-- Related products -->
            <section v-if="related.length" class="mt-16">
                <h2 class="text-xl font-semibold mb-6">Você também pode gostar</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <Link v-for="r in related" :key="r.id" :href="route('shop.show', r.slug)" class="group">
                        <div class="aspect-[3/4] rounded-xl overflow-hidden bg-gray-100 mb-2">
                            <img v-if="r.images?.[0]" :src="r.images[0].url" :alt="r.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ r.name }}</p>
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

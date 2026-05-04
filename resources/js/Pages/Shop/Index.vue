<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Product, Category } from '@/types'

const props = defineProps<{
    products: { data: Product[]; links: unknown[] }
    categories: Category[]
    filters: { search?: string; category?: string; min_price?: string; max_price?: string }
}>()

const search = ref(props.filters.search ?? '')
const selectedCategory = ref(props.filters.category ?? '')

let debounceTimer: ReturnType<typeof setTimeout>

watch([search, selectedCategory], () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        router.get(route('shop.index'), { search: search.value, category: selectedCategory.value }, { preserveState: true, replace: true })
    }, 400)
})

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function lowestPrice(product: Product): string {
    const prices = product.variations.map((v) => parseFloat(v.price))
    return prices.length ? formatCurrency(String(Math.min(...prices))) : '—'
}
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Filters sidebar -->
                <aside class="md:w-56 shrink-0">
                    <h2 class="font-semibold mb-4">Filtros</h2>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Buscar</label>
                        <input v-model="search" type="search" placeholder="Nome do produto..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Categoria</label>
                        <select v-model="selectedCategory" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Todas</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                        </select>
                    </div>
                </aside>

                <!-- Products grid -->
                <div class="flex-1">
                    <p class="text-sm text-gray-500 mb-6">{{ products.data.length }} produtos encontrados</p>

                    <div v-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-6">
                        <Link
                            v-for="product in products.data"
                            :key="product.id"
                            :href="route('shop.show', product.slug)"
                            class="group"
                        >
                            <div class="aspect-[3/4] rounded-xl overflow-hidden bg-gray-100 mb-3">
                                <img v-if="product.images?.[0]" :src="product.images[0].url" :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                            </div>
                            <p class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</p>
                            <p class="text-sm text-gray-500">A partir de {{ lowestPrice(product) }}</p>
                        </Link>
                    </div>

                    <p v-else class="text-gray-500 text-sm">Nenhum produto encontrado.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

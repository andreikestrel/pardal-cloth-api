<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Product, Category } from '@/types'

interface FacetTag { id: number; name: string; color: string }

interface Filters {
    q?: string
    categories?: string[]
    min_price?: string
    max_price?: string
    sizes?: string[]
    colors?: string[]
    tags?: number[]
}

const props = defineProps<{
    products: { data: Product[]; links: unknown[]; meta?: { total?: number } }
    categories: Category[]
    facets: { sizes: string[]; colors: string[]; tags: FacetTag[] }
    filters: Filters
}>()

const q = ref(props.filters.q ?? '')
const selectedCategories = ref<string[]>(props.filters.categories ?? [])
const selectedSizes = ref<string[]>(props.filters.sizes ?? [])
const selectedColors = ref<string[]>(props.filters.colors ?? [])
const selectedTags = ref<number[]>((props.filters.tags ?? []).map(Number))
const minPrice = ref(props.filters.min_price ?? '')
const maxPrice = ref(props.filters.max_price ?? '')
const showFilters = ref(false)

const activeFilterCount = computed(() =>
    selectedCategories.value.length
    + selectedSizes.value.length
    + selectedColors.value.length
    + selectedTags.value.length
    + (minPrice.value ? 1 : 0)
    + (maxPrice.value ? 1 : 0)
)

let debounceTimer: ReturnType<typeof setTimeout>

function applyFilters(immediate = false) {
    clearTimeout(debounceTimer)
    const fire = () => {
        router.get(route('shop.index'), {
            q:          q.value || undefined,
            categories: selectedCategories.value.length ? selectedCategories.value : undefined,
            sizes:      selectedSizes.value.length ? selectedSizes.value : undefined,
            colors:     selectedColors.value.length ? selectedColors.value : undefined,
            tags:       selectedTags.value.length ? selectedTags.value : undefined,
            min_price:  minPrice.value || undefined,
            max_price:  maxPrice.value || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }
    immediate ? fire() : (debounceTimer = setTimeout(fire, 250))
}

watch(q, () => applyFilters())

function toggle(list: string[], value: string): string[] {
    return list.includes(value) ? list.filter(v => v !== value) : [...list, value]
}

function toggleCategory(slug: string) {
    selectedCategories.value = toggle(selectedCategories.value, slug)
    applyFilters(true)
}
function toggleSize(s: string) {
    selectedSizes.value = toggle(selectedSizes.value, s)
    applyFilters(true)
}
function toggleColor(c: string) {
    selectedColors.value = toggle(selectedColors.value, c)
    applyFilters(true)
}
function toggleTag(id: number) {
    selectedTags.value = selectedTags.value.includes(id)
        ? selectedTags.value.filter(x => x !== id)
        : [...selectedTags.value, id]
    applyFilters(true)
}

function clearAll() {
    q.value = ''
    selectedCategories.value = []
    selectedSizes.value = []
    selectedColors.value = []
    selectedTags.value = []
    minPrice.value = ''
    maxPrice.value = ''
    applyFilters(true)
}

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
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Category chips -->
            <div class="flex gap-2 overflow-x-auto pb-2 mb-5 -mx-4 px-4">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="toggleCategory(cat.slug)"
                    :class="['px-4 py-2 rounded-full border text-sm font-medium whitespace-nowrap transition-colors',
                        selectedCategories.includes(cat.slug)
                            ? 'bg-gray-900 text-white border-gray-900'
                            : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500']">
                    {{ cat.name }}
                </button>
            </div>

            <!-- Search + filters bar -->
            <div class="flex gap-2 mb-6">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input v-model="q" type="search" placeholder="Buscar produtos..."
                        class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                </div>
                <button @click="showFilters = true"
                    class="relative inline-flex items-center gap-2 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-700 hover:border-gray-500 bg-white">
                    Filtros
                    <span v-if="activeFilterCount" class="bg-gray-900 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                        {{ activeFilterCount }}
                    </span>
                </button>
            </div>

            <!-- Results -->
            <p class="text-sm text-gray-500 mb-6">
                {{ products.meta?.total ?? products.data.length }} produto(s) encontrado(s)
            </p>

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
            <p v-else class="text-gray-500 text-sm py-12 text-center">Nenhum produto encontrado.</p>
        </div>

        <!-- Filters drawer -->
        <transition name="drawer">
            <div v-if="showFilters" class="fixed inset-0 z-50 flex">
                <div class="absolute inset-0 bg-black/40" @click="showFilters = false"></div>
                <aside class="relative ml-auto w-full max-w-sm bg-white h-full overflow-y-auto shadow-2xl flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Filtros</h2>
                        <button @click="showFilters = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                    </div>

                    <div class="flex-1 px-5 py-5 space-y-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Faixa de preço</h3>
                            <div class="flex gap-2 items-center">
                                <input v-model="minPrice" type="number" min="0" placeholder="Min"
                                    @input="applyFilters()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                                <span class="text-gray-400">—</span>
                                <input v-model="maxPrice" type="number" min="0" placeholder="Max"
                                    @input="applyFilters()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                            </div>
                        </div>

                        <div v-if="facets.sizes.length">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Tamanho</h3>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="s in facets.sizes" :key="s" @click="toggleSize(s)"
                                    :class="['px-3 py-1.5 rounded-lg border text-sm transition-colors',
                                        selectedSizes.includes(s)
                                            ? 'bg-gray-900 text-white border-gray-900'
                                            : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500']">
                                    {{ s }}
                                </button>
                            </div>
                        </div>

                        <div v-if="facets.colors.length">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Cor</h3>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="c in facets.colors" :key="c" @click="toggleColor(c)"
                                    :class="['px-3 py-1.5 rounded-lg border text-sm transition-colors',
                                        selectedColors.includes(c)
                                            ? 'bg-gray-900 text-white border-gray-900'
                                            : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500']">
                                    {{ c }}
                                </button>
                            </div>
                        </div>

                        <div v-if="facets.tags.length">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="tag in facets.tags" :key="tag.id" @click="toggleTag(tag.id)"
                                    :style="selectedTags.includes(tag.id)
                                        ? { backgroundColor: tag.color, borderColor: tag.color, color: '#fff' }
                                        : { borderColor: tag.color + '88', color: tag.color }"
                                    class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors bg-white hover:opacity-80">
                                    {{ tag.name }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 px-5 py-4 flex gap-3">
                        <button @click="clearAll"
                            class="flex-1 text-sm text-gray-600 border border-gray-300 rounded-xl py-2 hover:bg-gray-50">
                            Limpar tudo
                        </button>
                        <button @click="showFilters = false"
                            class="flex-1 text-sm text-white bg-gray-900 rounded-xl py-2 hover:bg-gray-700">
                            Aplicar
                        </button>
                    </div>
                </aside>
            </div>
        </transition>
    </AppLayout>
</template>

<style scoped>
.drawer-enter-active aside,
.drawer-leave-active aside {
    transition: transform 0.3s ease;
}
.drawer-enter-from aside,
.drawer-leave-to aside {
    transform: translateX(100%);
}
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.3s ease;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}
</style>

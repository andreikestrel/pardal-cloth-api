<script setup lang="ts">
import { watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useCart } from '@/Composables/useCart'

const {
    isOpen,
    items,
    calculation,
    isCalculating,
    isEmpty,
    closeCart,
    updateQuantity,
    removeItem,
    calculate,
} = useCart()

watch(isOpen, (open) => {
    if (open && items.value.length) calculate()
})

function formatBRL(value: string | number): string {
    const n = typeof value === 'string' ? parseFloat(value) : value
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(isFinite(n) ? n : 0)
}
</script>

<template>
    <transition name="drawer">
        <div v-if="isOpen" class="fixed inset-0 z-50 flex">
            <div class="absolute inset-0 bg-black/40" @click="closeCart"></div>

            <aside class="relative ml-auto w-full sm:max-w-md bg-white h-full overflow-hidden shadow-2xl flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                    <h2 class="font-semibold text-gray-900">Seu carrinho</h2>
                    <button @click="closeCart" aria-label="Fechar"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                <!-- Empty state -->
                <div v-if="isEmpty" class="flex-1 flex flex-col items-center justify-center text-center px-6 text-gray-400">
                    <svg class="w-14 h-14 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.4 7h12.8M7 13H5.4M17 21a1 1 0 100-2 1 1 0 000 2zm-10 0a1 1 0 100-2 1 1 0 000 2z" />
                    </svg>
                    <p class="text-sm">Seu carrinho está vazio.</p>
                    <Link :href="route('shop.index')" @click="closeCart"
                        class="mt-5 text-sm text-white px-5 py-2 rounded-xl"
                        style="background-color: var(--color-primary)">
                        Explorar produtos
                    </Link>
                </div>

                <!-- Items -->
                <template v-else>
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                        <div v-for="line in calculation?.items ?? []" :key="line.variation_id"
                            class="flex gap-3 pb-4 border-b border-gray-100 last:border-b-0">
                            <div class="w-20 h-24 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                                <img v-if="line.cover_url" :src="line.cover_url" :alt="line.product_name ?? ''"
                                    class="w-full h-full object-cover" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ line.product_name ?? 'Produto' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ line.size }} · {{ line.color }}
                                </p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ formatBRL(line.unit_price ?? '0') }}
                                </p>

                                <div class="flex items-center gap-2 mt-2">
                                    <button @click="updateQuantity(line.variation_id, line.quantity - 1)"
                                        class="w-7 h-7 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">−</button>
                                    <span class="text-sm w-6 text-center">{{ line.quantity }}</span>
                                    <button @click="updateQuantity(line.variation_id, line.quantity + 1)"
                                        class="w-7 h-7 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">+</button>
                                    <button @click="removeItem(line.variation_id)"
                                        class="ml-auto text-xs text-red-600 hover:text-red-700">Remover</button>
                                </div>
                            </div>
                        </div>

                        <p v-if="!calculation && isCalculating" class="text-center text-xs text-gray-400 py-4">
                            Calculando...
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-100 px-5 py-4 space-y-3 shrink-0 bg-white">
                        <div v-if="calculation" class="space-y-1 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span>{{ formatBRL(calculation.subtotal) }}</span>
                            </div>
                            <div v-if="parseFloat(calculation.discount_promotions) > 0" class="flex justify-between text-green-600">
                                <span>Promoções</span>
                                <span>−{{ formatBRL(calculation.discount_promotions) }}</span>
                            </div>
                            <div v-if="parseFloat(calculation.discount_coupon) > 0" class="flex justify-between text-green-600">
                                <span>Cupom</span>
                                <span>−{{ formatBRL(calculation.discount_coupon) }}</span>
                            </div>
                            <div class="flex justify-between font-semibold text-gray-900 pt-1">
                                <span>Total</span>
                                <span>{{ formatBRL(calculation.total) }}</span>
                            </div>
                        </div>

                        <Link :href="route('checkout.index')" @click="closeCart"
                            class="block w-full text-center text-white py-3 rounded-xl font-medium"
                            style="background-color: var(--color-primary)">
                            Finalizar compra
                        </Link>
                        <Link :href="route('cart.index')" @click="closeCart"
                            class="block w-full text-center text-sm text-gray-600 hover:text-gray-900">
                            Ver carrinho completo
                        </Link>
                    </div>
                </template>
            </aside>
        </div>
    </transition>
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

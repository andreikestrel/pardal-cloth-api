<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useCart } from '@/Composables/useCart'
import { usePromotion } from '@/Composables/usePromotion'

const {
    items, calculation, couponCode, isCalculating, isEmpty,
    removeItem, updateQuantity, calculate, applyCoupon, removeCoupon,
} = useCart()

const { formatCurrency, hasPromotions, promotionsSummary } = usePromotion(calculation)

const couponInput = ref('')
const couponError = ref('')
const couponSuccess = ref(false)

onMounted(() => calculate())

async function applyCode() {
    if (!couponInput.value.trim()) return
    couponError.value = ''
    couponSuccess.value = false

    await applyCoupon(couponInput.value.trim().toUpperCase())

    if (calculation.value?.discount_coupon && parseFloat(calculation.value.discount_coupon) > 0) {
        couponSuccess.value = true
    } else {
        couponError.value = 'Cupom inválido ou não aplicável ao seu carrinho.'
        removeCoupon()
    }
}

function handleRemoveCoupon() {
    couponInput.value = ''
    couponError.value = ''
    couponSuccess.value = false
    removeCoupon()
}
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 py-8 sm:py-12">
            <h1 class="text-2xl font-semibold text-gray-900 mb-8">Carrinho</h1>

            <!-- Empty state -->
            <div v-if="isEmpty" class="text-center py-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.4 7h12.8M17 21a1 1 0 100-2 1 1 0 000 2zm-10 0a1 1 0 100-2 1 1 0 000 2z" />
                </svg>
                <p class="text-gray-500 mb-6">Seu carrinho está vazio.</p>
                <Link :href="route('shop.index')"
                    class="inline-block px-6 py-2.5 text-sm font-medium text-white rounded-xl"
                    style="background-color: var(--color-primary)">
                    Explorar loja
                </Link>
            </div>

            <div v-else class="flex flex-col lg:flex-row gap-8">
                <!-- Items list -->
                <div class="flex-1 space-y-4">
                    <div v-for="item in (calculation?.items ?? items)" :key="item.variation_id"
                        class="flex gap-4 p-4 bg-white border border-gray-200 rounded-2xl">
                        <!-- Product image -->
                        <div class="w-20 h-20 rounded-xl bg-gray-100 shrink-0 overflow-hidden">
                            <img v-if="item.cover_url" :src="item.cover_url" :alt="item.product_name ?? ''"
                                class="w-full h-full object-cover" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">
                                {{ item.product_name ?? 'Produto' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ item.size }} · {{ item.color }}
                            </p>
                            <p v-if="item.unit_price" class="text-sm font-medium text-gray-900 mt-1">
                                {{ formatCurrency(item.unit_price) }}
                            </p>
                        </div>

                        <!-- Quantity stepper -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="updateQuantity(item.variation_id, item.quantity - 1)"
                                class="w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:border-gray-500 flex items-center justify-center text-lg leading-none">
                                −
                            </button>
                            <span class="w-6 text-center text-sm font-medium">{{ item.quantity }}</span>
                            <button @click="updateQuantity(item.variation_id, item.quantity + 1)"
                                class="w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:border-gray-500 flex items-center justify-center text-lg leading-none">
                                +
                            </button>
                        </div>

                        <!-- Remove -->
                        <button @click="removeItem(item.variation_id)"
                            class="text-gray-400 hover:text-red-500 transition-colors shrink-0 self-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Summary panel -->
                <aside class="lg:w-80 shrink-0">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 sticky top-20">
                        <h2 class="font-semibold text-gray-900 mb-4">Resumo</h2>

                        <div v-if="isCalculating" class="text-sm text-gray-400 py-4 text-center">Calculando…</div>

                        <div v-else-if="calculation" class="space-y-2.5">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(calculation.subtotal) }}</span>
                            </div>

                            <div v-for="promo in promotionsSummary" :key="promo.name"
                                class="flex justify-between text-sm text-green-700">
                                <span class="truncate mr-2">{{ promo.name }}</span>
                                <span class="shrink-0">−{{ formatCurrency(promo.discount) }}</span>
                            </div>

                            <div v-if="couponCode && parseFloat(calculation.discount_coupon) > 0"
                                class="flex justify-between text-sm text-green-700">
                                <span>Cupom ({{ couponCode }})</span>
                                <span>−{{ formatCurrency(calculation.discount_coupon) }}</span>
                            </div>

                            <div class="pt-3 border-t border-gray-200 flex justify-between font-semibold text-gray-900">
                                <span>Total</span>
                                <span>{{ formatCurrency(calculation.total) }}</span>
                            </div>
                        </div>

                        <!-- Coupon -->
                        <div class="mt-5 space-y-2">
                            <div v-if="!couponCode" class="flex gap-2">
                                <input v-model="couponInput" type="text" placeholder="Código do cupom"
                                    @keyup.enter="applyCode"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 uppercase" />
                                <button @click="applyCode"
                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                                    Aplicar
                                </button>
                            </div>

                            <p v-if="couponError" class="text-xs text-red-600">{{ couponError }}</p>

                            <div v-if="couponSuccess && couponCode" class="flex items-center justify-between text-xs text-green-700 bg-green-50 rounded-lg px-3 py-2">
                                <span>Cupom <strong>{{ couponCode }}</strong> aplicado!</span>
                                <button @click="handleRemoveCoupon" class="text-green-600 hover:text-green-800 font-medium ml-2">remover</button>
                            </div>
                        </div>

                        <!-- CTA -->
                        <Link :href="route('checkout.index')"
                            class="mt-5 block text-center text-white py-3 rounded-xl font-medium text-sm transition-opacity hover:opacity-90"
                            style="background-color: var(--color-primary)">
                            Finalizar compra
                        </Link>

                        <Link :href="route('shop.index')" class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-700">
                            Continuar comprando
                        </Link>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

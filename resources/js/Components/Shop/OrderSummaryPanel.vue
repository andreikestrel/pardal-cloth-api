<script setup lang="ts">
import { ref } from 'vue'
import type { CartCalculation } from '@/types'
import { usePromotion } from '@/Composables/usePromotion'

const props = defineProps<{
    calculation: CartCalculation | null
    isCalculating?: boolean
    couponCode?: string | null
    showCouponInput?: boolean
}>()

const emit = defineEmits<{
    applyCoupon: [code: string]
    removeCoupon: []
}>()

const { formatCurrency, hasPromotions, promotionsSummary } = usePromotion(
    { value: props.calculation } as any
)

const couponInput = ref('')
const couponError = ref('')

async function apply() {
    if (!couponInput.value.trim()) return
    couponError.value = ''
    emit('applyCoupon', couponInput.value.trim().toUpperCase())
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Resumo do pedido</h2>

        <div v-if="isCalculating" class="text-sm text-gray-400 py-2 text-center">Calculando…</div>

        <div v-else-if="calculation" class="space-y-2.5 text-sm">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>{{ formatCurrency(calculation.subtotal) }}</span>
            </div>

            <div v-for="promo in promotionsSummary" :key="promo.name"
                class="flex justify-between text-green-700">
                <span class="truncate mr-2">{{ promo.name }}</span>
                <span class="shrink-0">−{{ formatCurrency(promo.discount) }}</span>
            </div>

            <div v-if="couponCode && parseFloat(calculation.discount_coupon) > 0"
                class="flex justify-between text-green-700">
                <span>Cupom ({{ couponCode }})</span>
                <span>−{{ formatCurrency(calculation.discount_coupon) }}</span>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-between font-semibold text-gray-900">
                <span>Total</span>
                <span>{{ formatCurrency(calculation.total) }}</span>
            </div>
        </div>

        <!-- Coupon input -->
        <div v-if="showCouponInput" class="mt-5 space-y-2">
            <div v-if="!couponCode" class="flex gap-2">
                <input v-model="couponInput" type="text" placeholder="Código do cupom"
                    @keyup.enter="apply"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-gray-400" />
                <button @click="apply"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg font-medium transition-colors">
                    Aplicar
                </button>
            </div>
            <p v-if="couponError" class="text-xs text-red-600">{{ couponError }}</p>
            <div v-if="couponCode" class="flex items-center justify-between text-xs text-green-700 bg-green-50 rounded-lg px-3 py-2">
                <span>Cupom <strong>{{ couponCode }}</strong> aplicado</span>
                <button @click="emit('removeCoupon')" class="font-medium ml-2 hover:text-green-900">remover</button>
            </div>
        </div>

        <slot />
    </div>
</template>

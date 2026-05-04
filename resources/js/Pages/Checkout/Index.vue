<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useCart } from '@/Composables/useCart'
import { usePromotion } from '@/Composables/usePromotion'

const { items, calculation, isEmpty, calculate, applyCoupon, removeCoupon, couponCode } = useCart()
const { formatCurrency, hasPromotions, promotionsSummary, totalDiscount } = usePromotion(calculation)

const couponInput = ref('')
const couponError = ref('')

const form = useForm({
    items: [] as { variation_id: string; quantity: number }[],
    coupon_code: null as string | null,
    shipping_address: { street: '', number: '', complement: '', district: '', city: '', state: '', zip_code: '' },
    frontend_total: '0',
})

onMounted(() => calculate())

async function applyCode() {
    couponError.value = ''
    try {
        await applyCoupon(couponInput.value)
    } catch {
        couponError.value = 'Cupom inválido ou não aplicável.'
    }
}

function submit() {
    form.items = items.value.map((i) => ({ variation_id: i.variation_id, quantity: i.quantity }))
    form.coupon_code = couponCode.value
    form.frontend_total = calculation.value?.total ?? '0'
    form.post(route('orders.store'))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 py-10">
            <h1 class="text-2xl font-semibold mb-8">Finalizar compra</h1>

            <div v-if="isEmpty" class="text-gray-500 text-sm">Seu carrinho está vazio.</div>

            <div v-else class="flex flex-col lg:flex-row gap-10">
                <!-- Shipping form -->
                <form @submit.prevent="submit" class="flex-1 space-y-4">
                    <h2 class="font-medium text-gray-900">Endereço de entrega</h2>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm text-gray-700 mb-1">Rua</label>
                            <input v-model="form.shipping_address.street" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Número</label>
                            <input v-model="form.shipping_address.number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Complemento</label>
                        <input v-model="form.shipping_address.complement" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Bairro</label>
                            <input v-model="form.shipping_address.district" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">CEP</label>
                            <input v-model="form.shipping_address.zip_code" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Cidade</label>
                            <input v-model="form.shipping_address.city" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Estado (UF)</label>
                            <input v-model="form.shipping_address.state" required maxlength="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase" />
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-gray-900 text-white py-3 rounded-xl font-medium hover:bg-gray-700 disabled:opacity-50">
                        Continuar para pagamento
                    </button>
                </form>

                <!-- Order summary -->
                <aside class="lg:w-80">
                    <div class="border border-gray-200 rounded-2xl p-6 space-y-4">
                        <h2 class="font-medium text-gray-900">Resumo do pedido</h2>

                        <div v-if="calculation">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(calculation.subtotal) }}</span>
                            </div>

                            <template v-if="hasPromotions">
                                <div v-for="promo in promotionsSummary" :key="promo.name"
                                    class="flex justify-between text-sm text-green-700">
                                    <span>{{ promo.name }}</span>
                                    <span>−{{ formatCurrency(promo.discount) }}</span>
                                </div>
                            </template>

                            <div v-if="couponCode" class="flex justify-between text-sm text-green-700">
                                <span>Cupom ({{ couponCode }})</span>
                                <span>−{{ formatCurrency(calculation.discount_coupon) }}</span>
                            </div>

                            <div class="flex justify-between font-semibold text-gray-900 pt-3 border-t border-gray-200">
                                <span>Total</span>
                                <span>{{ formatCurrency(calculation.total) }}</span>
                            </div>
                        </div>

                        <!-- Coupon input -->
                        <div class="pt-2">
                            <div class="flex gap-2">
                                <input v-model="couponInput" type="text" placeholder="Código do cupom"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                                <button @click="applyCode" type="button"
                                    class="bg-gray-100 text-gray-800 px-3 py-2 rounded-lg text-sm hover:bg-gray-200">
                                    Aplicar
                                </button>
                            </div>
                            <p v-if="couponError" class="text-red-600 text-xs mt-1">{{ couponError }}</p>
                            <button v-if="couponCode" @click="removeCoupon" type="button"
                                class="text-xs text-gray-400 hover:text-gray-600 mt-1">
                                Remover cupom
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

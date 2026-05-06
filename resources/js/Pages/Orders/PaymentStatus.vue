<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useCart } from '@/Composables/useCart'
import type { Order } from '@/types'

const props = defineProps<{ order: Order }>()

const { clearCart } = useCart()
const payment = ref(props.order.payment)
const polling = ref(true)
let timer: ReturnType<typeof setInterval> | null = null

async function checkStatus() {
    try {
        const res = await fetch(route('orders.payment-status', props.order.id), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        if (!res.ok) return

        const data = await res.json()
        payment.value = data.order?.payment ?? payment.value

        if (payment.value?.status === 'approved') {
            polling.value = false
            clearInterval(timer!)
            clearCart()
            setTimeout(() => router.visit(route('orders.show', props.order.id)), 1500)
        }
    } catch {
        // silently retry on next tick
    }
}

onMounted(() => {
    if (payment.value?.status !== 'approved') {
        timer = setInterval(checkStatus, 5000)
    }
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-lg mx-auto px-4 py-16 text-center">
            <!-- Approved -->
            <template v-if="payment?.status === 'approved'">
                <div class="text-6xl mb-4">✅</div>
                <h1 class="text-xl font-semibold text-gray-900 mb-2">Pagamento aprovado!</h1>
                <p class="text-gray-500 text-sm">Redirecionando para o pedido…</p>
            </template>

            <!-- Pix -->
            <template v-else-if="payment?.method === 'pix'">
                <h1 class="text-xl font-semibold text-gray-900 mb-6">Pague via Pix</h1>
                <img v-if="payment.pix_qr_code"
                    :src="`data:image/png;base64,${payment.pix_qr_code}`"
                    alt="QR Code Pix"
                    class="w-52 h-52 mx-auto mb-5 rounded-xl border border-gray-200" />
                <p class="text-sm text-gray-500 mb-3">Ou copie o código abaixo:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-xs text-gray-600 break-all mb-4">
                    {{ payment.pix_code }}
                </div>
                <button @click="navigator.clipboard.writeText(payment!.pix_code!)"
                    class="border border-gray-300 rounded-xl px-5 py-2.5 text-sm hover:bg-gray-50 transition-colors">
                    Copiar código Pix
                </button>
                <p v-if="polling" class="mt-6 text-xs text-gray-400 animate-pulse">
                    Aguardando confirmação do pagamento…
                </p>
            </template>

            <!-- Boleto -->
            <template v-else-if="payment?.method === 'boleto'">
                <div class="text-5xl mb-4">📄</div>
                <h1 class="text-xl font-semibold text-gray-900 mb-3">Boleto gerado</h1>
                <p class="text-sm text-gray-500 mb-6">
                    Valor: <strong>{{ formatCurrency(payment.amount) }}</strong>
                </p>
                <a :href="payment.boleto_url!" target="_blank"
                    class="inline-block text-white px-6 py-3 rounded-xl text-sm font-medium"
                    style="background-color: var(--color-primary)">
                    Visualizar boleto
                </a>
                <p v-if="polling" class="mt-6 text-xs text-gray-400 animate-pulse">
                    Aguardando confirmação do pagamento…
                </p>
            </template>

            <!-- Checkout Pro redirect -->
            <template v-else-if="payment?.method === 'checkout_pro'">
                <div class="text-5xl mb-4">💳</div>
                <h1 class="text-xl font-semibold text-gray-900 mb-3">Pague com cartão</h1>
                <a v-if="payment.payment_url" :href="payment.payment_url"
                    class="inline-block text-white px-6 py-3 rounded-xl text-sm font-medium bg-blue-600 hover:bg-blue-700">
                    Ir para o Mercado Pago
                </a>
            </template>

            <!-- Generic pending -->
            <template v-else>
                <div class="text-5xl mb-4 animate-spin">⏳</div>
                <h1 class="text-xl font-semibold text-gray-900">Processando pagamento…</h1>
            </template>
        </div>
    </AppLayout>
</template>

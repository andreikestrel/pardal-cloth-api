<script setup lang="ts">
import { ref } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Order } from '@/types'

defineProps<{ order?: Order }>()

const method = ref<'pix' | 'boleto' | 'checkout_pro'>('pix')
const loading = ref(false)
const error = ref('')

// Payment result fields shown after initiation
const pixCode = ref<string | null>(null)
const pixQrCode = ref<string | null>(null)
const boletoUrl = ref<string | null>(null)
const paymentUrl = ref<string | null>(null)

const methods = [
    { value: 'pix', label: 'Pix', description: 'Pagamento instantâneo via Pix' },
    { value: 'boleto', label: 'Boleto', description: 'Vencimento em 3 dias úteis' },
    { value: 'checkout_pro', label: 'Cartão (Mercado Pago)', description: 'Pagamento com cartão de crédito' },
] as const
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto px-4 py-10">
            <h1 class="text-2xl font-semibold mb-8">Escolha a forma de pagamento</h1>

            <!-- Method selection -->
            <div class="space-y-3 mb-8">
                <label
                    v-for="m in methods"
                    :key="m.value"
                    :class="['flex items-center gap-4 border rounded-xl px-4 py-3 cursor-pointer transition-colors',
                        method === m.value ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-400']"
                >
                    <input type="radio" :value="m.value" v-model="method" class="accent-gray-900" />
                    <div>
                        <p class="font-medium text-sm text-gray-900">{{ m.label }}</p>
                        <p class="text-xs text-gray-500">{{ m.description }}</p>
                    </div>
                </label>
            </div>

            <p v-if="error" class="text-red-600 text-sm mb-4">{{ error }}</p>

            <!-- Pix result -->
            <div v-if="pixCode" class="border border-gray-200 rounded-2xl p-6 space-y-4">
                <h2 class="font-medium text-gray-900">Pague via Pix</h2>
                <img v-if="pixQrCode" :src="`data:image/png;base64,${pixQrCode}`" alt="QR Code Pix"
                    class="w-48 h-48 mx-auto" />
                <div class="bg-gray-50 rounded-lg px-3 py-2 text-xs text-gray-600 break-all">{{ pixCode }}</div>
                <button @click="navigator.clipboard.writeText(pixCode!)"
                    class="w-full border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Copiar código Pix
                </button>
            </div>

            <!-- Boleto result -->
            <div v-else-if="boletoUrl" class="border border-gray-200 rounded-2xl p-6 text-center space-y-4">
                <h2 class="font-medium text-gray-900">Boleto gerado</h2>
                <a :href="boletoUrl" target="_blank"
                    class="inline-block bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-gray-700">
                    Visualizar boleto
                </a>
            </div>

            <!-- Checkout Pro redirect -->
            <div v-else-if="paymentUrl" class="border border-gray-200 rounded-2xl p-6 text-center space-y-4">
                <h2 class="font-medium text-gray-900">Redirecionando para o Mercado Pago…</h2>
                <a :href="paymentUrl"
                    class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-blue-700">
                    Ir para o pagamento
                </a>
            </div>

            <!-- Initiate button (shown before payment is started) -->
            <button
                v-if="!pixCode && !boletoUrl && !paymentUrl"
                type="submit"
                :disabled="loading"
                class="w-full bg-gray-900 text-white py-3 rounded-xl font-medium hover:bg-gray-700 disabled:opacity-50"
            >
                {{ loading ? 'Aguarde…' : 'Confirmar pagamento' }}
            </button>
        </div>
    </AppLayout>
</template>

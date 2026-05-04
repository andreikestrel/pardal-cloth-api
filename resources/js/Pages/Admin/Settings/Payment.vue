<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

interface PaymentSettings {
    gateway: 'mercadopago' | 'asaas'
    mercadopago_public_key: string | null
    mercadopago_access_token_masked: string | null
    asaas_api_key_masked: string | null
    pix_enabled: boolean
    boleto_enabled: boolean
    credit_card_enabled: boolean
    checkout_pro_enabled: boolean
}

const props = defineProps<{ paymentSettings: PaymentSettings }>()

const form = useForm({
    gateway:              props.paymentSettings.gateway,
    mercadopago_public_key:    props.paymentSettings.mercadopago_public_key ?? '',
    mercadopago_access_token:  '',
    asaas_api_key:             '',
    pix_enabled:          props.paymentSettings.pix_enabled,
    boleto_enabled:       props.paymentSettings.boleto_enabled,
    credit_card_enabled:  props.paymentSettings.credit_card_enabled,
    checkout_pro_enabled: props.paymentSettings.checkout_pro_enabled,
})

const isMercadoPago = computed(() => form.gateway === 'mercadopago')
const isAsaas       = computed(() => form.gateway === 'asaas')

function submit() {
    form.put(route('admin.settings.payment.update'))
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Configurações de pagamento" />

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Gateway selector -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                    <h2 class="font-medium text-gray-900">Gateway</h2>

                    <div class="flex gap-4">
                        <label v-for="gw in ['mercadopago', 'asaas']" :key="gw"
                            :class="['flex-1 border-2 rounded-xl px-4 py-3 cursor-pointer transition-colors text-sm font-medium text-center',
                                form.gateway === gw ? 'border-current text-current' : 'border-gray-200 text-gray-500 hover:border-gray-300']"
                            :style="form.gateway === gw ? { borderColor: 'var(--color-primary)', color: 'var(--color-primary)' } : {}">
                            <input type="radio" v-model="form.gateway" :value="gw" class="sr-only" />
                            {{ gw === 'mercadopago' ? 'Mercado Pago' : 'Asaas' }}
                        </label>
                    </div>
                </div>

                <!-- Mercado Pago credentials -->
                <div v-if="isMercadoPago" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                    <h2 class="font-medium text-gray-900">Credenciais Mercado Pago</h2>

                    <FormField label="Public Key" :error="form.errors.mercadopago_public_key">
                        <input v-model="form.mercadopago_public_key" type="text"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>

                    <FormField label="Access Token" :error="form.errors.mercadopago_access_token">
                        <input v-model="form.mercadopago_access_token" type="password"
                            :placeholder="paymentSettings.mercadopago_access_token_masked ?? 'Deixe em branco para manter'"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        <p class="text-xs text-gray-400 mt-1">Deixe em branco para manter o token atual.</p>
                    </FormField>
                </div>

                <!-- Asaas credentials -->
                <div v-if="isAsaas" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                    <h2 class="font-medium text-gray-900">Credenciais Asaas</h2>

                    <FormField label="API Key" :error="form.errors.asaas_api_key">
                        <input v-model="form.asaas_api_key" type="password"
                            :placeholder="paymentSettings.asaas_api_key_masked ?? 'Deixe em branco para manter'"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        <p class="text-xs text-gray-400 mt-1">Deixe em branco para manter a chave atual.</p>
                    </FormField>
                </div>

                <!-- Payment methods -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-3">
                    <h2 class="font-medium text-gray-900 mb-4">Métodos de pagamento</h2>

                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                        <input v-model="form.pix_enabled" type="checkbox" class="rounded" />
                        <span>Pix</span>
                    </label>
                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                        <input v-model="form.boleto_enabled" type="checkbox" class="rounded" />
                        <span>Boleto</span>
                    </label>
                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                        <input v-model="form.credit_card_enabled" type="checkbox" class="rounded" />
                        <span>Cartão de crédito</span>
                    </label>
                    <label v-if="isMercadoPago" class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                        <input v-model="form.checkout_pro_enabled" type="checkbox" class="rounded" />
                        <span>Checkout Pro (Mercado Pago)</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :loading="form.processing">
                        Salvar pagamentos
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

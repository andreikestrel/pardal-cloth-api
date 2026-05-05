<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import SettingsTabs from '@/Components/Admin/SettingsTabs.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

type GatewayName = 'mercadopago' | 'asaas'

interface Gateway {
    id: string
    gateway: GatewayName
    active: boolean
    credentials: Record<string, string>
}

const props = defineProps<{ gateways: Gateway[] }>()

const selected = ref<GatewayName>(
    (props.gateways.find(g => g.active)?.gateway ?? 'mercadopago') as GatewayName
)

const current = computed<Gateway | undefined>(() =>
    props.gateways.find(g => g.gateway === selected.value)
)

const CREDENTIAL_FIELDS: Record<GatewayName, { key: string, label: string, placeholder: string }[]> = {
    mercadopago: [
        { key: 'public_key',   label: 'Public Key',   placeholder: 'APP_USR-...' },
        { key: 'access_token', label: 'Access Token', placeholder: 'APP_USR-...' },
    ],
    asaas: [
        { key: 'api_key', label: 'API Key', placeholder: '$aact_...' },
    ],
}

const fields = computed(() => CREDENTIAL_FIELDS[selected.value])

const form = useForm({
    gateway:     selected.value,
    active:      current.value?.active ?? false,
    credentials: {} as Record<string, string>,
})

function selectGateway(g: GatewayName) {
    selected.value = g
    form.gateway = g
    form.active = props.gateways.find(x => x.gateway === g)?.active ?? false
    form.credentials = {}
}

function submit() {
    form.put(route('admin.settings.payment.update'), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Configurações" />
        <SettingsTabs />

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                    <h2 class="font-medium text-gray-900">Gateway</h2>

                    <div class="flex gap-3">
                        <button v-for="gw in (['mercadopago', 'asaas'] as GatewayName[])" :key="gw"
                            type="button" @click="selectGateway(gw)"
                            :class="['flex-1 border-2 rounded-xl px-4 py-3 transition-colors text-sm font-medium text-center',
                                selected === gw ? 'text-current' : 'border-gray-200 text-gray-500 hover:border-gray-300']"
                            :style="selected === gw ? { borderColor: 'var(--color-primary)', color: 'var(--color-primary)' } : {}">
                            {{ gw === 'mercadopago' ? 'Mercado Pago' : 'Asaas' }}
                        </button>
                    </div>

                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                        <input v-model="form.active" type="checkbox" class="rounded" />
                        <span>Gateway ativo</span>
                    </label>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                    <h2 class="font-medium text-gray-900">
                        Credenciais {{ selected === 'mercadopago' ? 'Mercado Pago' : 'Asaas' }}
                    </h2>

                    <FormField v-for="f in fields" :key="f.key" :label="f.label">
                        <input v-model="form.credentials[f.key]" type="password"
                            :placeholder="current?.credentials?.[f.key] || f.placeholder"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>

                    <p class="text-xs text-gray-400">
                        Deixe em branco para manter as credenciais atuais.
                    </p>
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

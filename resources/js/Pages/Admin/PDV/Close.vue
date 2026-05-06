<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

interface SessionData {
    id: string
    register: string
    operator: string
    opened_at: string
    opening_balance: string
}

interface Summary {
    total_sales: number
    total_revenue: string
    cash_sales: string
    expected_balance: string
}

const props = defineProps<{
    session: SessionData
    summary: Summary
}>()

const closingBalance = ref('')
const notes = ref('')
const submitting = ref(false)
const error = ref('')

const difference = computed(() => {
    const counted = parseFloat(closingBalance.value) || 0
    const expected = parseFloat(props.summary.expected_balance) || 0
    return counted - expected
})

const differenceFormatted = computed(() => {
    const diff = difference.value
    const abs = Math.abs(diff).toFixed(2)
    const sign = diff >= 0 ? '+' : '-'
    return `${sign} R$ ${abs.replace('.', ',')}`
})

const differenceClass = computed(() => {
    if (!closingBalance.value) return 'text-gray-400'
    return difference.value >= 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'
})

function formatBRL(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}

function formatDateTime(iso: string): string {
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(iso))
}

function submit() {
    if (!closingBalance.value) {
        error.value = 'Informe o saldo contado em caixa.'
        return
    }
    submitting.value = true
    error.value = ''
    router.post(
        route('admin.pdv.sessions.close.confirm', props.session.id),
        { closing_balance: closingBalance.value, notes: notes.value },
        {
            onError: (errors) => {
                error.value = Object.values(errors).join(' ')
                submitting.value = false
            },
        }
    )
}
</script>

<template>
    <AdminLayout>
        <PageHeader
            title="Fechar caixa"
            :subtitle="`${session.register} — ${session.operator}`"
        />

        <div class="max-w-2xl space-y-6">
            <!-- Session info -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                <h2 class="font-semibold text-gray-900">Resumo da sessão</h2>

                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Abertura</dt>
                        <dd class="font-medium text-gray-900">{{ formatDateTime(session.opened_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Saldo de abertura</dt>
                        <dd class="font-medium text-gray-900">{{ formatBRL(session.opening_balance) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total de vendas</dt>
                        <dd class="font-medium text-gray-900">{{ summary.total_sales }} venda{{ summary.total_sales !== 1 ? 's' : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Receita total</dt>
                        <dd class="font-medium text-gray-900">{{ formatBRL(summary.total_revenue) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vendas em dinheiro</dt>
                        <dd class="font-medium text-gray-900">{{ formatBRL(summary.cash_sales) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Saldo esperado em caixa</dt>
                        <dd class="font-semibold text-gray-900">{{ formatBRL(summary.expected_balance) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Closing form -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                <h2 class="font-semibold text-gray-900">Conferência de caixa</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Saldo contado (R$)
                    </label>
                    <input
                        v-model="closingBalance"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0,00"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"
                    />
                </div>

                <!-- Difference indicator -->
                <div v-if="closingBalance" class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                    <span class="text-sm text-gray-600">Diferença</span>
                    <span :class="['text-sm', differenceClass]">{{ differenceFormatted }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea
                        v-model="notes"
                        rows="3"
                        placeholder="Opcional — anotações sobre a sessão"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-gray-400"
                    />
                </div>

                <p v-if="error" class="text-xs text-red-600">{{ error }}</p>

                <div class="flex gap-3 pt-1">
                    <a
                        :href="route('admin.pdv.terminal')"
                        class="flex-1 text-center text-sm border border-gray-300 rounded-xl py-2.5 hover:bg-gray-50"
                    >
                        Cancelar
                    </a>
                    <button
                        @click="submit"
                        :disabled="submitting"
                        class="flex-1 text-sm text-white rounded-xl py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 transition-colors"
                    >
                        {{ submitting ? 'Fechando...' : 'Fechar caixa' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

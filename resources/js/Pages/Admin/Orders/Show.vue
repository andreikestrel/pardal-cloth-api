<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import StatusTimeline from '@/Components/Orders/StatusTimeline.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import type { Order, OrderStatus } from '@/types'

const props = defineProps<{
    order: Order & {
        user: { name: string; email: string } | null
        pdv_customer_name?: string | null
        pdv_customer_doc?: string | null
        source?: string
    }
}>()

const statusForm = useForm({
    status: props.order.status as OrderStatus,
    note:   '',
})

const ALL_STATUSES: { value: OrderStatus; label: string }[] = [
    { value: 'pending',   label: 'Aguardando' },
    { value: 'confirmed', label: 'Confirmado' },
    { value: 'preparing', label: 'Preparando' },
    { value: 'shipped',   label: 'Enviado' },
    { value: 'delivered', label: 'Entregue' },
    { value: 'cancelled', label: 'Cancelado' },
]

function submitStatus() {
    statusForm.put(route('admin.orders.update', props.order.id), { preserveScroll: true })
}

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long' }).format(new Date(date))
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="`Pedido #${order.id.slice(0, 8).toUpperCase()}`">
            <template #action>
                <Link :href="route('admin.orders.index')"
                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                    ← Voltar
                </Link>
            </template>
        </PageHeader>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Left column: timeline + items -->
            <div class="md:col-span-2 space-y-6">
                <!-- Timeline -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h2 class="font-medium text-gray-900 mb-5">Acompanhamento</h2>
                    <StatusTimeline :current-status="order.status" :history="order.status_history ?? []" />
                </div>

                <!-- Items -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-medium text-gray-900">Itens</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="item in order.items" :key="item.id"
                            class="flex items-center justify-between px-5 py-3 text-sm">
                            <div>
                                <p class="font-medium text-gray-900">{{ item.variation?.product?.name ?? item.variation_id }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ item.variation?.size }} · {{ item.variation?.color }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-gray-700">{{ item.quantity }}× {{ formatCurrency(item.unit_price) }}</p>
                                <p v-if="parseFloat(item.discount) > 0" class="text-green-600 text-xs">
                                    −{{ formatCurrency(item.discount) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: status change + summary + customer -->
            <div class="space-y-4">
                <!-- Status update form -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h2 class="font-medium text-gray-900 mb-4">Atualizar status</h2>
                    <form @submit.prevent="submitStatus" class="space-y-3">
                        <FormField label="Novo status" :error="statusForm.errors.status">
                            <select v-model="statusForm.status"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                <option v-for="s in ALL_STATUSES" :key="s.value" :value="s.value">
                                    {{ s.label }}
                                </option>
                            </select>
                        </FormField>
                        <FormField label="Nota (opcional)" :error="statusForm.errors.note">
                            <input v-model="statusForm.note" type="text" placeholder="ex: Código de rastreio..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        </FormField>
                        <PrimaryButton type="submit" :loading="statusForm.processing" class="w-full justify-center">
                            Salvar status
                        </PrimaryButton>
                    </form>
                </div>

                <!-- Price breakdown -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5 text-sm space-y-2.5">
                    <h2 class="font-medium text-gray-900 mb-3">Valores</h2>
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ formatCurrency(order.subtotal) }}</span>
                    </div>
                    <div v-if="parseFloat(order.discount_promotions) > 0"
                        class="flex justify-between text-green-700">
                        <span>Descontos</span>
                        <span>−{{ formatCurrency(order.discount_promotions) }}</span>
                    </div>
                    <div v-if="parseFloat(order.discount_coupon) > 0"
                        class="flex justify-between text-green-700">
                        <span>Cupom</span>
                        <span>−{{ formatCurrency(order.discount_coupon) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-gray-900 pt-2.5 border-t border-gray-200">
                        <span>Total</span>
                        <span>{{ formatCurrency(order.total) }}</span>
                    </div>
                </div>

                <!-- Customer -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5 text-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-medium text-gray-900">Cliente</h2>
                        <span v-if="order.source === 'pdv'"
                            class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                            PDV
                        </span>
                    </div>

                    <!-- Online order: user account -->
                    <template v-if="order.user">
                        <p class="text-gray-800 font-medium">{{ order.user.name }}</p>
                        <p class="text-gray-500">{{ order.user.email }}</p>
                    </template>

                    <!-- PDV walk-in customer -->
                    <template v-else>
                        <p class="text-gray-800 font-medium">
                            {{ order.pdv_customer_name ?? 'Cliente não identificado' }}
                        </p>
                        <p v-if="order.pdv_customer_doc" class="text-gray-500">
                            CPF: {{ order.pdv_customer_doc }}
                        </p>
                    </template>

                    <!-- Shipping address (online only) -->
                    <div v-if="order.shipping_address"
                        class="mt-3 pt-3 border-t border-gray-100 text-gray-600 leading-relaxed">
                        <p>{{ order.shipping_address.street }}, {{ order.shipping_address.number }}</p>
                        <p>{{ order.shipping_address.district }} — {{ order.shipping_address.city }}/{{ order.shipping_address.state }}</p>
                        <p>CEP {{ order.shipping_address.zip }}</p>
                    </div>
                    <p v-else class="mt-2 text-gray-400 text-xs">Venda presencial — sem endereço de entrega.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

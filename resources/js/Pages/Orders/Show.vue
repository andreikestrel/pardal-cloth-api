<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import StatusTimeline from '@/Components/Orders/StatusTimeline.vue'
import { useOrderSocket } from '@/Composables/useOrderSocket'
import type { Order, OrderStatus } from '@/types'

const props = defineProps<{ order: Order }>()

const { currentStatus, lastNote, lastLabel } = useOrderSocket(props.order.id)

const displayStatus = computed<OrderStatus>(() => currentStatus.value ?? props.order.status)
const displayHistory = computed(() => props.order.status_history ?? [])

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long' }).format(new Date(date))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Pedido · {{ formatDate(order.created_at) }}</p>
                    <h1 class="text-xl font-semibold font-mono text-gray-900">
                        #{{ order.id.slice(0, 8).toUpperCase() }}
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <StatusBadge :status="displayStatus" />
                    <a :href="route('orders.invoice', order.id)" target="_blank"
                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg px-3 py-1.5">
                        Baixar nota
                    </a>
                </div>
            </div>

            <!-- Real-time update banner -->
            <div v-if="currentStatus"
                class="mb-6 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
                Status atualizado para <strong>{{ lastLabel ?? currentStatus }}</strong>
                <span v-if="lastNote"> — {{ lastNote }}</span>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Main column -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Timeline -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <h2 class="font-medium text-gray-900 mb-5">Acompanhamento</h2>
                        <StatusTimeline :current-status="displayStatus" :history="displayHistory" />
                    </div>

                    <!-- Items table -->
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h2 class="font-medium text-gray-900">Itens do pedido</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="item in order.items" :key="item.id"
                                class="flex items-center gap-4 px-5 py-4 text-sm">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ item.variation_id }}</p>
                                    <p class="text-gray-500 text-xs mt-0.5">{{ item.size }} · {{ item.color }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-gray-700">{{ item.quantity }}× {{ formatCurrency(item.unit_price) }}</p>
                                    <p v-if="parseFloat(item.discount) > 0" class="text-green-600 text-xs">
                                        −{{ formatCurrency(item.discount) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
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

                    <!-- Shipping address -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 text-sm">
                        <h2 class="font-medium text-gray-900 mb-3">Entrega</h2>
                        <p class="text-gray-600 leading-relaxed">
                            {{ order.shipping_address.street }}, {{ order.shipping_address.number }}<br />
                            {{ order.shipping_address.district }} — {{ order.shipping_address.city }}/{{ order.shipping_address.state }}<br />
                            CEP {{ order.shipping_address.zip }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

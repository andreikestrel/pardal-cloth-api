<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import type { Order } from '@/types'

defineProps<{ orders: { data: Order[] } }>()

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(new Date(date))
}

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-10">
            <PageHeader title="Meus pedidos" />

            <div v-if="!orders.data.length" class="text-center py-16">
                <p class="text-gray-500 text-sm">Você ainda não fez nenhum pedido.</p>
                <Link :href="route('shop.index')"
                    class="inline-block mt-4 text-sm font-medium text-gray-700 hover:underline">
                    Ir para a loja →
                </Link>
            </div>

            <div v-else class="space-y-3">
                <Link
                    v-for="order in orders.data"
                    :key="order.id"
                    :href="route('orders.show', order.id)"
                    class="flex items-center justify-between gap-4 bg-white border border-gray-200 rounded-2xl p-5
                           hover:border-gray-400 transition-colors flex-wrap"
                >
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="font-mono text-sm font-medium text-gray-800">
                                #{{ order.id.slice(0, 8).toUpperCase() }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <StatusBadge :status="order.status" />
                    </div>

                    <div class="flex items-center gap-6 text-sm">
                        <span class="text-gray-500">
                            {{ order.items?.length ?? 0 }} {{ (order.items?.length ?? 0) === 1 ? 'item' : 'itens' }}
                        </span>
                        <span class="font-semibold text-gray-900">{{ formatCurrency(order.total) }}</span>
                        <span class="text-gray-400">→</span>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

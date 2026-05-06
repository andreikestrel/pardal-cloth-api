<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import type { Order, OrderStatus } from '@/types'

defineProps<{ orders: Order[] }>()

const headers = ['Pedido', 'Origem', 'Cliente', 'Total', 'Status', 'Data', 'Ações']

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(new Date(date))
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Pedidos" />

        <DataTable :headers="headers">
            <tr v-for="order in orders" :key="order.id"
                class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm font-mono font-medium text-gray-900">
                    #{{ order.id.slice(0, 8).toUpperCase() }}
                </td>
                <td class="px-5 py-3">
                    <span :class="['text-xs font-medium px-2 py-0.5 rounded-full',
                        (order as any).source === 'pdv'
                            ? 'bg-amber-100 text-amber-700'
                            : 'bg-blue-100 text-blue-700']">
                        {{ (order as any).source === 'pdv' ? 'PDV' : 'Catálogo' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-600">
                    {{ (order as any).user?.name ?? (order as any).pdv_customer_name ?? 'Balcão' }}
                </td>
                <td class="px-5 py-3 text-sm text-gray-700">{{ formatCurrency(order.total) }}</td>
                <td class="px-5 py-3">
                    <StatusBadge :status="order.status as OrderStatus" />
                </td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                <td class="px-5 py-3 text-sm">
                    <Link :href="route('admin.orders.show', order.id)"
                        class="text-gray-500 hover:text-gray-800">Ver</Link>
                </td>
            </tr>
            <tr v-if="!orders.length">
                <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhum pedido encontrado.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

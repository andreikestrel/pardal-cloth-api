<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import KpiCard from '@/Components/Admin/KpiCard.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import type { Order, OrderStatus, ProductVariation } from '@/types'

interface Kpis {
    orders_today: number
    revenue_today: string
    pending_orders: number
    low_stock: number
}

defineProps<{
    kpis: Kpis
    recentOrders: (Order & { user: { name: string } })[]
    lowStock: (ProductVariation & { product: { name: string } })[]
}>()

function formatCurrency(value: string | number) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(
        typeof value === 'string' ? parseFloat(value) : value
    )
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(new Date(date))
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Dashboard" />

        <!-- KPI cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <KpiCard label="Pedidos hoje" :value="kpis.orders_today" />
            <KpiCard label="Receita hoje" :value="formatCurrency(kpis.revenue_today)" description="Apenas entregues" />
            <KpiCard label="Aguardando" :value="kpis.pending_orders" />
            <KpiCard label="Baixo estoque" :value="kpis.low_stock" :highlight="kpis.low_stock > 0" />
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Recent orders -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-medium text-gray-900">Pedidos recentes</h2>
                    <Link :href="route('admin.orders.index')" class="text-xs text-gray-400 hover:text-gray-700">
                        Ver todos →
                    </Link>
                </div>
                <div class="divide-y divide-gray-100">
                    <Link
                        v-for="order in recentOrders"
                        :key="order.id"
                        :href="route('admin.orders.show', order.id)"
                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-mono text-gray-800">#{{ order.id.slice(0, 8).toUpperCase() }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ order.user?.name }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <StatusBadge :status="order.status as OrderStatus" />
                            <span class="text-sm font-medium text-gray-700">{{ formatCurrency(order.total) }}</span>
                        </div>
                    </Link>
                    <p v-if="!recentOrders.length" class="px-5 py-6 text-center text-sm text-gray-400">
                        Nenhum pedido ainda.
                    </p>
                </div>
            </div>

            <!-- Low stock alerts -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-medium text-gray-900">Baixo estoque</h2>
                    <Link :href="route('admin.stock.index')" class="text-xs text-gray-400 hover:text-gray-700">
                        Ver estoque →
                    </Link>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="v in lowStock" :key="v.id"
                        class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ v.product?.name }}</p>
                            <p class="text-xs text-gray-400">{{ v.size }} · {{ v.color }}</p>
                        </div>
                        <span class="text-sm font-semibold text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1 shrink-0">
                            {{ v.stock }} un.
                        </span>
                    </div>
                    <p v-if="!lowStock.length" class="px-5 py-6 text-center text-sm text-gray-400">
                        Estoque normalizado ✓
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

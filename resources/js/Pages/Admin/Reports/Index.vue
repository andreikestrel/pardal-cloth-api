<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

interface RevenueByDay {
    date: string
    total: string
    orders: number
}

interface TopProduct {
    name: string
    qty: number
    revenue: string
}

interface ReportData {
    period_start: string
    period_end:   string
    total_revenue: string
    total_orders:  number
    avg_ticket:    string
    revenue_by_day: RevenueByDay[]
    top_products:   TopProduct[]
}

defineProps<{ report: ReportData }>()

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(new Date(date))
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Relatórios">
            <template #action>
                <span class="text-sm text-gray-400">
                    {{ formatDate(report.period_start) }} — {{ formatDate(report.period_end) }}
                </span>
            </template>
        </PageHeader>

        <!-- KPI row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-xs text-gray-400 mb-1">Receita total</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(report.total_revenue) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-xs text-gray-400 mb-1">Pedidos</p>
                <p class="text-2xl font-semibold text-gray-900">{{ report.total_orders }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-xs text-gray-400 mb-1">Ticket médio</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(report.avg_ticket) }}</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Revenue by day -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-medium text-gray-900">Receita por dia</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="row in report.revenue_by_day" :key="row.date"
                        class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-gray-500">{{ formatDate(row.date) }}</span>
                        <div class="flex items-center gap-4">
                            <span class="text-gray-400 text-xs">{{ row.orders }} pedidos</span>
                            <span class="font-medium text-gray-800">{{ formatCurrency(row.total) }}</span>
                        </div>
                    </div>
                    <p v-if="!report.revenue_by_day.length"
                        class="px-5 py-6 text-center text-sm text-gray-400">
                        Nenhum dado disponível.
                    </p>
                </div>
            </div>

            <!-- Top products -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-medium text-gray-900">Produtos mais vendidos</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(p, i) in report.top_products" :key="p.name"
                        class="flex items-center gap-3 px-5 py-3 text-sm">
                        <span class="w-5 text-gray-400 text-xs font-bold shrink-0">{{ i + 1 }}.</span>
                        <span class="flex-1 text-gray-800 font-medium truncate">{{ p.name }}</span>
                        <span class="text-gray-400 text-xs">{{ p.qty }} un.</span>
                        <span class="font-medium text-gray-700">{{ formatCurrency(p.revenue) }}</span>
                    </div>
                    <p v-if="!report.top_products.length"
                        class="px-5 py-6 text-center text-sm text-gray-400">
                        Nenhum dado disponível.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

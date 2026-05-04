<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import type { Coupon } from '@/types'

defineProps<{ coupons: Coupon[] }>()

const headers = ['Código', 'Desconto', 'Usos', 'Validade', 'Ativo', 'Ações']

function formatDiscount(c: Coupon) {
    return c.discount_type === 'percentage'
        ? `${c.discount_value}%`
        : `R$ ${c.discount_value}`
}

function formatDate(date: string | null) {
    if (!date) return '—'
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(new Date(date))
}

function destroy(id: string) {
    if (!confirm('Excluir este cupom?')) return
    router.delete(route('admin.coupons.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Cupons">
            <template #action>
                <Link :href="route('admin.coupons.create')"
                    class="text-sm font-medium text-white px-4 py-2 rounded-xl"
                    style="background-color: var(--color-primary)">
                    Novo cupom
                </Link>
            </template>
        </PageHeader>

        <DataTable :headers="headers">
            <tr v-for="c in coupons" :key="c.id"
                class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm font-mono font-medium text-gray-900 tracking-widest">
                    {{ c.code }}
                </td>
                <td class="px-5 py-3 text-sm text-gray-700">{{ formatDiscount(c) }}</td>
                <td class="px-5 py-3 text-sm text-gray-500">
                    {{ c.used_count }}{{ c.max_uses ? ` / ${c.max_uses}` : '' }}
                </td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ formatDate(c.expires_at) }}</td>
                <td class="px-5 py-3">
                    <span :class="['text-xs font-medium px-2 py-0.5 rounded-full',
                        c.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        {{ c.active ? 'Sim' : 'Não' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm">
                    <div class="flex gap-3">
                        <Link :href="route('admin.coupons.edit', c.id)"
                            class="text-gray-500 hover:text-gray-800">Editar</Link>
                        <button @click="destroy(c.id)"
                            class="text-red-400 hover:text-red-600">Excluir</button>
                    </div>
                </td>
            </tr>
            <tr v-if="!coupons.length">
                <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhum cupom cadastrado.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import type { Promotion } from '@/types'

defineProps<{ promotions: Promotion[] }>()

const headers = ['Nome', 'Gatilho', 'Desconto', 'Prioridade', 'Ativo', 'Ações']

const triggerLabels: Record<string, string> = {
    min_qty: 'Qtd. mínima',
    min_amount: 'Valor mínimo',
    product: 'Produto',
    category: 'Categoria',
}

const discountLabels: Record<string, string> = {
    percentage: '%',
    fixed: 'R$',
    free_item: 'Item grátis',
    free_shipping: 'Frete grátis',
}

function formatDiscount(p: Promotion) {
    if (p.discount_type === 'free_item' || p.discount_type === 'free_shipping') {
        return discountLabels[p.discount_type]
    }
    const prefix = p.discount_type === 'fixed' ? 'R$ ' : ''
    const suffix = p.discount_type === 'percentage' ? '%' : ''
    return `${prefix}${p.discount_value}${suffix}`
}

function destroy(id: string) {
    if (!confirm('Excluir esta promoção?')) return
    router.delete(route('admin.promotions.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Promoções">
            <template #action>
                <Link :href="route('admin.promotions.create')"
                    class="text-sm font-medium text-white px-4 py-2 rounded-xl"
                    style="background-color: var(--color-primary)">
                    Nova promoção
                </Link>
            </template>
        </PageHeader>

        <DataTable :headers="headers">
            <tr v-for="p in promotions" :key="p.id"
                class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ p.name }}</td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ triggerLabels[p.trigger_type] }}</td>
                <td class="px-5 py-3 text-sm text-gray-700">{{ formatDiscount(p) }}</td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ p.priority }}</td>
                <td class="px-5 py-3">
                    <span :class="['text-xs font-medium px-2 py-0.5 rounded-full',
                        p.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        {{ p.active ? 'Sim' : 'Não' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm">
                    <div class="flex gap-3">
                        <Link :href="route('admin.promotions.edit', p.id)"
                            class="text-gray-500 hover:text-gray-800">Editar</Link>
                        <button @click="destroy(p.id)"
                            class="text-red-400 hover:text-red-600">Excluir</button>
                    </div>
                </td>
            </tr>
            <tr v-if="!promotions.length">
                <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhuma promoção cadastrada.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

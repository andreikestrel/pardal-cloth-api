<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import type { Product } from '@/types'

defineProps<{ products: Product[] }>()

const headers = ['Produto', 'Categoria', 'Preço base', 'Ativo', 'Ações']

function formatCurrency(value: string) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

function destroy(id: string) {
    if (!confirm('Excluir este produto?')) return
    router.delete(route('admin.products.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Produtos">
            <template #action>
                <Link :href="route('admin.products.create')"
                    class="text-sm font-medium text-white px-4 py-2 rounded-xl"
                    style="background-color: var(--color-primary)">
                    Novo produto
                </Link>
            </template>
        </PageHeader>

        <DataTable :headers="headers">
            <tr v-for="product in products" :key="product.id"
                class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ product.name }}</td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ product.category?.name ?? '—' }}</td>
                <td class="px-5 py-3 text-sm text-gray-700">{{ formatCurrency(product.base_price) }}</td>
                <td class="px-5 py-3">
                    <span :class="['text-xs font-medium px-2 py-0.5 rounded-full',
                        product.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        {{ product.active ? 'Sim' : 'Não' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm">
                    <div class="flex gap-3">
                        <Link :href="route('admin.products.edit', product.id)"
                            class="text-gray-500 hover:text-gray-800">Editar</Link>
                        <button @click="destroy(product.id)"
                            class="text-red-400 hover:text-red-600">Excluir</button>
                    </div>
                </td>
            </tr>
            <tr v-if="!products.length">
                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhum produto cadastrado.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import StockAdjustModal from '@/Components/Admin/StockAdjustModal.vue'
import type { ProductVariation, Product } from '@/types'

type VariationWithProduct = ProductVariation & { product: Pick<Product, 'name'> }

defineProps<{ variations: VariationWithProduct[] }>()

const headers = ['Produto', 'Tamanho', 'Cor', 'SKU', 'Estoque', 'Mínimo', 'Ação']

const selected = ref<VariationWithProduct | null>(null)

function openAdjust(v: VariationWithProduct) { selected.value = v }
function closeAdjust() { selected.value = null }

function isLow(v: VariationWithProduct) {
    return v.stock <= v.min_stock
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Estoque" />

        <DataTable :headers="headers">
            <tr v-for="v in variations" :key="v.id"
                :class="['border-t border-gray-100 transition-colors',
                    isLow(v) ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-gray-50']">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ v.product.name }}</td>
                <td class="px-5 py-3 text-sm text-gray-600">{{ v.size }}</td>
                <td class="px-5 py-3 text-sm text-gray-600">{{ v.color }}</td>
                <td class="px-5 py-3 text-xs font-mono text-gray-500">{{ v.sku }}</td>
                <td class="px-5 py-3">
                    <span :class="['text-sm font-semibold px-2.5 py-1 rounded-lg',
                        isLow(v) ? 'text-amber-700 bg-amber-100' : 'text-gray-700 bg-gray-100']">
                        {{ v.stock }} un.
                    </span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ v.min_stock }}</td>
                <td class="px-5 py-3 text-sm">
                    <button @click="openAdjust(v)"
                        class="text-xs font-medium text-white px-3 py-1.5 rounded-lg transition-opacity hover:opacity-90"
                        style="background-color: var(--color-primary)">
                        Ajustar
                    </button>
                </td>
            </tr>
            <tr v-if="!variations.length">
                <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhuma variação cadastrada.
                </td>
            </tr>
        </DataTable>

        <StockAdjustModal v-if="selected" :variation="selected" @close="closeAdjust" />
    </AdminLayout>
</template>

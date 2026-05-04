<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import type { ProductVariation, Product } from '@/types'

type VariationWithProduct = ProductVariation & { product: Pick<Product, 'name'> }

const props = defineProps<{ variations: VariationWithProduct[] }>()

const headers = ['Produto', 'Tamanho', 'Cor', 'SKU', 'Estoque', 'Mínimo', 'Ação']

// Tracks which row is in edit mode (variation id → form instance)
const editing = ref<string | null>(null)

const adjustForm = useForm({ quantity: 0, reason: '' })

function startEdit(v: VariationWithProduct) {
    editing.value = v.id
    adjustForm.quantity = 0
    adjustForm.reason = ''
}

function cancelEdit() {
    editing.value = null
}

function submitAdjust(id: string) {
    adjustForm.post(route('admin.stock.adjust', id), {
        preserveScroll: true,
        onSuccess: () => { editing.value = null },
    })
}

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
                    <!-- Inline adjust form -->
                    <div v-if="editing === v.id" class="flex items-center gap-2">
                        <input v-model.number="adjustForm.quantity" type="number"
                            placeholder="±qtd"
                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        <input v-model="adjustForm.reason" type="text"
                            placeholder="Motivo"
                            class="w-32 border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        <button @click="submitAdjust(v.id)"
                            class="text-xs text-white px-2.5 py-1 rounded-lg"
                            style="background-color: var(--color-primary)">
                            OK
                        </button>
                        <button @click="cancelEdit"
                            class="text-xs text-gray-500 hover:text-gray-700">
                            ✕
                        </button>
                    </div>
                    <button v-else @click="startEdit(v)"
                        class="text-gray-500 hover:text-gray-800 text-sm">
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
    </AdminLayout>
</template>

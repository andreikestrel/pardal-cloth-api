<script setup lang="ts">
interface Variation {
    size: string
    color: string
    price: string
    stock: number
    min_stock: number
    sku: string
}

const variations = defineModel<Variation[]>({ required: true })

function addRow() {
    variations.value.push({ size: '', color: '', price: '', stock: 0, min_stock: 5, sku: '' })
}

function removeRow(index: number) {
    variations.value.splice(index, 1)
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-medium text-gray-700">Variações <span class="text-red-500">*</span></h3>
            <button type="button" @click="addRow"
                class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 font-medium">
                + Adicionar variação
            </button>
        </div>

        <p v-if="!variations.length" class="text-sm text-gray-400 py-4 text-center border border-dashed border-gray-300 rounded-xl">
            Adicione ao menos uma variação (tamanho + cor + preço).
        </p>

        <div v-else class="space-y-3">
            <div v-for="(v, i) in variations" :key="i"
                class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end p-3 border border-gray-200 rounded-xl bg-gray-50">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tamanho</label>
                    <input v-model="v.size" type="text" placeholder="P, M, G…"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Cor</label>
                    <input v-model="v.color" type="text" placeholder="Preto, Azul…"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Preço (R$)</label>
                    <input v-model="v.price" type="number" step="0.01" min="0" placeholder="99.90"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estoque</label>
                    <input v-model.number="v.stock" type="number" min="0"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Mín. estoque</label>
                    <input v-model.number="v.min_stock" type="number" min="0"
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" />
                </div>
                <div class="flex items-end">
                    <button type="button" @click="removeRow(i)"
                        class="w-full py-1.5 text-xs text-red-600 hover:text-red-800 border border-red-200 hover:border-red-400 rounded-lg transition-colors">
                        Remover
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

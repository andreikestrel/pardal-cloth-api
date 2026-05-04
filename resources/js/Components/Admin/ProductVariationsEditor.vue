<script setup lang="ts">
import { ref } from 'vue'
import BarcodeScanner from '@/Components/UI/BarcodeScanner.vue'

interface Variation {
    size: string
    color: string
    price: string
    stock: number
    min_stock: number
    sku: string
    barcode: string
}

const variations = defineModel<Variation[]>({ required: true })

// Index of the variation row currently waiting for a scan result
const scanningIndex = ref<number | null>(null)

function addRow() {
    variations.value.push({ size: '', color: '', price: '', stock: 0, min_stock: 5, sku: '', barcode: '' })
}

function removeRow(index: number) {
    variations.value.splice(index, 1)
}

function openScanner(index: number) {
    scanningIndex.value = index
}

function onScanned(code: string) {
    if (scanningIndex.value !== null) {
        variations.value[scanningIndex.value].barcode = code
    }
    scanningIndex.value = null
}
</script>

<template>
    <div>
        <!-- Scanner modal -->
        <BarcodeScanner
            v-if="scanningIndex !== null"
            @scanned="onScanned"
            @close="scanningIndex = null"
        />

        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-medium text-gray-700">Variações <span class="text-red-500">*</span></h3>
            <button type="button" @click="addRow"
                class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 font-medium">
                + Adicionar variação
            </button>
        </div>

        <p v-if="!variations.length"
            class="text-sm text-gray-400 py-4 text-center border border-dashed border-gray-300 rounded-xl">
            Adicione ao menos uma variação (tamanho + cor + preço).
        </p>

        <div v-else class="space-y-3">
            <div v-for="(v, i) in variations" :key="i"
                class="p-3 border border-gray-200 rounded-xl bg-gray-50 space-y-2">

                <!-- Row 1: size, color, price, stock, min_stock -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
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
                </div>

                <!-- Row 2: SKU, barcode, remove -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">SKU</label>
                        <input v-model="v.sku" type="text" placeholder="CAM-BAS-P-BCO"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm font-mono" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Código de barras</label>
                        <div class="flex gap-1.5">
                            <input v-model="v.barcode" type="text" placeholder="7891234567890"
                                class="flex-1 min-w-0 border border-gray-300 rounded-lg px-2 py-1.5 text-sm font-mono" />
                            <button type="button" @click="openScanner(i)"
                                title="Escanear código"
                                class="shrink-0 px-2.5 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                <!-- barcode icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9V4.5A1.5 1.5 0 0 1 4.5 3H9M3 15v4.5A1.5 1.5 0 0 0 4.5 21H9M15 3h4.5A1.5 1.5 0 0 1 21 4.5V9M15 21h4.5A1.5 1.5 0 0 0 21 19.5V15" />
                                    <path stroke-linecap="round" d="M7 8v8M10 8v8M13 8v8M16 8v8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="removeRow(i)"
                            class="w-full py-1.5 text-xs text-red-600 hover:text-red-800 border border-red-200 hover:border-red-400 rounded-lg transition-colors">
                            Remover variação
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

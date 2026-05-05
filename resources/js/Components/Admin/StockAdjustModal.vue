<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import type { ProductVariation, Product } from '@/types'

type VariationWithProduct = ProductVariation & { product: Pick<Product, 'name'> }

const props = defineProps<{ variation: VariationWithProduct }>()
const emit  = defineEmits<{ close: [] }>()

type MovementType = 'in' | 'out'

const movementType = ref<MovementType>('in')

const REASONS: Record<MovementType, string[]> = {
    in: [
        'Entrada de mercadoria',
        'Devolução de cliente',
        'Ajuste de inventário',
        'Transferência recebida',
        'Inventário físico',
        'Outro',
    ],
    out: [
        'Venda manual / PDV',
        'Avaria / Defeito',
        'Perda / Furto',
        'Brinde / Amostra',
        'Ajuste de inventário',
        'Transferência enviada',
        'Inventário físico',
        'Outro',
    ],
}

const reasons = computed(() => REASONS[movementType.value])

const form = useForm({
    variation_id: props.variation.id,
    quantity:     1,
    reason:       REASONS.in[0],
    note:         '',
})

// When type changes, reset reason to first option of new type
function setType(t: MovementType) {
    movementType.value = t
    form.reason = REASONS[t][0]
}

const signedQuantity = computed(() =>
    movementType.value === 'out' ? -Math.abs(form.quantity) : Math.abs(form.quantity)
)

const finalReason = computed(() =>
    form.reason === 'Outro' && form.note ? `Outro: ${form.note}` : form.reason
)

function submit() {
    const payload = useForm({
        variation_id: props.variation.id,
        quantity:     signedQuantity.value,
        reason:       finalReason.value,
    })

    payload.post(route('admin.stock.adjust'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    })
}

function increment() { if (form.quantity < 9999) form.quantity++ }
function decrement() { if (form.quantity > 1)    form.quantity-- }
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <!-- Header -->
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h2 class="font-semibold text-gray-900">Ajustar estoque</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ variation.product.name }} · {{ variation.size }} · {{ variation.color }}
                    </p>
                </div>
                <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none mt-0.5">
                    &times;
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">
                <!-- Current stock -->
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                    <span class="text-sm text-gray-500">Estoque atual</span>
                    <span :class="['text-lg font-bold',
                        variation.stock <= variation.min_stock ? 'text-amber-600' : 'text-gray-900']">
                        {{ variation.stock }} un.
                    </span>
                </div>

                <!-- Entrada / Baixa toggle -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de movimentação</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="setType('in')"
                            :class="['py-2.5 rounded-xl text-sm font-medium border-2 transition-colors',
                                movementType === 'in'
                                    ? 'bg-green-50 border-green-500 text-green-700'
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300']">
                            ↑ Entrada
                        </button>
                        <button type="button" @click="setType('out')"
                            :class="['py-2.5 rounded-xl text-sm font-medium border-2 transition-colors',
                                movementType === 'out'
                                    ? 'bg-red-50 border-red-400 text-red-700'
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300']">
                            ↓ Baixa
                        </button>
                    </div>
                </div>

                <!-- Quantity stepper -->
                <FormField label="Quantidade" :error="form.errors.quantity">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="decrement"
                            class="w-10 h-10 rounded-xl border border-gray-300 text-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors shrink-0">
                            −
                        </button>
                        <input v-model.number="form.quantity" type="number" min="1" max="9999"
                            class="flex-1 text-center border border-gray-300 rounded-xl px-3 py-2 text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        <button type="button" @click="increment"
                            class="w-10 h-10 rounded-xl border border-gray-300 text-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors shrink-0">
                            +
                        </button>
                    </div>

                    <!-- Preview -->
                    <p class="mt-2 text-xs text-center"
                        :class="movementType === 'in' ? 'text-green-600' : 'text-red-600'">
                        {{ movementType === 'in' ? '+' : '−' }}{{ form.quantity }} un.
                        → novo estoque: <strong>{{ variation.stock + signedQuantity }}</strong>
                    </p>
                </FormField>

                <!-- Reason -->
                <FormField label="Motivo" :error="form.errors.reason">
                    <select v-model="form.reason"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <option v-for="r in reasons" :key="r" :value="r">{{ r }}</option>
                    </select>
                </FormField>

                <!-- Free text when Outro -->
                <FormField v-if="form.reason === 'Outro'" label="Descreva o motivo">
                    <input v-model="form.note" type="text" placeholder="Ex: devolução fornecedor"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                </FormField>
            </div>

            <!-- Footer -->
            <div class="px-6 pb-5 flex gap-3 justify-end border-t border-gray-100 pt-4">
                <button type="button" @click="emit('close')"
                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                    Cancelar
                </button>
                <PrimaryButton @click="submit" :loading="form.processing">
                    Confirmar ajuste
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

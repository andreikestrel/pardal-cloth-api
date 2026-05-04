<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import type { Promotion, Category, Product, TriggerType, DiscountType } from '@/types'

const props = defineProps<{
    promotion?: Promotion
    categories: Category[]
    products: Product[]
    submitRoute: string
    method?: 'post' | 'put'
}>()

const form = useForm({
    name:           props.promotion?.name ?? '',
    trigger_type:   props.promotion?.trigger_type ?? 'min_qty' as TriggerType,
    discount_type:  props.promotion?.discount_type ?? 'percentage' as DiscountType,
    discount_value: props.promotion?.discount_value ?? '',
    min_items:      props.promotion?.min_items ?? null as number | null,
    min_amount:     props.promotion?.min_amount ?? null as string | null,
    target_id:      props.promotion?.target_id ?? null as string | null,
    active:         props.promotion?.active ?? true,
    priority:       props.promotion?.priority ?? 0,
    starts_at:      props.promotion?.starts_at ?? null as string | null,
    ends_at:        props.promotion?.ends_at ?? null as string | null,
})

const needsMinItems  = computed(() => form.trigger_type === 'min_qty')
const needsMinAmount = computed(() => form.trigger_type === 'min_amount')
const needsTarget    = computed(() => form.trigger_type === 'product' || form.trigger_type === 'category')
const needsFreeItem  = computed(() => form.discount_type === 'free_item')

function submit() {
    const opts = { forceFormData: false }
    props.method === 'put'
        ? form.put(props.submitRoute, opts)
        : form.post(props.submitRoute, opts)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <FormField label="Nome" :error="form.errors.name" required class="sm:col-span-2">
                <input v-model="form.name" type="text" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Gatilho" :error="form.errors.trigger_type" required>
                <select v-model="form.trigger_type"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="min_qty">Quantidade mínima</option>
                    <option value="min_amount">Valor mínimo</option>
                    <option value="product">Produto específico</option>
                    <option value="category">Categoria específica</option>
                </select>
            </FormField>

            <FormField label="Tipo de desconto" :error="form.errors.discount_type" required>
                <select v-model="form.discount_type"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="percentage">Percentual (%)</option>
                    <option value="fixed">Fixo (R$)</option>
                    <option value="free_item">Item grátis</option>
                    <option value="free_shipping">Frete grátis</option>
                </select>
            </FormField>

            <FormField v-if="!needsFreeItem" label="Valor do desconto" :error="form.errors.discount_value" required>
                <input v-model="form.discount_value" type="number" min="0" step="0.01" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField v-if="needsMinItems" label="Qtd. mínima de itens" :error="form.errors.min_items">
                <input v-model.number="form.min_items" type="number" min="1"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField v-if="needsMinAmount" label="Valor mínimo (R$)" :error="form.errors.min_amount">
                <input v-model="form.min_amount" type="number" min="0" step="0.01"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField v-if="form.trigger_type === 'category'" label="Categoria" :error="form.errors.target_id">
                <select v-model="form.target_id"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="">Selecione...</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </FormField>

            <FormField v-if="needsTarget && form.trigger_type === 'product'" label="Produto" :error="form.errors.target_id">
                <select v-model="form.target_id"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="">Selecione...</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </FormField>

            <FormField label="Prioridade" :error="form.errors.priority">
                <input v-model.number="form.priority" type="number" min="0"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Início" :error="form.errors.starts_at">
                <input v-model="form.starts_at" type="datetime-local"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Fim" :error="form.errors.ends_at">
                <input v-model="form.ends_at" type="datetime-local"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input v-model="form.active" type="checkbox" class="rounded" />
            Promoção ativa
        </label>

        <div class="flex justify-end gap-3 pt-2">
            <slot name="cancel" />
            <PrimaryButton type="submit" :loading="form.processing">
                {{ promotion ? 'Salvar' : 'Criar promoção' }}
            </PrimaryButton>
        </div>
    </form>
</template>

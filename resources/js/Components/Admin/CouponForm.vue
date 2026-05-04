<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import type { Coupon } from '@/types'

const props = defineProps<{
    coupon?: Coupon
    submitRoute: string
    method?: 'post' | 'put'
}>()

const form = useForm({
    code:             props.coupon?.code ?? '',
    discount_type:    props.coupon?.discount_type ?? 'percentage' as 'percentage' | 'fixed',
    discount_value:   props.coupon?.discount_value ?? '',
    min_order_amount: props.coupon?.min_order_amount ?? null as string | null,
    max_uses:         props.coupon?.max_uses ?? null as number | null,
    uses_per_user:    props.coupon?.uses_per_user ?? null as number | null,
    stackable:        props.coupon?.stackable ?? false,
    active:           props.coupon?.active ?? true,
    expires_at:       props.coupon?.expires_at ?? null as string | null,
})

function submit() {
    props.method === 'put'
        ? form.put(props.submitRoute)
        : form.post(props.submitRoute)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <FormField label="Código" :error="form.errors.code" required class="sm:col-span-2">
                <input v-model="form.code" type="text" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-gray-400"
                    placeholder="ex: PROMO10" />
            </FormField>

            <FormField label="Tipo de desconto" :error="form.errors.discount_type" required>
                <select v-model="form.discount_type"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="percentage">Percentual (%)</option>
                    <option value="fixed">Fixo (R$)</option>
                </select>
            </FormField>

            <FormField label="Valor do desconto" :error="form.errors.discount_value" required>
                <input v-model="form.discount_value" type="number" min="0" step="0.01" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Pedido mínimo (R$)" :error="form.errors.min_order_amount">
                <input v-model="form.min_order_amount" type="number" min="0" step="0.01"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Máx. usos totais" :error="form.errors.max_uses">
                <input v-model.number="form.max_uses" type="number" min="1"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Usos por usuário" :error="form.errors.uses_per_user">
                <input v-model.number="form.uses_per_user" type="number" min="1"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Validade" :error="form.errors.expires_at" class="sm:col-span-2">
                <input v-model="form.expires_at" type="datetime-local"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>
        </div>

        <div class="flex flex-col gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input v-model="form.stackable" type="checkbox" class="rounded" />
                Acumulável com promoções
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input v-model="form.active" type="checkbox" class="rounded" />
                Cupom ativo
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <slot name="cancel" />
            <PrimaryButton type="submit" :loading="form.processing">
                {{ coupon ? 'Salvar' : 'Criar cupom' }}
            </PrimaryButton>
        </div>
    </form>
</template>

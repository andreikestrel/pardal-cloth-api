<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import ProductVariationsEditor from '@/Components/Admin/ProductVariationsEditor.vue'
import type { Category, Product } from '@/types'

interface Variation {
    size: string; color: string; price: string; stock: number; min_stock: number; sku: string
}

const props = defineProps<{
    categories: Category[]
    product?: Product
    submitRoute: string
    method?: 'post' | 'put'
}>()

const form = useForm({
    name:        props.product?.name ?? '',
    slug:        props.product?.slug ?? '',
    description: props.product?.description ?? '',
    category_id: props.product?.category_id ?? '',
    image:       null as File | null,
    variations:  (props.product?.variations ?? []).map((v) => ({
        size: v.size, color: v.color, price: v.price, stock: v.stock, min_stock: v.min_stock, sku: v.sku,
    })) as Variation[],
})

function autoSlug() {
    if (!props.product) {
        form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
    }
}

function onImageChange(e: Event) {
    const target = e.target as HTMLInputElement
    form.image = target.files?.[0] ?? null
}

function submit() {
    const options = { forceFormData: true }
    if (props.method === 'put') {
        form.post(props.submitRoute, { ...options, _method: 'PUT' } as any)
    } else {
        form.post(props.submitRoute, options)
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-5">
            <FormField label="Nome" :error="form.errors.name" required>
                <input v-model="form.name" @input="autoSlug" type="text" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Slug" :error="form.errors.slug" required>
                <input v-model="form.slug" type="text" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Categoria" :error="form.errors.category_id" required>
                <select v-model="form.category_id" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="">Selecione…</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </FormField>

            <FormField label="Imagem da capa" :error="form.errors.image">
                <input type="file" accept="image/*" @change="onImageChange"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" />
            </FormField>
        </div>

        <FormField label="Descrição" :error="form.errors.description">
            <textarea v-model="form.description" rows="3"
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
        </FormField>

        <ProductVariationsEditor v-model="form.variations" />

        <div class="flex justify-end gap-3 pt-2">
            <slot name="cancel" />
            <PrimaryButton type="submit" :loading="form.processing">
                {{ product ? 'Salvar alterações' : 'Criar produto' }}
            </PrimaryButton>
        </div>
    </form>
</template>

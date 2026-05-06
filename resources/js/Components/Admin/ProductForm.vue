<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import ProductVariationsEditor from '@/Components/Admin/ProductVariationsEditor.vue'
import TagPicker from '@/Components/Admin/TagPicker.vue'
import type { Category, Product } from '@/types'

interface Tag { id: number; name: string; color: string }

interface Variation {
    size: string; color: string; price: string; stock: number; min_stock: number; sku: string
}

const props = defineProps<{
    categories: Category[]
    allTags: Tag[]
    product?: Product
    submitRoute: string
    method?: 'post' | 'put'
}>()

const form = useForm({
    name:        props.product?.name ?? '',
    slug:        props.product?.slug ?? '',
    description: props.product?.description ?? '',
    base_price:  props.product?.base_price ?? '',
    category_id: props.product?.category_id ?? '',
    image:       null as File | null,
    tag_ids:     (Array.isArray(props.product?.tags) ? props.product!.tags : []).map((t: Tag) => t.id) as number[],
    variations:  (Array.isArray(props.product?.variations) ? props.product!.variations : []).map((v) => ({
        size: v.size, color: v.color, price: v.price, stock: v.stock, min_stock: v.min_stock, sku: v.sku,
    })) as Variation[],
})

function autoSlug() {
    if (!props.product) {
        form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
    }
}

// ProductResource exposes the saved cover URL as `cover` — show it as the initial preview
const imagePreview = ref<string | null>((props.product as any)?.cover ?? null)

function onImageChange(e: Event) {
    const target = e.target as HTMLInputElement
    const file = target.files?.[0] ?? null
    form.image = file
    if (file) {
        const reader = new FileReader()
        reader.onload = () => imagePreview.value = reader.result as string
        reader.readAsDataURL(file)
    }
}

function submit() {
    // PHP only parses multipart/form-data on POST — for PUT we spoof via _method form field
    if (props.method === 'put') {
        form.transform((data) => ({ ...data, _method: 'PUT' }))
            .post(props.submitRoute, { forceFormData: true })
    } else {
        form.post(props.submitRoute, { forceFormData: true })
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

            <FormField label="Preço base (R$)" :error="form.errors.base_price" required>
                <input v-model="form.base_price" type="number" min="0" step="0.01" required placeholder="0,00"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                <p class="text-xs text-gray-400 mt-1">Preço exibido no catálogo antes de selecionar variação.</p>
            </FormField>

            <FormField label="Imagem da capa" :error="form.errors.image">
                <div v-if="imagePreview" class="aspect-[3/4] w-32 rounded-xl overflow-hidden bg-gray-100 mb-2 border border-gray-200">
                    <img :src="imagePreview" alt="" class="w-full h-full object-cover" />
                </div>
                <input type="file" accept="image/*" @change="onImageChange"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" />
                <p v-if="product && imagePreview" class="text-xs text-gray-400 mt-1">
                    Selecione um arquivo para substituir a imagem atual.
                </p>
            </FormField>
        </div>

        <FormField label="Descrição" :error="form.errors.description">
            <textarea v-model="form.description" rows="3"
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
        </FormField>

        <ProductVariationsEditor v-model="form.variations" />

        <FormField label="Tags" :error="form.errors.tag_ids">
            <TagPicker v-model="form.tag_ids" :tags="allTags" />
        </FormField>

        <div class="flex justify-end gap-3 pt-2">
            <slot name="cancel" />
            <PrimaryButton type="submit" :loading="form.processing">
                {{ product ? 'Salvar alterações' : 'Criar produto' }}
            </PrimaryButton>
        </div>
    </form>
</template>

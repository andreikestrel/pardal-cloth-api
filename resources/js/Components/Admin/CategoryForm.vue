<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import type { Category } from '@/types'

const props = defineProps<{
    category?: Category
    submitRoute: string
    method?: 'post' | 'put'
}>()

const form = useForm({
    name:  props.category?.name ?? '',
    slug:  props.category?.slug ?? '',
    image: null as File | null,
})

function autoSlug() {
    if (!props.category) {
        form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
    }
}

function onImageChange(e: Event) {
    form.image = (e.target as HTMLInputElement).files?.[0] ?? null
}

function submit() {
    const opts = { forceFormData: true }
    props.method === 'put'
        ? form.post(props.submitRoute, { ...opts, _method: 'PUT' } as any)
        : form.post(props.submitRoute, opts)
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <FormField label="Nome" :error="form.errors.name" required>
                <input v-model="form.name" @input="autoSlug" type="text" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>

            <FormField label="Slug" :error="form.errors.slug" required>
                <input v-model="form.slug" type="text" required
                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
            </FormField>
        </div>

        <FormField label="Imagem" :error="form.errors.image">
            <input type="file" accept="image/*" @change="onImageChange"
                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" />
        </FormField>

        <div class="flex justify-end gap-3 pt-2">
            <slot name="cancel" />
            <PrimaryButton type="submit" :loading="form.processing">
                {{ category ? 'Salvar' : 'Criar categoria' }}
            </PrimaryButton>
        </div>
    </form>
</template>

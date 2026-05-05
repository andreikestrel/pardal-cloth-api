<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

interface Slide {
    id: string
    title: string | null
    subtitle: string | null
    link_url: string | null
    link_label: string | null
    sort_order: number
    active: boolean
    image_url: string | null
}

const props = defineProps<{ slides: Slide[] }>()

const showForm = ref(false)
const editingId = ref<string | null>(null)

const form = useForm({
    title:      '',
    subtitle:   '',
    link_url:   '',
    link_label: '',
    active:     true,
    sort_order: 0,
    image:      null as File | null,
})

function openCreate() {
    editingId.value = null
    form.reset()
    form.active = true
    showForm.value = true
}

function openEdit(slide: Slide) {
    editingId.value = slide.id
    form.title      = slide.title ?? ''
    form.subtitle   = slide.subtitle ?? ''
    form.link_url   = slide.link_url ?? ''
    form.link_label = slide.link_label ?? ''
    form.active     = slide.active
    form.sort_order = slide.sort_order
    form.image      = null
    showForm.value  = true
}

function onImage(e: Event) {
    form.image = (e.target as HTMLInputElement).files?.[0] ?? null
}

function submit() {
    const url = editingId.value
        ? route('admin.catalog.slides.update', editingId.value)
        : route('admin.catalog.slides.store')

    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showForm.value = false
            editingId.value = null
            form.reset()
        },
    })
}

function destroy(slide: Slide) {
    if (!confirm(`Remover slide "${slide.title || 'sem título'}"?`)) return
    router.delete(route('admin.catalog.slides.destroy', slide.id), { preserveScroll: true })
}

function move(slide: Slide, direction: -1 | 1) {
    const ordered = [...props.slides]
    const idx = ordered.findIndex(s => s.id === slide.id)
    const swapWith = idx + direction
    if (swapWith < 0 || swapWith >= ordered.length) return
    ;[ordered[idx], ordered[swapWith]] = [ordered[swapWith], ordered[idx]]
    router.post(
        route('admin.catalog.slides.reorder'),
        { order: ordered.map(s => s.id) },
        { preserveScroll: true },
    )
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Catálogo — Carrossel">
            <template #action>
                <button @click="openCreate"
                    class="text-sm text-white px-4 py-2 rounded-xl font-medium transition-opacity hover:opacity-90"
                    style="background-color: var(--color-primary)">
                    + Novo slide
                </button>
            </template>
        </PageHeader>

        <p class="text-sm text-gray-500 mb-6 max-w-2xl">
            Slides aparecem no topo da página inicial em ordem. Use o link para apontar
            para um produto, cupom ou qualquer URL (ex: <code class="bg-gray-100 px-1 rounded">/store/products/camiseta-basica</code>).
        </p>

        <!-- Form -->
        <div v-if="showForm" class="bg-white border border-gray-200 rounded-2xl p-6 mb-6 max-w-3xl">
            <h2 class="font-medium text-gray-900 mb-5">
                {{ editingId ? 'Editar slide' : 'Novo slide' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <FormField label="Imagem" :error="form.errors.image" :required="!editingId">
                    <input type="file" accept="image/*" @change="onImage"
                        :required="!editingId"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" />
                    <p class="text-xs text-gray-400 mt-1">Recomendado 1920x600. Max 4MB.</p>
                </FormField>

                <div class="grid sm:grid-cols-2 gap-4">
                    <FormField label="Título (opcional)" :error="form.errors.title">
                        <input v-model="form.title" type="text" maxlength="120"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>

                    <FormField label="Subtítulo (opcional)" :error="form.errors.subtitle">
                        <input v-model="form.subtitle" type="text" maxlength="200"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <FormField label="Link (opcional)" :error="form.errors.link_url">
                        <input v-model="form.link_url" type="text" placeholder="/store/products/..."
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>

                    <FormField label="Texto do botão (opcional)" :error="form.errors.link_label">
                        <input v-model="form.link_label" type="text" maxlength="60" placeholder="Ver coleção"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>
                </div>

                <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                    <input v-model="form.active" type="checkbox" class="rounded" />
                    <span>Ativo</span>
                </label>

                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="showForm = false"
                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                        Cancelar
                    </button>
                    <PrimaryButton type="submit" :loading="form.processing">
                        Salvar
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="space-y-3">
            <div v-for="(slide, i) in slides" :key="slide.id"
                class="bg-white border border-gray-200 rounded-2xl p-4 flex items-center gap-4">
                <div class="flex flex-col">
                    <button @click="move(slide, -1)" :disabled="i === 0"
                        class="text-gray-400 hover:text-gray-700 disabled:opacity-20">↑</button>
                    <button @click="move(slide, 1)" :disabled="i === slides.length - 1"
                        class="text-gray-400 hover:text-gray-700 disabled:opacity-20">↓</button>
                </div>

                <div class="w-32 h-20 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                    <img v-if="slide.image_url" :src="slide.image_url" :alt="slide.title ?? ''"
                        class="w-full h-full object-cover" />
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ slide.title || '(sem título)' }}
                    </p>
                    <p v-if="slide.subtitle" class="text-xs text-gray-500 truncate">{{ slide.subtitle }}</p>
                    <p v-if="slide.link_url" class="text-xs text-gray-400 truncate font-mono mt-0.5">
                        → {{ slide.link_url }}
                    </p>
                </div>

                <span :class="['text-xs font-medium px-2 py-1 rounded-lg shrink-0',
                    slide.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                    {{ slide.active ? 'Ativo' : 'Oculto' }}
                </span>

                <div class="flex gap-3 shrink-0">
                    <button @click="openEdit(slide)" class="text-gray-600 hover:text-gray-900 text-sm">Editar</button>
                    <button @click="destroy(slide)" class="text-red-600 hover:text-red-700 text-sm">Remover</button>
                </div>
            </div>

            <p v-if="!slides.length" class="text-center text-sm text-gray-400 py-12 bg-white border border-gray-200 rounded-2xl">
                Nenhum slide cadastrado. Clique em "+ Novo slide" para começar.
            </p>
        </div>
    </AdminLayout>
</template>

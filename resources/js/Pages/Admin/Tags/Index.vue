<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

interface Tag { id: number; name: string; slug: string; color: string; link_url: string | null }

defineProps<{ tags: Tag[] }>()

const editingId = ref<number | null>(null)
const form = useForm({ name: '', color: '#2563eb', link_url: '' })

function startNew() {
    editingId.value = null
    form.reset()
    form.color = '#2563eb'
}

function startEdit(tag: Tag) {
    editingId.value = tag.id
    form.name     = tag.name
    form.color    = tag.color
    form.link_url = tag.link_url ?? ''
}

function submit() {
    const url    = editingId.value ? route('admin.tags.update', editingId.value) : route('admin.tags.store')
    const method = editingId.value ? 'put' : 'post'
    form[method](url, { preserveScroll: true, onSuccess: startNew })
}

function destroy(tag: Tag) {
    if (!confirm(`Remover tag "${tag.name}"?`)) return
    router.delete(route('admin.tags.destroy', tag.id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Tags" />

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4 h-fit">
                <h2 class="font-medium text-gray-900">{{ editingId ? 'Editar tag' : 'Nova tag' }}</h2>

                <FormField label="Nome" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" required maxlength="80"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                </FormField>

                <FormField label="Cor" :error="form.errors.color">
                    <div class="flex items-center gap-2">
                        <input v-model="form.color" type="color"
                            class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
                        <input v-model="form.color" type="text"
                            class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </div>
                </FormField>

                <FormField label="Link (opcional)" :error="form.errors.link_url">
                    <input v-model="form.link_url" type="text" maxlength="500" placeholder="/store/catalog?tag=verao"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    <p class="text-xs text-gray-400 mt-1">URL para onde a tag deve levar o usuário.</p>
                </FormField>

                <div class="flex gap-2 justify-end">
                    <button v-if="editingId" type="button" @click="startNew"
                        class="text-sm text-gray-500 border border-gray-300 rounded-xl px-3 py-2">
                        Cancelar
                    </button>
                    <PrimaryButton type="submit" :loading="form.processing">
                        {{ editingId ? 'Salvar' : 'Criar' }}
                    </PrimaryButton>
                </div>
            </form>

            <!-- List -->
            <div class="md:col-span-2 space-y-2">
                <div v-for="tag in tags" :key="tag.id"
                    class="bg-white border border-gray-200 rounded-2xl p-4 flex items-center gap-4 flex-wrap">
                    <span :style="{ backgroundColor: tag.color + '22', color: tag.color }"
                        class="text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md">
                        {{ tag.name }}
                    </span>
                    <span v-if="tag.link_url" class="text-xs text-gray-400 truncate max-w-xs">{{ tag.link_url }}</span>
                    <div class="ml-auto flex gap-3">
                        <button @click="startEdit(tag)" class="text-sm text-gray-600 hover:text-gray-900">Editar</button>
                        <button @click="destroy(tag)" class="text-sm text-red-600 hover:text-red-700">Remover</button>
                    </div>
                </div>
                <p v-if="!tags.length"
                    class="text-center text-sm text-gray-400 py-12 bg-white border border-gray-200 rounded-2xl">
                    Nenhuma tag criada.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

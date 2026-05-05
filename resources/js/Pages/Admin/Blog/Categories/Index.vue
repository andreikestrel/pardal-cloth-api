<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

interface Category { id: string; name: string; slug: string; color: string; posts_count: number }

defineProps<{ categories: Category[] }>()

const editingId = ref<string | null>(null)
const form = useForm({ name: '', color: '#2563eb' })

function startNew() {
    editingId.value = null
    form.reset()
    form.color = '#2563eb'
}

function startEdit(cat: Category) {
    editingId.value = cat.id
    form.name = cat.name
    form.color = cat.color
}

function submit() {
    const url = editingId.value
        ? route('admin.blog.categories.update', editingId.value)
        : route('admin.blog.categories.store')
    const method = editingId.value ? 'put' : 'post'

    form[method](url, { preserveScroll: true, onSuccess: startNew })
}

function destroy(cat: Category) {
    if (!confirm(`Remover "${cat.name}"?`)) return
    router.delete(route('admin.blog.categories.destroy', cat.id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Blog — Categorias" />

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4 h-fit">
                <h2 class="font-medium text-gray-900">{{ editingId ? 'Editar' : 'Nova categoria' }}</h2>

                <FormField label="Nome" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" required maxlength="120"
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
                <div v-for="cat in categories" :key="cat.id"
                    class="bg-white border border-gray-200 rounded-2xl p-4 flex items-center gap-4">
                    <span :style="{ backgroundColor: cat.color + '22', color: cat.color }"
                        class="text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md">
                        {{ cat.name }}
                    </span>
                    <span class="text-xs text-gray-400">{{ cat.posts_count }} post(s)</span>
                    <div class="ml-auto flex gap-3">
                        <button @click="startEdit(cat)" class="text-sm text-gray-600 hover:text-gray-900">Editar</button>
                        <button @click="destroy(cat)" class="text-sm text-red-600 hover:text-red-700">Remover</button>
                    </div>
                </div>
                <p v-if="!categories.length" class="text-center text-sm text-gray-400 py-12 bg-white border border-gray-200 rounded-2xl">
                    Nenhuma categoria.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

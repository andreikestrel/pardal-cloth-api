<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import type { Category } from '@/types'

defineProps<{ categories: Category[] }>()

const headers = ['Nome', 'Slug', 'Ativo', 'Ações']

function destroy(id: string) {
    if (!confirm('Excluir esta categoria?')) return
    router.delete(route('admin.categories.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Categorias">
            <template #action>
                <Link :href="route('admin.categories.create')"
                    class="text-sm font-medium text-white px-4 py-2 rounded-xl"
                    style="background-color: var(--color-primary)">
                    Nova categoria
                </Link>
            </template>
        </PageHeader>

        <DataTable :headers="headers">
            <tr v-for="cat in categories" :key="cat.id"
                class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm text-gray-900 flex items-center gap-3">
                    <img v-if="cat.image_url" :src="cat.image_url" :alt="cat.name"
                        class="w-8 h-8 rounded-lg object-cover" />
                    <div v-else class="w-8 h-8 rounded-lg bg-gray-100" />
                    {{ cat.name }}
                </td>
                <td class="px-5 py-3 text-sm text-gray-500 font-mono">{{ cat.slug }}</td>
                <td class="px-5 py-3">
                    <span :class="['text-xs font-medium px-2 py-0.5 rounded-full',
                        cat.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        {{ cat.active ? 'Sim' : 'Não' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm">
                    <div class="flex gap-3">
                        <Link :href="route('admin.categories.edit', cat.id)"
                            class="text-gray-500 hover:text-gray-800">Editar</Link>
                        <button @click="destroy(cat.id)"
                            class="text-red-400 hover:text-red-600">Excluir</button>
                    </div>
                </td>
            </tr>
            <tr v-if="!categories.length">
                <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhuma categoria cadastrada.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

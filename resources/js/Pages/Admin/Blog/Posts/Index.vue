<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'

interface Post {
    id: string
    title: string
    slug: string
    published_at: string | null
    active: boolean
    cover_url: string | null
    category: { name: string; color: string } | null
    author: { name: string } | null
    created_at: string
}

defineProps<{ posts: Post[] }>()

const headers = ['', 'Título', 'Categoria', 'Autor', 'Status', 'Publicado em']

function status(post: Post): { label: string; classes: string } {
    if (!post.active) return { label: 'Inativo', classes: 'bg-gray-100 text-gray-500' }
    if (!post.published_at) return { label: 'Rascunho', classes: 'bg-amber-100 text-amber-700' }
    if (new Date(post.published_at) > new Date()) return { label: 'Agendado', classes: 'bg-blue-100 text-blue-700' }
    return { label: 'Publicado', classes: 'bg-green-100 text-green-700' }
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Blog">
            <template #action>
                <Link :href="route('admin.blog.posts.create')"
                    class="text-sm text-white px-4 py-2 rounded-xl font-medium transition-opacity hover:opacity-90"
                    style="background-color: var(--color-primary)">
                    + Novo post
                </Link>
            </template>
        </PageHeader>

        <DataTable :headers="headers">
            <tr v-for="post in posts" :key="post.id" class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-3 py-3 w-20">
                    <div class="w-16 h-12 rounded-lg overflow-hidden bg-gray-100">
                        <img v-if="post.cover_url" :src="post.cover_url" :alt="post.title" class="w-full h-full object-cover" />
                    </div>
                </td>
                <td class="px-3 py-3 text-sm">
                    <Link :href="route('admin.blog.posts.edit', post.id)" class="font-medium text-gray-900 hover:underline">
                        {{ post.title }}
                    </Link>
                </td>
                <td class="px-3 py-3">
                    <span v-if="post.category"
                        :style="{ backgroundColor: post.category.color + '22', color: post.category.color }"
                        class="inline-block text-xs font-semibold uppercase tracking-wider px-2 py-1 rounded-md">
                        {{ post.category.name }}
                    </span>
                </td>
                <td class="px-3 py-3 text-sm text-gray-500">{{ post.author?.name ?? '—' }}</td>
                <td class="px-3 py-3">
                    <span :class="['text-xs font-medium px-2 py-1 rounded-lg', status(post).classes]">
                        {{ status(post).label }}
                    </span>
                </td>
                <td class="px-3 py-3 text-xs text-gray-500">
                    {{ post.published_at ? new Date(post.published_at).toLocaleDateString('pt-BR') : '—' }}
                </td>
            </tr>
            <tr v-if="!posts.length">
                <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhum post ainda.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

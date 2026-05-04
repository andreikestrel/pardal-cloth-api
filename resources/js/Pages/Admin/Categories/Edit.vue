<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import CategoryForm from '@/Components/Admin/CategoryForm.vue'
import type { Category } from '@/types'

const props = defineProps<{ category: Category }>()
</script>

<template>
    <AdminLayout>
        <PageHeader :title="`Editar: ${category.name}`" />

        <div class="max-w-2xl">
            <CategoryForm
                :category="category"
                :submit-route="route('admin.categories.update', category.id)"
                method="put">
                <template #cancel>
                    <Link :href="route('admin.categories.index')"
                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                        Cancelar
                    </Link>
                </template>
            </CategoryForm>
        </div>
    </AdminLayout>
</template>

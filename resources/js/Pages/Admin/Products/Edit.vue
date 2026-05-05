<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import ProductForm from '@/Components/Admin/ProductForm.vue'
import type { Category, Product } from '@/types'

interface Tag { id: number; name: string; color: string }
defineProps<{ product: Product; categories: Category[]; allTags: Tag[] }>()
</script>

<template>
    <AdminLayout>
        <PageHeader :title="`Editar: ${product.name}`">
            <template #action>
                <Link :href="route('admin.products.index')"
                    class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                    ← Voltar
                </Link>
            </template>
        </PageHeader>

        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <ProductForm
                :product="product"
                :categories="categories"
                :all-tags="allTags"
                :submit-route="route('admin.products.edit', product.id)"
                method="put"
            >
                <template #cancel>
                    <Link :href="route('admin.products.index')"
                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2.5">
                        Cancelar
                    </Link>
                </template>
            </ProductForm>
        </div>
    </AdminLayout>
</template>

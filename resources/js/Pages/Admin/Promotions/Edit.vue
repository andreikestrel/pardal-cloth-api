<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import PromotionForm from '@/Components/Admin/PromotionForm.vue'
import type { Promotion, Category, Product } from '@/types'

const props = defineProps<{
    promotion: Promotion
    categories: Category[]
    products: Product[]
}>()
</script>

<template>
    <AdminLayout>
        <PageHeader :title="`Editar: ${promotion.name}`" />

        <div class="max-w-2xl">
            <PromotionForm
                :promotion="promotion"
                :categories="categories"
                :products="products"
                :submit-route="route('admin.promotions.update', promotion.id)"
                method="put">
                <template #cancel>
                    <Link :href="route('admin.promotions.index')"
                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                        Cancelar
                    </Link>
                </template>
            </PromotionForm>
        </div>
    </AdminLayout>
</template>

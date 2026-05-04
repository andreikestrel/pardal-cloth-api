<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import PromotionForm from '@/Components/Admin/PromotionForm.vue'
import type { Category, Product } from '@/types'

defineProps<{ categories: Category[]; products: Product[] }>()
</script>

<template>
    <AdminLayout>
        <PageHeader title="Nova promoção" />

        <div class="max-w-2xl">
            <PromotionForm
                :categories="categories"
                :products="products"
                :submit-route="route('admin.promotions.store')"
                method="post">
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

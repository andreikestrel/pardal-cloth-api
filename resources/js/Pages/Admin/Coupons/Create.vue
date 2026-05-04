<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import CouponForm from '@/Components/Admin/CouponForm.vue'
</script>

<template>
    <AdminLayout>
        <PageHeader title="Novo cupom" />

        <div class="max-w-2xl">
            <CouponForm :submit-route="route('admin.coupons.store')" method="post">
                <template #cancel>
                    <Link :href="route('admin.coupons.index')"
                        class="text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-xl px-4 py-2">
                        Cancelar
                    </Link>
                </template>
            </CouponForm>
        </div>
    </AdminLayout>
</template>

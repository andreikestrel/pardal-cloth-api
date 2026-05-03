<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { PageProps } from '@/types'

const page = usePage<PageProps>()
const flash = computed(() => page.props.flash)

const navItems = [
    { label: 'Pedidos',    href: route('admin.orders.index') },
    { label: 'Produtos',   href: route('admin.products.index') },
    { label: 'Categorias', href: route('admin.categories.index') },
    { label: 'Promoções',  href: route('admin.promotions.index') },
    { label: 'Cupons',     href: route('admin.coupons.index') },
    { label: 'Estoque',    href: route('admin.stock.index') },
    { label: 'Relatórios', href: route('admin.reports.index') },
    { label: 'Config.',    href: route('admin.settings.general') },
]
</script>

<template>
    <div class="min-h-screen flex bg-gray-50">
        <!-- Sidebar -->
        <aside class="w-56 bg-gray-900 text-white flex flex-col shrink-0">
            <div class="px-6 py-5 font-semibold text-lg border-b border-gray-700">
                <Link :href="route('admin.orders.index')">Admin</Link>
            </div>

            <nav class="flex-1 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="block px-6 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="px-6 py-4 border-t border-gray-700 text-xs text-gray-500">
                <Link :href="route('home')" class="hover:text-gray-300">← Voltar à loja</Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Flash -->
            <div v-if="flash.success" class="bg-green-50 border-b border-green-200 px-6 py-2 text-sm text-green-800">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="bg-red-50 border-b border-red-200 px-6 py-2 text-sm text-red-800">
                {{ flash.error }}
            </div>

            <main class="flex-1 p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

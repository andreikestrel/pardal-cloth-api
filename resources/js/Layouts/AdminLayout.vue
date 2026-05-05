<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PardaLogo from '@/Components/UI/PardaLogo.vue'
import type { PageProps } from '@/types'

const page = usePage<PageProps>()
const flash = computed(() => page.props.flash)
const settings = computed(() => page.props.settings)

const themeVars = computed(() => ({
    '--color-primary':   settings.value.primary_color   || '#111827',
    '--color-secondary': settings.value.secondary_color || '#6b7280',
    '--color-accent':    settings.value.accent_color    || '#f59e0b',
}))

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard',  href: route('admin.dashboard') },
        { label: 'Pedidos',    href: route('admin.orders.index') },
        { label: 'Produtos',   href: route('admin.products.index') },
        { label: 'Categorias', href: route('admin.categories.index') },
        { label: 'Promoções',  href: route('admin.promotions.index') },
        { label: 'Cupons',     href: route('admin.coupons.index') },
        { label: 'Estoque',    href: route('admin.stock.index') },
        { label: 'Catálogo',   href: route('admin.catalog.slides') },
        { label: 'Tags',       href: route('admin.tags.index') },
    ]

    if (settings.value?.blog_enabled) {
        items.push(
            { label: 'Blog',          href: route('admin.blog.posts.index') },
            { label: 'Blog: temas',   href: route('admin.blog.categories.index') },
        )
    }

    items.push(
        { label: 'Relatórios', href: route('admin.reports.index') },
        { label: 'Usuários',   href: route('admin.users.index') },
    )

    return items
})

const settingsItems = [
    { label: 'Geral',      href: route('admin.settings.general') },
    { label: 'Pagamentos', href: route('admin.settings.payment') },
]

const currentPath = computed(() => page.url)
</script>

<template>
    <div class="min-h-screen flex bg-gray-50" :style="themeVars">
        <!-- Sidebar -->
        <aside class="w-56 shrink-0 flex flex-col" :style="{ backgroundColor: 'var(--color-primary)' }">
            <div class="px-5 py-4 border-b border-white/10">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2 text-white">
                    <img v-if="settings.logo" :src="settings.logo" :alt="settings.company_name" class="h-7 w-auto" />
                    <template v-else>
                        <PardaLogo class="h-6 w-auto" />
                        <span class="font-semibold text-base">{{ settings.company_name }}</span>
                    </template>
                </Link>
                <p class="text-white/50 text-xs mt-0.5">Painel admin</p>
            </div>

            <nav class="flex-1 py-3 overflow-y-auto">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="['block px-5 py-2.5 text-sm transition-colors',
                        currentPath.startsWith(item.href)
                            ? 'text-white bg-white/15 font-medium'
                            : 'text-white/70 hover:text-white hover:bg-white/10']"
                >
                    {{ item.label }}
                </Link>

                <!-- Settings group -->
                <div class="mt-3 pt-3 border-t border-white/10">
                    <p class="px-5 pb-1 text-xs text-white/30 uppercase tracking-wider">Configurações</p>
                    <Link
                        v-for="item in settingsItems"
                        :key="item.href"
                        :href="item.href"
                        :class="['block px-5 py-2.5 text-sm transition-colors',
                            currentPath.startsWith(item.href)
                                ? 'text-white bg-white/15 font-medium'
                                : 'text-white/70 hover:text-white hover:bg-white/10']"
                    >
                        {{ item.label }}
                    </Link>
                </div>
            </nav>

            <div class="px-5 py-4 border-t border-white/10 text-xs text-white/40">
                <Link :href="route('home')" class="hover:text-white/70">← Voltar à loja</Link>
            </div>
        </aside>

        <!-- Main area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Flash -->
            <div v-if="flash.success" class="bg-green-50 border-b border-green-200 px-6 py-2 text-sm text-green-800">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="bg-red-50 border-b border-red-200 px-6 py-2 text-sm text-red-800">
                {{ flash.error }}
            </div>

            <main class="flex-1 p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

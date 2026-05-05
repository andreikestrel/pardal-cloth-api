<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useCart } from '@/Composables/useCart'
import CartDrawer from '@/Components/Shop/CartDrawer.vue'
import type { PageProps } from '@/types'

const page = usePage<PageProps>()
const user = computed(() => page.props.auth.user)
const settings = computed(() => page.props.settings)
const flash = computed(() => page.props.flash)

const { itemCount, openCart } = useCart()

const themeVars = computed(() => ({
    '--color-primary':   settings.value.primary_color   || '#111827',
    '--color-secondary': settings.value.secondary_color || '#6b7280',
    '--color-accent':    settings.value.accent_color    || '#f59e0b',
}))
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white" :style="themeVars">
        <!-- Flash messages -->
        <div v-if="flash.success" class="px-4 py-2 text-sm text-center text-green-800 bg-green-50 border-b border-green-200">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="px-4 py-2 text-sm text-center text-red-800 bg-red-50 border-b border-red-200">
            {{ flash.error }}
        </div>

        <!-- Navigation -->
        <header class="border-b border-gray-200 sticky top-0 bg-white z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <!-- Logo / brand -->
                <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
                    <img v-if="settings.logo" :src="settings.logo" :alt="settings.company_name"
                        class="h-8 w-auto" />
                    <span v-else class="text-xl font-semibold tracking-tight" :style="{ color: 'var(--color-primary)' }">
                        {{ settings.company_name }}
                    </span>
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <Link :href="route('shop.index')" class="text-gray-600 hover:text-gray-900">Loja</Link>
                    <Link :href="route('categories.index')" class="text-gray-600 hover:text-gray-900">Categorias</Link>
                    <Link :href="route('about')" class="text-gray-600 hover:text-gray-900">Sobre</Link>
                </nav>

                <!-- Right side -->
                <div class="flex items-center gap-3 text-sm">
                    <!-- Cart button — opens side drawer -->
                    <button @click="openCart" aria-label="Abrir carrinho"
                        class="relative text-gray-700 hover:text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.4 7h12.8M7 13H5.4M17 21a1 1 0 100-2 1 1 0 000 2zm-10 0a1 1 0 100-2 1 1 0 000 2z" />
                        </svg>
                        <span v-if="itemCount > 0"
                            class="absolute -top-1.5 -right-1.5 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center"
                            :style="{ backgroundColor: 'var(--color-primary)' }">
                            {{ itemCount > 9 ? '9+' : itemCount }}
                        </span>
                    </button>

                    <template v-if="user">
                        <Link :href="route('orders.index')" class="hidden sm:inline text-gray-600 hover:text-gray-900">Meus pedidos</Link>
                        <Link v-if="user.roles.includes('admin')" :href="route('admin.dashboard')"
                            class="hidden sm:inline text-gray-600 hover:text-gray-900">Admin</Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-gray-600 hover:text-gray-900">Sair</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">Entrar</Link>
                        <Link :href="route('register')"
                            class="hidden sm:inline-block text-white px-4 py-1.5 rounded-lg text-sm font-medium"
                            :style="{ backgroundColor: 'var(--color-primary)' }">
                            Cadastrar
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Mobile nav strip -->
            <div class="md:hidden border-t border-gray-100 px-4 py-2 flex gap-5 text-sm text-gray-600 overflow-x-auto">
                <Link :href="route('shop.index')" class="whitespace-nowrap hover:text-gray-900">Loja</Link>
                <Link :href="route('categories.index')" class="whitespace-nowrap hover:text-gray-900">Categorias</Link>
                <Link :href="route('about')" class="whitespace-nowrap hover:text-gray-900">Sobre</Link>
                <Link v-if="user" :href="route('orders.index')" class="whitespace-nowrap hover:text-gray-900">Meus pedidos</Link>
            </div>
        </header>

        <!-- Page content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-8 text-center text-sm text-gray-400">
            &copy; {{ new Date().getFullYear() }} {{ settings.company_name }}. Todos os direitos reservados.
        </footer>

        <!-- Cart drawer (global, always mounted) -->
        <CartDrawer />
    </div>
</template>

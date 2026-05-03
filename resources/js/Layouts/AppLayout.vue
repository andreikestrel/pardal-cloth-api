<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { PageProps } from '@/types'

const page = usePage<PageProps>()
const user = computed(() => page.props.auth.user)
const settings = computed(() => page.props.settings)
const flash = computed(() => page.props.flash)
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white">
        <!-- Flash messages -->
        <div v-if="flash.success" class="bg-green-50 border-b border-green-200 px-4 py-2 text-sm text-green-800 text-center">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="bg-red-50 border-b border-red-200 px-4 py-2 text-sm text-red-800 text-center">
            {{ flash.error }}
        </div>

        <!-- Navigation -->
        <header class="border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <Link :href="route('home')" class="text-xl font-semibold tracking-tight">
                    {{ settings.company_name }}
                </Link>

                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <Link :href="route('shop.index')" class="text-gray-600 hover:text-gray-900">Loja</Link>
                    <Link :href="route('categories.index')" class="text-gray-600 hover:text-gray-900">Categorias</Link>
                </nav>

                <div class="flex items-center gap-4 text-sm">
                    <template v-if="user">
                        <Link :href="route('orders.index')" class="text-gray-600 hover:text-gray-900">Meus pedidos</Link>
                        <Link v-if="user.roles.includes('admin')" :href="route('admin.orders.index')" class="text-gray-600 hover:text-gray-900">Admin</Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-gray-600 hover:text-gray-900">Sair</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">Entrar</Link>
                        <Link :href="route('register')" class="bg-gray-900 text-white px-4 py-1.5 rounded-md hover:bg-gray-700">Cadastrar</Link>
                    </template>
                </div>
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
    </div>
</template>

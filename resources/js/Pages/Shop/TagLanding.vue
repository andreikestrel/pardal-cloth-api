<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'

interface Tag { id: number; name: string; color: string; link_url: string | null }

interface Product {
    id: string
    name: string
    slug: string
    base_price: number
    cover_url: string | null
    colors: string[]
    sizes: string[]
}

interface Post {
    id: string
    title: string
    slug: string
    excerpt: string | null
    published_at: string
    cover_url: string | null
    category: { name: string; color: string } | null
}

defineProps<{
    tag: Tag
    products: Product[]
    posts: Post[]
}>()

function formatPrice(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
}
</script>

<template>
    <AppLayout>
        <!-- Tag header -->
        <div class="max-w-7xl mx-auto px-4 pt-10 pb-4">
            <div class="flex items-center gap-3 mb-2">
                <span :style="{ backgroundColor: tag.color + '22', color: tag.color }"
                    class="text-sm font-semibold uppercase tracking-wider px-3 py-1.5 rounded-lg">
                    # {{ tag.name }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ tag.name }}</h1>
            <p class="text-sm text-gray-400 mt-1">
                {{ products.length }} produto{{ products.length !== 1 ? 's' : '' }}
                <template v-if="posts.length">
                    · {{ posts.length }} post{{ posts.length !== 1 ? 's' : '' }}
                </template>
            </p>
        </div>

        <!-- Products section -->
        <section v-if="products.length" class="max-w-7xl mx-auto px-4 py-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">Produtos</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <Link v-for="product in products" :key="product.id"
                    :href="route('shop.show', product.slug)"
                    class="group flex flex-col">
                    <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100 mb-3 relative">
                        <img v-if="product.cover_url" :src="product.cover_url" :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-gray-600 transition-colors line-clamp-2">
                        {{ product.name }}
                    </h3>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ formatPrice(product.base_price) }}</p>
                </Link>
            </div>
        </section>

        <!-- Blog posts section -->
        <section v-if="posts.length" class="max-w-7xl mx-auto px-4 py-8 border-t border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">Posts do blog</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link v-for="post in posts" :key="post.id" :href="route('blog.show', post.slug)"
                    class="group flex flex-col">
                    <div class="aspect-[16/10] rounded-2xl overflow-hidden bg-gray-100 mb-4 relative">
                        <img v-if="post.cover_url" :src="post.cover_url" :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <span v-if="post.category"
                            :style="{ backgroundColor: post.category.color, color: '#fff' }"
                            class="absolute bottom-3 left-3 text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md">
                            {{ post.category.name }}
                        </span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-gray-700 transition-colors line-clamp-2">
                        {{ post.title }}
                    </h3>
                    <p v-if="post.excerpt" class="text-sm text-gray-500 mt-2 line-clamp-2">{{ post.excerpt }}</p>
                    <p class="mt-auto pt-4 text-xs text-gray-400">{{ formatDate(post.published_at) }}</p>
                </Link>
            </div>
        </section>

        <div v-if="!products.length && !posts.length" class="max-w-7xl mx-auto px-4 py-20 text-center text-sm text-gray-400">
            Nenhum conteúdo com esta tag ainda.
        </div>
    </AppLayout>
</template>

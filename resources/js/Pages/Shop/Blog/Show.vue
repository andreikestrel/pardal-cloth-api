<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import BlogPostBody from '@/Components/Shop/BlogPostBody.vue'

interface Category { id: string; name: string; slug: string; color: string }
interface Author { id: string; name: string }
interface Post {
    id: string
    title: string
    slug: string
    excerpt: string | null
    body_html: string
    published_at: string
    cover_url: string | null
    category: Category | null
    author: Author | null
}

defineProps<{ post: Post; related: Post[] }>()

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<template>
    <AppLayout>
        <article>
            <!-- Cover -->
            <div v-if="post.cover_url" class="aspect-[21/9] bg-gray-100 overflow-hidden">
                <img :src="post.cover_url" :alt="post.title" class="w-full h-full object-cover" />
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
                <Link :href="route('blog.index')" class="text-sm text-gray-500 hover:text-gray-900 mb-6 inline-flex items-center gap-1">
                    ← Voltar para o blog
                </Link>

                <div v-if="post.category" class="mb-3">
                    <Link :href="route('blog.index', { category: post.category.slug })"
                        :style="{ backgroundColor: post.category.color + '22', color: post.category.color }"
                        class="inline-block text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md">
                        {{ post.category.name }}
                    </Link>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight mb-4">{{ post.title }}</h1>

                <p v-if="post.excerpt" class="text-lg text-gray-500 mb-6">{{ post.excerpt }}</p>

                <div class="flex items-center gap-3 text-sm text-gray-500 mb-8 pb-6 border-b border-gray-100">
                    <span v-if="post.author">por <strong class="text-gray-900">{{ post.author.name }}</strong></span>
                    <span>·</span>
                    <span>{{ formatDate(post.published_at) }}</span>
                </div>

                <BlogPostBody :html="post.body_html" />
            </div>
        </article>

        <!-- Related -->
        <section v-if="related.length" class="bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-xl font-semibold mb-6">Continue lendo</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link v-for="r in related" :key="r.id" :href="route('blog.show', r.slug)" class="group">
                        <div class="aspect-[16/10] rounded-xl overflow-hidden bg-gray-100 mb-3">
                            <img v-if="r.cover_url" :src="r.cover_url" :alt="r.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-gray-700 line-clamp-2">{{ r.title }}</h3>
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

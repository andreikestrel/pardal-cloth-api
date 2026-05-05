<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'

interface Author { id: string; name: string }
interface Category { id: string; name: string; slug: string; color: string }
interface Post {
    id: string
    title: string
    slug: string
    excerpt: string | null
    published_at: string
    cover_url: string | null
    category: Category | null
    author: Author | null
}

const props = defineProps<{
    featured: Post[]
    posts: { data: Post[]; meta?: { total?: number } }
    categories: Category[]
    filter: { category?: string }
}>()

// ─── Carousel ────────────────────────────────────────────────────────────
const current = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

function next() { current.value = (current.value + 1) % Math.max(1, props.featured.length) }
function go(i: number)  { current.value = i }
function start() { if (props.featured.length > 1) timer = setInterval(next, 6000) }
function stop()  { if (timer) { clearInterval(timer); timer = null } }

onMounted(start)
onBeforeUnmount(stop)

const activeCategory = computed(() => props.filter.category ?? '')

function selectCategory(slug: string | null) {
    router.get(route('blog.index'), slug ? { category: slug } : {}, { preserveScroll: true, replace: true })
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
}
</script>

<template>
    <AppLayout>
        <!-- Featured carousel -->
        <section v-if="featured.length" class="relative bg-gray-100" @mouseenter="stop" @mouseleave="start">
            <div class="relative aspect-[16/7] sm:aspect-[16/6] min-h-[280px] overflow-hidden">
                <template v-for="(post, i) in featured" :key="post.id">
                    <Link :href="route('blog.show', post.slug)"
                        class="absolute inset-0 transition-opacity duration-700"
                        :class="i === current ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                        <img v-if="post.cover_url" :src="post.cover_url" :alt="post.title"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

                        <div class="relative h-full flex flex-col justify-end max-w-7xl mx-auto px-6 sm:px-10 pb-10 text-white">
                            <span v-if="post.category"
                                :style="{ backgroundColor: post.category.color, color: '#fff' }"
                                class="self-start text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md mb-3">
                                {{ post.category.name }}
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-bold leading-tight max-w-3xl drop-shadow">
                                {{ post.title }}
                            </h2>
                            <p v-if="post.excerpt" class="mt-2 text-sm sm:text-base text-white/90 max-w-2xl line-clamp-2">
                                {{ post.excerpt }}
                            </p>
                        </div>
                    </Link>
                </template>
            </div>

            <div v-if="featured.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <button v-for="(_, i) in featured" :key="i" @click="go(i)" :aria-label="`Slide ${i + 1}`"
                    :class="['h-2 rounded-full transition-all',
                        i === current ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/80 w-2']" />
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 py-12">
            <h1 class="text-2xl font-semibold mb-2">Veja mais</h1>
            <p class="text-sm text-gray-500 mb-6">Histórias, dicas e novidades.</p>

            <!-- Category chips -->
            <div v-if="categories.length" class="flex gap-2 overflow-x-auto pb-2 mb-6 -mx-4 px-4">
                <button @click="selectCategory(null)"
                    :class="['px-4 py-2 rounded-full border text-sm font-medium whitespace-nowrap transition-colors',
                        !activeCategory
                            ? 'bg-gray-900 text-white border-gray-900'
                            : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500']">
                    Todas
                </button>
                <button v-for="cat in categories" :key="cat.id" @click="selectCategory(cat.slug)"
                    :class="['px-4 py-2 rounded-full border text-sm font-medium whitespace-nowrap transition-colors',
                        activeCategory === cat.slug
                            ? 'text-white border-transparent'
                            : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500']"
                    :style="activeCategory === cat.slug ? { backgroundColor: cat.color, borderColor: cat.color } : {}">
                    {{ cat.name }}
                </button>
            </div>

            <!-- Cards grid -->
            <div v-if="posts.data.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link v-for="post in posts.data" :key="post.id" :href="route('blog.show', post.slug)"
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
                    <p v-if="post.excerpt" class="text-sm text-gray-500 mt-2 line-clamp-2">
                        {{ post.excerpt }}
                    </p>

                    <div class="mt-auto pt-4 text-xs text-gray-400">
                        <span v-if="post.author">por <strong class="text-gray-700">{{ post.author.name }}</strong></span>
                        <span class="mx-1.5">·</span>
                        <span>{{ formatDate(post.published_at) }}</span>
                    </div>
                </Link>
            </div>
            <p v-else class="text-center text-sm text-gray-400 py-12">Nenhum post nesta categoria.</p>
        </div>
    </AppLayout>
</template>

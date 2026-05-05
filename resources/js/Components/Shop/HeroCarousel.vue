<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Slide {
    id: string
    title: string | null
    subtitle: string | null
    link_url: string | null
    link_label: string | null
    image_url: string | null
}

const props = defineProps<{ slides: Slide[] }>()

const current = ref(0)
const intervalMs = 5000
let timer: ReturnType<typeof setInterval> | null = null

const hasSlides = computed(() => props.slides.length > 0)

function go(i: number) {
    current.value = (i + props.slides.length) % props.slides.length
    restart()
}

function next() { go(current.value + 1) }
function prev() { go(current.value - 1) }

function start() {
    if (!hasSlides.value || props.slides.length < 2) return
    timer = setInterval(next, intervalMs)
}

function stop() {
    if (timer) {
        clearInterval(timer)
        timer = null
    }
}

function restart() {
    stop()
    start()
}

function isExternal(url: string): boolean {
    return /^https?:\/\//i.test(url)
}

onMounted(start)
onBeforeUnmount(stop)
</script>

<template>
    <div v-if="hasSlides" class="relative overflow-hidden bg-gray-900"
        @mouseenter="stop" @mouseleave="start">
        <div class="relative aspect-[16/6] sm:aspect-[16/5] min-h-[260px]">
            <template v-for="(slide, i) in slides" :key="slide.id">
                <div
                    class="absolute inset-0 transition-opacity duration-700"
                    :class="i === current ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                >
                    <img v-if="slide.image_url" :src="slide.image_url" :alt="slide.title ?? ''"
                        class="absolute inset-0 w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/40"></div>

                    <div class="relative h-full flex flex-col items-center justify-center text-center text-white px-6">
                        <h2 v-if="slide.title" class="text-3xl sm:text-5xl font-bold mb-3 drop-shadow">
                            {{ slide.title }}
                        </h2>
                        <p v-if="slide.subtitle" class="text-sm sm:text-lg text-white/90 mb-6 max-w-2xl drop-shadow">
                            {{ slide.subtitle }}
                        </p>

                        <a v-if="slide.link_url && isExternal(slide.link_url)"
                            :href="slide.link_url" target="_blank" rel="noopener"
                            class="bg-white text-gray-900 px-6 sm:px-8 py-2.5 sm:py-3 rounded-xl font-medium text-sm sm:text-base hover:bg-gray-100 transition-colors">
                            {{ slide.link_label || 'Saiba mais' }}
                        </a>
                        <Link v-else-if="slide.link_url"
                            :href="slide.link_url"
                            class="bg-white text-gray-900 px-6 sm:px-8 py-2.5 sm:py-3 rounded-xl font-medium text-sm sm:text-base hover:bg-gray-100 transition-colors">
                            {{ slide.link_label || 'Saiba mais' }}
                        </Link>
                    </div>
                </div>
            </template>
        </div>

        <!-- Arrows -->
        <button v-if="slides.length > 1" @click="prev" aria-label="Anterior"
            class="absolute top-1/2 left-3 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur text-white text-lg flex items-center justify-center transition-colors">
            ‹
        </button>
        <button v-if="slides.length > 1" @click="next" aria-label="Próximo"
            class="absolute top-1/2 right-3 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur text-white text-lg flex items-center justify-center transition-colors">
            ›
        </button>

        <!-- Dots -->
        <div v-if="slides.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
            <button v-for="(_, i) in slides" :key="i" @click="go(i)" :aria-label="`Slide ${i + 1}`"
                :class="['h-2 rounded-full transition-all',
                    i === current ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/80 w-2']" />
        </div>
    </div>
</template>

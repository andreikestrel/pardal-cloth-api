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
    <div v-if="hasSlides" class="relative overflow-hidden"
        @mouseenter="stop" @mouseleave="start">

        <!-- Slides — stack via CSS grid so the container takes the height of the active image -->
        <div class="carousel-track">
            <template v-for="(slide, i) in slides" :key="slide.id">
                <div class="carousel-slide"
                    :class="i === current ? 'opacity-100' : 'opacity-0 pointer-events-none'">

                    <img v-if="slide.image_url" :src="slide.image_url" :alt="slide.title ?? ''"
                        class="w-full block" />

                    <!-- Text pinned to the bottom of the image -->
                    <div v-if="slide.title || slide.subtitle || slide.link_url"
                        class="absolute inset-x-0 bottom-0 flex flex-col items-center text-center pb-8 px-6">
                        <h2 v-if="slide.title" class="slide-title text-3xl sm:text-5xl font-bold mb-2">
                            {{ slide.title }}
                        </h2>
                        <p v-if="slide.subtitle" class="slide-subtitle text-sm sm:text-lg mb-5 max-w-2xl">
                            {{ slide.subtitle }}
                        </p>
                        <a v-if="slide.link_url && isExternal(slide.link_url)"
                            :href="slide.link_url" target="_blank" rel="noopener"
                            class="bg-white text-gray-900 px-6 sm:px-8 py-2.5 sm:py-3 rounded-xl font-medium text-sm sm:text-base hover:bg-gray-100 transition-colors">
                            {{ slide.link_label || 'Saiba mais' }}
                        </a>
                        <Link v-else-if="slide.link_url" :href="slide.link_url"
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

<style scoped>
/* Stack all slides in the same grid cell so container height = active image height */
.carousel-track {
    display: grid;
}

.carousel-slide {
    grid-area: 1 / 1;
    position: relative;
    transition: opacity 0.7s ease;
}

.slide-title {
    color: #fff;
    -webkit-text-stroke: 1.5px rgba(0, 0, 0, 0.85);
    paint-order: stroke fill;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.6), 0 1px 3px rgba(0, 0, 0, 0.8);
}

.slide-subtitle {
    color: #fff;
    -webkit-text-stroke: 0.6px rgba(0, 0, 0, 0.7);
    paint-order: stroke fill;
    text-shadow: 0 1px 8px rgba(0, 0, 0, 0.7), 0 1px 2px rgba(0, 0, 0, 0.9);
}
</style>

<script setup lang="ts">
defineProps<{ html: string }>()
</script>

<template>
    <div class="blog-prose" v-html="html"></div>
</template>

<style>
.blog-prose { color: #1f2937; line-height: 1.75; font-size: 1rem; }
.blog-prose h1 { font-size: 2rem; font-weight: 700; margin: 1.75rem 0 0.75rem; color: #111827; }
.blog-prose h2 { font-size: 1.5rem; font-weight: 700; margin: 1.5rem 0 0.5rem; color: #111827; }
.blog-prose h3 { font-size: 1.25rem; font-weight: 600; margin: 1.25rem 0 0.5rem; color: #111827; }
.blog-prose p  { margin: 0.75rem 0; }
.blog-prose img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 1.25rem 0; }
.blog-prose ul { list-style: disc; padding-left: 1.5rem; margin: 0.75rem 0; }
.blog-prose ol { list-style: decimal; padding-left: 1.5rem; margin: 0.75rem 0; }
.blog-prose li { margin: 0.25rem 0; }
.blog-prose blockquote { border-left: 4px solid #d1d5db; padding-left: 1.25rem; color: #4b5563; font-style: italic; margin: 1.25rem 0; }
.blog-prose pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-family: ui-monospace, monospace; font-size: 0.875rem; margin: 1rem 0; }
.blog-prose code { background: #f3f4f6; color: #111827; padding: 0.1rem 0.3rem; border-radius: 0.25rem; font-family: ui-monospace, monospace; font-size: 0.875em; }
.blog-prose pre code { background: transparent; color: inherit; padding: 0; }
.blog-prose a { color: #2563eb; text-decoration: underline; }
.blog-prose hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
.blog-prose strong { font-weight: 600; }
</style>

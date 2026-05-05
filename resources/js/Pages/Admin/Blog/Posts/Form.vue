<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue'
import BlogPostBody from '@/Components/Shop/BlogPostBody.vue'

interface BlogCategory { id: string; name: string; color: string }
interface BlogPost {
    id: string
    title: string
    excerpt: string | null
    body_html: string
    blog_category_id: string | null
    published_at: string | null
    active: boolean
    cover_url: string | null
}

const props = defineProps<{
    post: BlogPost | null
    categories: BlogCategory[]
}>()

const isEdit = computed(() => !!props.post)
const draftKey = computed(() => `blog_draft_${props.post?.id ?? 'new'}`)

const tab = ref<'editor' | 'preview'>('editor')

const form = useForm({
    title:            props.post?.title ?? '',
    excerpt:          props.post?.excerpt ?? '',
    body_html:        props.post?.body_html ?? '',
    blog_category_id: props.post?.blog_category_id ?? '',
    published_at:     props.post?.published_at?.slice(0, 16) ?? '',
    active:           props.post?.active ?? true,
    cover:            null as File | null,
})

const coverPreview = ref<string | null>(props.post?.cover_url ?? null)
const lastSavedAt = ref<Date | null>(null)

// ─── Local autosave (formatting + uploaded image URLs persist via HTML) ────────
function loadDraft() {
    try {
        const raw = localStorage.getItem(draftKey.value)
        if (!raw) return
        const draft = JSON.parse(raw)
        if (props.post && draft.savedAt && new Date(draft.savedAt) <= new Date(props.post.updated_at ?? 0)) {
            // Server is newer — discard stale draft
            localStorage.removeItem(draftKey.value)
            return
        }
        if (confirm('Há um rascunho não salvo deste post. Restaurar?')) {
            form.title            = draft.title ?? form.title
            form.excerpt          = draft.excerpt ?? form.excerpt
            form.body_html        = draft.body_html ?? form.body_html
            form.blog_category_id = draft.blog_category_id ?? form.blog_category_id
        } else {
            localStorage.removeItem(draftKey.value)
        }
    } catch { /* corrupt — ignore */ }
}

function saveDraft() {
    localStorage.setItem(draftKey.value, JSON.stringify({
        title:            form.title,
        excerpt:          form.excerpt,
        body_html:        form.body_html,
        blog_category_id: form.blog_category_id,
        savedAt:          new Date().toISOString(),
    }))
    lastSavedAt.value = new Date()
}

let autosaveTimer: ReturnType<typeof setTimeout>
watch(
    () => [form.title, form.excerpt, form.body_html, form.blog_category_id],
    () => {
        clearTimeout(autosaveTimer)
        autosaveTimer = setTimeout(saveDraft, 1500)
    },
    { deep: true },
)

onMounted(() => loadDraft())

function onCoverChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null
    form.cover = file
    if (file) {
        const reader = new FileReader()
        reader.onload = () => coverPreview.value = reader.result as string
        reader.readAsDataURL(file)
    }
}

function submit() {
    const url = isEdit.value
        ? route('admin.blog.posts.update', props.post!.id)
        : route('admin.blog.posts.store')

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            localStorage.removeItem(draftKey.value)
        },
    })
}

function destroy() {
    if (!props.post || !confirm('Remover este post?')) return
    router.delete(route('admin.blog.posts.destroy', props.post.id))
}
</script>

<template>
    <AdminLayout>
        <PageHeader :title="isEdit ? 'Editar post' : 'Novo post'">
            <template #action>
                <button v-if="isEdit" @click="destroy"
                    class="text-sm text-red-600 hover:text-red-700 px-3 py-2">
                    Remover
                </button>
            </template>
        </PageHeader>

        <!-- Tabs -->
        <div class="flex gap-1 mb-5 border-b border-gray-200">
            <button @click="tab = 'editor'"
                :class="['px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors',
                    tab === 'editor' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700']">
                Editor
            </button>
            <button @click="tab = 'preview'"
                :class="['px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors',
                    tab === 'preview' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700']">
                Visualizar
            </button>
            <span v-if="lastSavedAt" class="ml-auto text-xs text-gray-400 self-center">
                Rascunho salvo às {{ lastSavedAt.toLocaleTimeString('pt-BR') }}
            </span>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Editor tab -->
            <div v-show="tab === 'editor'" class="space-y-5">
                <div class="grid lg:grid-cols-3 gap-5">
                    <div class="lg:col-span-2 space-y-5">
                        <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                            <FormField label="Título" :error="form.errors.title" required>
                                <input v-model="form.title" type="text" required maxlength="200"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-gray-400" />
                            </FormField>

                            <FormField label="Resumo (excerpt)" :error="form.errors.excerpt">
                                <textarea v-model="form.excerpt" maxlength="500" rows="2"
                                    placeholder="Frase curta que aparece no card e no SEO."
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></textarea>
                            </FormField>
                        </div>

                        <FormField label="Conteúdo" :error="form.errors.body_html" required>
                            <RichTextEditor v-model="form.body_html" />
                            <p class="text-xs text-gray-400 mt-1">
                                Cole imagens diretamente (Ctrl+V) ou arraste do seu computador. O upload acontece automaticamente.
                            </p>
                        </FormField>
                    </div>

                    <aside class="space-y-5">
                        <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-4">
                            <FormField label="Imagem de capa" :error="form.errors.cover" :required="!isEdit">
                                <div v-if="coverPreview" class="aspect-[16/9] rounded-xl overflow-hidden bg-gray-100 mb-2">
                                    <img :src="coverPreview" alt="" class="w-full h-full object-cover" />
                                </div>
                                <input type="file" accept="image/*" @change="onCoverChange"
                                    :required="!isEdit"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" />
                            </FormField>

                            <FormField label="Categoria" :error="form.errors.blog_category_id">
                                <select v-model="form.blog_category_id"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                    <option value="">Sem categoria</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </FormField>

                            <FormField label="Publicar em" :error="form.errors.published_at">
                                <input v-model="form.published_at" type="datetime-local"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" />
                                <p class="text-xs text-gray-400 mt-1">Deixe em branco para guardar como rascunho.</p>
                            </FormField>

                            <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                                <input v-model="form.active" type="checkbox" class="rounded" />
                                <span>Ativo</span>
                            </label>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- Preview tab — same component used on the public blog page -->
            <div v-show="tab === 'preview'" class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div v-if="coverPreview" class="aspect-[21/9] bg-gray-100">
                    <img :src="coverPreview" :alt="form.title" class="w-full h-full object-cover" />
                </div>
                <article class="max-w-3xl mx-auto px-6 py-10">
                    <p v-if="form.blog_category_id" class="mb-3">
                        <span :style="{
                            backgroundColor: (categories.find(c => c.id === form.blog_category_id)?.color ?? '#2563eb') + '22',
                            color: categories.find(c => c.id === form.blog_category_id)?.color ?? '#2563eb',
                        }"
                            class="inline-block text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md">
                            {{ categories.find(c => c.id === form.blog_category_id)?.name }}
                        </span>
                    </p>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">{{ form.title || '(sem título)' }}</h1>
                    <p v-if="form.excerpt" class="text-lg text-gray-500 mb-6">{{ form.excerpt }}</p>
                    <BlogPostBody :html="form.body_html" />
                </article>
            </div>

            <div class="flex justify-end gap-3">
                <PrimaryButton type="submit" :loading="form.processing">
                    {{ isEdit ? 'Salvar alterações' : 'Criar post' }}
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>

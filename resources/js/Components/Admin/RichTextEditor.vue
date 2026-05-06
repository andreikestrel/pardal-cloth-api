<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import { route } from 'ziggy-js'

const props = defineProps<{
    modelValue: string
    placeholder?: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const uploading = ref(false)

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3] },
            link: false,
        }),
        Image.configure({ inline: false, allowBase64: false }),
        Link.configure({
            openOnClick: false,
            autolink: true,
            HTMLAttributes: { rel: 'noopener noreferrer', target: '_blank' },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose-base max-w-none focus:outline-none min-h-[400px] px-4 py-3',
        },
        handlePaste: (_view, event) => {
            const files = Array.from(event.clipboardData?.files ?? [])
                .filter(f => f.type.startsWith('image/'))
            if (!files.length) return false
            event.preventDefault()
            files.forEach(uploadAndInsert)
            return true
        },
        handleDrop: (_view, event) => {
            const files = Array.from((event as DragEvent).dataTransfer?.files ?? [])
                .filter(f => f.type.startsWith('image/'))
            if (!files.length) return false
            event.preventDefault()
            files.forEach(uploadAndInsert)
            return true
        },
    },
    onUpdate: ({ editor }) => emit('update:modelValue', editor.getHTML()),
})

watch(() => props.modelValue, (val) => {
    if (editor.value && editor.value.getHTML() !== val) {
        editor.value.commands.setContent(val, { emitUpdate: false })
    }
})

onBeforeUnmount(() => editor.value?.destroy())

function csrf(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
}

async function uploadAndInsert(file: File) {
    const fd = new FormData()
    fd.append('image', file)

    uploading.value = true
    try {
        const res = await fetch(route('admin.blog.upload-image'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            credentials: 'same-origin',
        })
        if (!res.ok) throw new Error('upload failed')
        const { url } = await res.json()
        editor.value?.chain().focus().setImage({ src: url, alt: file.name }).run()
    } catch (e) {
        console.error(e)
        alert('Falha ao enviar imagem.')
    } finally {
        uploading.value = false
    }
}

async function pickAndUpload() {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/*'
    input.onchange = () => input.files?.[0] && uploadAndInsert(input.files[0])
    input.click()
}

function setLink() {
    const url = prompt('URL do link:', editor.value?.getAttributes('link').href ?? 'https://')
    if (url === null) return
    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run()
        return
    }
    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

const buttons = [
    { icon: 'B',  label: 'Negrito',     action: () => editor.value?.chain().focus().toggleBold().run(),       active: () => editor.value?.isActive('bold') },
    { icon: 'I',  label: 'Itálico',     action: () => editor.value?.chain().focus().toggleItalic().run(),     active: () => editor.value?.isActive('italic') },
    { icon: 'S',  label: 'Riscado',     action: () => editor.value?.chain().focus().toggleStrike().run(),     active: () => editor.value?.isActive('strike') },
    { icon: 'H2', label: 'Título 2',    action: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run(), active: () => editor.value?.isActive('heading', { level: 2 }) },
    { icon: 'H3', label: 'Título 3',    action: () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run(), active: () => editor.value?.isActive('heading', { level: 3 }) },
    { icon: '•',  label: 'Lista',       action: () => editor.value?.chain().focus().toggleBulletList().run(), active: () => editor.value?.isActive('bulletList') },
    { icon: '1.', label: 'Lista num.',  action: () => editor.value?.chain().focus().toggleOrderedList().run(),active: () => editor.value?.isActive('orderedList') },
    { icon: '“',  label: 'Citação',     action: () => editor.value?.chain().focus().toggleBlockquote().run(), active: () => editor.value?.isActive('blockquote') },
    { icon: '</>', label: 'Código',     action: () => editor.value?.chain().focus().toggleCodeBlock().run(),  active: () => editor.value?.isActive('codeBlock') },
]
</script>

<template>
    <div class="border border-gray-300 rounded-xl bg-white overflow-hidden">
        <div class="flex flex-wrap items-center gap-1 px-2 py-2 border-b border-gray-200 bg-gray-50">
            <button v-for="b in buttons" :key="b.label" type="button" @click="b.action" :title="b.label"
                :class="['w-8 h-8 rounded-lg text-sm font-semibold flex items-center justify-center transition-colors',
                    b.active?.() ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200']">
                {{ b.icon }}
            </button>

            <button type="button" @click="setLink" title="Link"
                class="w-8 h-8 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200 flex items-center justify-center">
                🔗
            </button>

            <button type="button" @click="pickAndUpload" title="Inserir imagem"
                class="w-8 h-8 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-200 flex items-center justify-center">
                🖼
            </button>

            <span v-if="uploading" class="ml-auto text-xs text-gray-500 px-2">Enviando imagem…</span>
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>

<style>
.ProseMirror img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; }
.ProseMirror h2 { font-size: 1.5rem; font-weight: 700; margin: 1.25rem 0 0.5rem; }
.ProseMirror h3 { font-size: 1.25rem; font-weight: 600; margin: 1rem 0 0.5rem; }
.ProseMirror p { margin: 0.5rem 0; line-height: 1.7; }
.ProseMirror ul { list-style: disc; padding-left: 1.5rem; }
.ProseMirror ol { list-style: decimal; padding-left: 1.5rem; }
.ProseMirror blockquote { border-left: 3px solid #d1d5db; padding-left: 1rem; color: #4b5563; font-style: italic; margin: 1rem 0; }
.ProseMirror pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-family: ui-monospace, monospace; font-size: 0.875rem; }
.ProseMirror a { color: #2563eb; text-decoration: underline; }
</style>

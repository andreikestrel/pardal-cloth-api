<script setup lang="ts">
import { ref, computed } from 'vue'

interface Tag { id: number; name: string; color: string }

const props = defineProps<{
    modelValue: number[]
    tags: Tag[]
}>()

const emit = defineEmits<{
    'update:modelValue': [value: number[]]
}>()

const search = ref('')

const filtered = computed(() =>
    search.value.trim()
        ? props.tags.filter(t =>
            t.name.toLowerCase().includes(search.value.toLowerCase()) &&
            !props.modelValue.includes(t.id)
        )
        : props.tags.filter(t => !props.modelValue.includes(t.id))
)

const selected = computed(() => props.tags.filter(t => props.modelValue.includes(t.id)))

function addTag(tag: Tag) {
    emit('update:modelValue', [...props.modelValue, tag.id])
    search.value = ''
}

function removeTag(id: number) {
    emit('update:modelValue', props.modelValue.filter(x => x !== id))
}
</script>

<template>
    <div>
        <!-- Selected chips -->
        <div v-if="selected.length" class="flex flex-wrap gap-1.5 mb-2">
            <span
                v-for="tag in selected" :key="tag.id"
                :style="{ backgroundColor: tag.color + '22', color: tag.color }"
                class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md">
                {{ tag.name }}
                <button type="button" @click="removeTag(tag.id)" class="ml-0.5 leading-none hover:opacity-70">×</button>
            </span>
        </div>

        <!-- Search input -->
        <input
            v-model="search"
            type="text"
            placeholder="Buscar tag..."
            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />

        <!-- Suggestion dropdown -->
        <div v-if="filtered.length && search.trim()" class="mt-1 border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden max-h-40 overflow-y-auto">
            <button
                v-for="tag in filtered" :key="tag.id"
                type="button"
                @click="addTag(tag)"
                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-2">
                <span :style="{ backgroundColor: tag.color }" class="w-2.5 h-2.5 rounded-full flex-shrink-0"></span>
                {{ tag.name }}
            </button>
        </div>

        <!-- All tags when search is empty -->
        <div v-else-if="!search.trim() && filtered.length" class="flex flex-wrap gap-1.5 mt-2">
            <button
                v-for="tag in filtered" :key="tag.id"
                type="button"
                @click="addTag(tag)"
                :style="{ borderColor: tag.color + '88', color: tag.color }"
                class="text-xs font-medium px-2.5 py-1 rounded-md border bg-white hover:opacity-80 transition-opacity">
                + {{ tag.name }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    headers: string[]
    empty?: string
}>()
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th v-for="header in headers" :key="header"
                        class="text-left px-4 py-3 font-medium text-gray-600 text-xs uppercase tracking-wide">
                        {{ header }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <slot />
                <tr v-if="!$slots.default">
                    <td :colspan="headers.length" class="px-4 py-8 text-center text-gray-400 text-sm">
                        {{ empty ?? 'Nenhum registro encontrado.' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

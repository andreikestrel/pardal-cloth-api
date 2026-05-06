<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

interface Register {
    id: string
    name: string
    location: string | null
    active: boolean
    sessions_count: number
}

const props = defineProps<{ registers: Register[] }>()

// Create modal
const creating = ref(false)
const createForm = reactive({ name: '', location: '' })
const createError = ref('')

function openCreate() {
    createForm.name = ''
    createForm.location = ''
    createError.value = ''
    creating.value = true
}

function submitCreate() {
    router.post(route('admin.pdv.registers.store'), createForm, {
        onSuccess: () => { creating.value = false },
        onError: (errors) => { createError.value = Object.values(errors).join(' ') },
    })
}

// Edit modal
const editing = ref<Register | null>(null)
const editForm = reactive({ name: '', location: '', active: true })
const editError = ref('')

function openEdit(reg: Register) {
    editForm.name = reg.name
    editForm.location = reg.location ?? ''
    editForm.active = reg.active
    editError.value = ''
    editing.value = reg
}

function submitEdit() {
    if (!editing.value) return
    router.put(route('admin.pdv.registers.update', editing.value.id), editForm, {
        onSuccess: () => { editing.value = null },
        onError: (errors) => { editError.value = Object.values(errors).join(' ') },
    })
}

// Delete
function destroy(reg: Register) {
    if (!confirm(`Remover "${reg.name}"? Esta ação não pode ser desfeita.`)) return
    router.delete(route('admin.pdv.registers.destroy', reg.id))
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Caixas" subtitle="Gerencie os terminais disponíveis para o PDV.">
            <template #action>
                <button
                    @click="openCreate"
                    class="text-sm text-white px-4 py-2 rounded-lg hover:opacity-90"
                    :style="{ backgroundColor: 'var(--color-primary)' }"
                >
                    Novo caixa
                </button>
            </template>
        </PageHeader>

        <div class="flex items-center gap-3 mb-6">
            <a :href="route('admin.pdv.index')"
                class="text-sm text-gray-500 hover:text-gray-800"
            >← Voltar ao PDV</a>
        </div>

        <!-- Registers table -->
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Nome</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-600">Localização</th>
                        <th class="text-center px-5 py-3 font-medium text-gray-600">Sessões</th>
                        <th class="text-center px-5 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="reg in registers" :key="reg.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ reg.name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ reg.location ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-500">{{ reg.sessions_count }}</td>
                        <td class="px-5 py-3 text-center">
                            <span :class="['text-xs font-medium px-2.5 py-1 rounded-full',
                                reg.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                {{ reg.active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="openEdit(reg)"
                                    class="text-xs text-blue-600 hover:underline">
                                    Editar
                                </button>
                                <button
                                    v-if="reg.sessions_count === 0"
                                    @click="destroy(reg)"
                                    class="text-xs text-red-500 hover:underline">
                                    Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!registers.length">
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">
                            Nenhum caixa cadastrado.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create modal -->
        <transition name="fade">
            <div v-if="creating" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="creating = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
                    <h2 class="font-semibold text-gray-900 mb-4">Novo caixa</h2>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input v-model="createForm.name" type="text" placeholder="Ex: Terminal 01"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-gray-400" />

                    <label class="block text-sm font-medium text-gray-700 mb-1">Localização (opcional)</label>
                    <input v-model="createForm.location" type="text" placeholder="Ex: Loja principal"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-gray-400" />

                    <p v-if="createError" class="text-xs text-red-600 mt-1 mb-2">{{ createError }}</p>

                    <div class="flex gap-3 mt-4">
                        <button @click="creating = false"
                            class="flex-1 text-sm border border-gray-300 rounded-xl py-2 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button @click="submitCreate"
                            class="flex-1 text-sm text-white rounded-xl py-2 hover:opacity-90"
                            :style="{ backgroundColor: 'var(--color-primary)' }">
                            Criar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Edit modal -->
        <transition name="fade">
            <div v-if="editing" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="editing = null"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
                    <h2 class="font-semibold text-gray-900 mb-4">Editar caixa</h2>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input v-model="editForm.name" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-gray-400" />

                    <label class="block text-sm font-medium text-gray-700 mb-1">Localização (opcional)</label>
                    <input v-model="editForm.location" type="text"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-gray-400" />

                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input v-model="editForm.active" type="checkbox"
                            class="w-4 h-4 rounded border-gray-300" />
                        Ativo
                    </label>

                    <p v-if="editError" class="text-xs text-red-600 mt-2">{{ editError }}</p>

                    <div class="flex gap-3 mt-4">
                        <button @click="editing = null"
                            class="flex-1 text-sm border border-gray-300 rounded-xl py-2 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button @click="submitEdit"
                            class="flex-1 text-sm text-white rounded-xl py-2 hover:opacity-90"
                            :style="{ backgroundColor: 'var(--color-primary)' }">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

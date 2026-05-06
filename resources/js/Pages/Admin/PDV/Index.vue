<script setup lang="ts">
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import type { PageProps } from '@/types'

interface Register {
    id: string
    name: string
    location: string | null
    active_session: { id: string; operator: string; opened_at: string } | null
}

interface MySession {
    id: string
    register: string
}

const props = defineProps<{
    registers: Register[]
    mySession: MySession | null
}>()

const page = usePage<PageProps>()
const settings = page.props.settings

const opening = ref(false)
const selectedRegister = ref<string>('')
const openingBalance = ref('0.00')
const openingError = ref('')

function openModal(registerId: string) {
    selectedRegister.value = registerId
    openingBalance.value = '0.00'
    openingError.value = ''
    opening.value = true
}

function submitOpen() {
    router.post(route('admin.pdv.sessions.open'), {
        cash_register_id: selectedRegister.value,
        opening_balance: openingBalance.value,
    }, {
        onError: (errors) => {
            openingError.value = Object.values(errors).join(' ')
        },
    })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="PDV — Caixas" subtitle="Selecione um caixa para iniciar ou retomar uma sessão.">
            <template #action>
                <a :href="route('admin.pdv.registers.index')"
                    class="text-sm text-gray-500 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50">
                    Gerenciar caixas
                </a>
            </template>
        </PageHeader>

        <!-- Resume active session banner -->
        <div v-if="mySession"
            class="mb-6 flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-5 py-4">
            <div>
                <p class="text-sm font-semibold text-green-800">Você tem uma sessão aberta em {{ mySession.register }}</p>
                <p class="text-xs text-green-600 mt-0.5">Clique em Retomar para acessar o terminal.</p>
            </div>
            <a :href="route('admin.pdv.terminal')"
                class="text-sm font-medium text-white px-4 py-2 rounded-lg"
                :style="{ backgroundColor: 'var(--color-primary)' }">
                Retomar terminal
            </a>
        </div>

        <!-- Register grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="reg in registers" :key="reg.id"
                class="bg-white border border-gray-200 rounded-2xl p-5 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ reg.name }}</p>
                        <p v-if="reg.location" class="text-xs text-gray-400 mt-0.5">{{ reg.location }}</p>
                    </div>
                    <span :class="['text-xs font-medium px-2.5 py-1 rounded-full',
                        reg.active_session ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700']">
                        {{ reg.active_session ? 'Em uso' : 'Livre' }}
                    </span>
                </div>

                <p v-if="reg.active_session" class="text-xs text-gray-500">
                    Operador: <span class="font-medium">{{ reg.active_session.operator }}</span>
                </p>

                <button
                    :disabled="!!reg.active_session && !mySession"
                    @click="openModal(reg.id)"
                    :class="['w-full py-2 rounded-xl text-sm font-medium transition-colors',
                        reg.active_session
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'text-white hover:opacity-90']"
                    :style="!reg.active_session ? { backgroundColor: 'var(--color-primary)' } : {}">
                    {{ reg.active_session ? 'Caixa ocupado' : 'Abrir caixa' }}
                </button>
            </div>

            <div v-if="!registers.length"
                class="col-span-full text-center py-16 text-sm text-gray-400">
                Nenhum caixa cadastrado. Adicione registradoras em configurações.
            </div>
        </div>

        <!-- Open session modal -->
        <transition name="fade">
            <div v-if="opening" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="opening = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
                    <h2 class="font-semibold text-gray-900 mb-4">Abrir caixa</h2>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Saldo inicial (R$)</label>
                    <input v-model="openingBalance" type="number" min="0" step="0.01"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-gray-400" />

                    <p v-if="openingError" class="text-xs text-red-600 mb-3">{{ openingError }}</p>

                    <div class="flex gap-3 mt-4">
                        <button @click="opening = false"
                            class="flex-1 text-sm border border-gray-300 rounded-xl py-2 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button @click="submitOpen"
                            class="flex-1 text-sm text-white rounded-xl py-2 hover:opacity-90"
                            :style="{ backgroundColor: 'var(--color-primary)' }">
                            Abrir
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

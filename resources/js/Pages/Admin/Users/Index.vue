<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import DataTable from '@/Components/Admin/DataTable.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

interface AdminUser {
    id: string
    name: string
    email: string
    role: string | null
    active: boolean
    created_at: string | null
}

defineProps<{ users: AdminUser[] }>()

const headers = ['Nome', 'E-mail', 'Papel', 'Status', 'Cadastrado em', 'Ações']

const showInvite = ref(false)

const form = useForm({
    name:  '',
    email: '',
    role:  'admin' as 'admin' | 'customer',
})

function submit() {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            showInvite.value = false
        },
    })
}

function resend(user: AdminUser) {
    router.post(route('admin.users.resend', user.id), {}, { preserveScroll: true })
}

function destroy(user: AdminUser) {
    if (!confirm(`Remover ${user.name}?`)) return
    router.delete(route('admin.users.destroy', user.id), { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Usuários">
            <template #action>
                <button @click="showInvite = !showInvite"
                    class="text-sm text-white px-4 py-2 rounded-xl font-medium transition-opacity hover:opacity-90"
                    style="background-color: var(--color-primary)">
                    {{ showInvite ? 'Cancelar' : '+ Convidar usuário' }}
                </button>
            </template>
        </PageHeader>

        <div v-if="showInvite" class="bg-white border border-gray-200 rounded-2xl p-6 mb-6 max-w-2xl">
            <h2 class="font-medium text-gray-900 mb-4">Novo convite</h2>
            <p class="text-sm text-gray-500 mb-5">
                O usuário receberá um e-mail com um link para definir a senha. O link expira em 24 horas.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <FormField label="Nome" :error="form.errors.name" required>
                        <input v-model="form.name" type="text" required
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>

                    <FormField label="E-mail" :error="form.errors.email" required>
                        <input v-model="form.email" type="email" required
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>
                </div>

                <FormField label="Papel" :error="form.errors.role" required>
                    <select v-model="form.role" required
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <option value="admin">Administrador</option>
                        <option value="customer">Cliente</option>
                    </select>
                </FormField>

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :loading="form.processing">
                        Enviar convite
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <DataTable :headers="headers">
            <tr v-for="u in users" :key="u.id" class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ u.name }}</td>
                <td class="px-5 py-3 text-sm text-gray-600">{{ u.email }}</td>
                <td class="px-5 py-3 text-sm text-gray-600 capitalize">{{ u.role ?? '—' }}</td>
                <td class="px-5 py-3">
                    <span :class="['text-xs font-medium px-2 py-1 rounded-lg',
                        u.active ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700']">
                        {{ u.active ? 'Ativo' : 'Convite pendente' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500">{{ u.created_at }}</td>
                <td class="px-5 py-3 text-sm">
                    <div class="flex gap-3">
                        <button v-if="!u.active" @click="resend(u)"
                            class="text-gray-600 hover:text-gray-900 text-xs">
                            Reenviar
                        </button>
                        <button @click="destroy(u)" class="text-red-600 hover:text-red-700 text-xs">
                            Remover
                        </button>
                    </div>
                </td>
            </tr>
            <tr v-if="!users.length">
                <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">
                    Nenhum usuário cadastrado.
                </td>
            </tr>
        </DataTable>
    </AdminLayout>
</template>

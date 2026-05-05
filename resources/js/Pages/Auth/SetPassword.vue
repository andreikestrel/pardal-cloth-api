<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps<{ token: string, email: string }>()

const form = useForm({
    token:                 props.token,
    email:                 props.email,
    password:              '',
    password_confirmation: '',
})

function submit() {
    form.post(route('password.set.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <AuthLayout>
        <h1 class="text-xl font-semibold mb-2 text-gray-900">Defina sua senha</h1>
        <p class="text-sm text-gray-500 mb-6">
            Bem-vindo(a)! Crie uma senha para acessar sua conta <strong>{{ form.email }}</strong>.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
                <input v-model="form.password" type="password" required autocomplete="new-password" minlength="6"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900" />
                <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirme a senha</label>
                <input v-model="form.password_confirmation" type="password" required autocomplete="new-password" minlength="6"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900" />
            </div>

            <p v-if="form.errors.email" class="text-red-600 text-xs">{{ form.errors.email }}</p>

            <button type="submit" :disabled="form.processing"
                class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-700 disabled:opacity-50">
                Definir senha e entrar
            </button>
        </form>
    </AuthLayout>
</template>

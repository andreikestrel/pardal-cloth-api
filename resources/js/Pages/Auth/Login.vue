<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login.store'), { onFinish: () => form.reset('password') })
}
</script>

<template>
    <AuthLayout>
        <h1 class="text-xl font-semibold mb-6 text-gray-900">Entrar na sua conta</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input v-model="form.email" type="email" required autocomplete="email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900" />
                <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input v-model="form.password" type="password" required autocomplete="current-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900" />
                <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input v-model="form.remember" type="checkbox" class="rounded" />
                Lembrar-me
            </label>

            <button type="submit" :disabled="form.processing"
                class="w-full bg-gray-900 text-white py-2 rounded-lg text-sm font-medium hover:bg-gray-700 disabled:opacity-50">
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Não tem conta?
            <a :href="route('register')" class="text-gray-900 font-medium hover:underline">Cadastre-se</a>
        </p>
    </AuthLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import SettingsTabs from '@/Components/Admin/SettingsTabs.vue'
import FormField from '@/Components/UI/FormField.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'
import type { Settings } from '@/types'

const props = defineProps<{ settings: Settings }>()

const form = useForm({
    company_name:    props.settings.company_name,
    primary_color:   props.settings.primary_color,
    secondary_color: props.settings.secondary_color,
    accent_color:    props.settings.accent_color,
    blog_enabled:    !!(props.settings as Settings & { blog_enabled?: boolean }).blog_enabled,
    logo:            null as File | null,
})

function onLogoChange(e: Event) {
    form.logo = (e.target as HTMLInputElement).files?.[0] ?? null
}

function submit() {
    form.post(route('admin.settings.general.update'), { forceFormData: true })
}
</script>

<template>
    <AdminLayout>
        <PageHeader title="Configurações" />
        <SettingsTabs />

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                    <h2 class="font-medium text-gray-900">Identidade da loja</h2>

                    <FormField label="Nome da empresa" :error="form.errors.company_name" required>
                        <input v-model="form.company_name" type="text" required
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                    </FormField>

                    <FormField label="Logo" :error="form.errors.logo">
                        <div class="flex items-center gap-4">
                            <img v-if="settings.logo" :src="settings.logo" alt="Logo"
                                class="h-12 rounded-lg object-contain border border-gray-200 p-1" />
                            <input type="file" accept="image/*" @change="onLogoChange"
                                class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm" />
                        </div>
                    </FormField>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                    <h2 class="font-medium text-gray-900">Cores</h2>

                    <div class="grid sm:grid-cols-3 gap-5">
                        <FormField label="Cor primária" :error="form.errors.primary_color">
                            <div class="flex items-center gap-2">
                                <input v-model="form.primary_color" type="color"
                                    class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
                                <input v-model="form.primary_color" type="text"
                                    class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                            </div>
                        </FormField>

                        <FormField label="Cor secundária" :error="form.errors.secondary_color">
                            <div class="flex items-center gap-2">
                                <input v-model="form.secondary_color" type="color"
                                    class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
                                <input v-model="form.secondary_color" type="text"
                                    class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                            </div>
                        </FormField>

                        <FormField label="Cor de destaque" :error="form.errors.accent_color">
                            <div class="flex items-center gap-2">
                                <input v-model="form.accent_color" type="color"
                                    class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5" />
                                <input v-model="form.accent_color" type="text"
                                    class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-400" />
                            </div>
                        </FormField>
                    </div>

                    <!-- Preview -->
                    <div class="flex gap-3 pt-2">
                        <div class="h-8 w-24 rounded-lg text-white text-xs flex items-center justify-center font-medium"
                            :style="{ backgroundColor: form.primary_color }">
                            Primária
                        </div>
                        <div class="h-8 w-24 rounded-lg text-white text-xs flex items-center justify-center font-medium"
                            :style="{ backgroundColor: form.secondary_color }">
                            Secundária
                        </div>
                        <div class="h-8 w-24 rounded-lg text-white text-xs flex items-center justify-center font-medium"
                            :style="{ backgroundColor: form.accent_color }">
                            Destaque
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-3">
                    <h2 class="font-medium text-gray-900 mb-2">Módulos</h2>
                    <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                        <input v-model="form.blog_enabled" type="checkbox" class="rounded" />
                        <span>
                            <strong>Blog</strong>
                            <span class="text-gray-500 block text-xs">Quando ligado, /blog fica acessível e o link aparece no menu da loja.</span>
                        </span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :loading="form.processing">
                        Salvar configurações
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

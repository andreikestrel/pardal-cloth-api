<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Settings } from '@/types'

defineProps<{ settings: Settings }>()

const paymentMethods = [
    { name: 'Pix', description: 'Pagamento instantâneo, aprovação na hora', icon: '⚡' },
    { name: 'Boleto Bancário', description: 'Vencimento em até 3 dias úteis', icon: '📄' },
    { name: 'Cartão de Crédito', description: 'Via Mercado Pago ou Asaas', icon: '💳' },
]

const openingHours = [
    { days: 'Segunda a Sexta', hours: '09h às 18h' },
    { days: 'Sábado', hours: '09h às 13h' },
    { days: 'Domingo e Feriados', hours: 'Fechado' },
]
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">Sobre a {{ settings.company_name }}</h1>
                <p class="text-gray-500 max-w-xl mx-auto">
                    Moda com qualidade e estilo para o seu guarda-roupa. Conheça nossa loja e como funcionamos.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Store info -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h2 class="font-semibold text-gray-900 mb-4 text-lg">Nossa loja</h2>

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex gap-3">
                            <span class="text-xl">📍</span>
                            <div>
                                <p class="font-medium text-gray-800">Endereço</p>
                                <p>Rua das Flores, 123 — Centro</p>
                                <p>São Paulo, SP — CEP 01310-000</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <span class="text-xl">📞</span>
                            <div>
                                <p class="font-medium text-gray-800">Telefone</p>
                                <p>(11) 99999-0000</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <span class="text-xl">✉️</span>
                            <div>
                                <p class="font-medium text-gray-800">E-mail</p>
                                <p>contato@pardalcloth.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opening hours -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h2 class="font-semibold text-gray-900 mb-4 text-lg">Horário de funcionamento</h2>

                    <div class="space-y-3">
                        <div v-for="item in openingHours" :key="item.days"
                            class="flex justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600">{{ item.days }}</span>
                            <span class="font-medium text-gray-900">{{ item.hours }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment methods -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 md:col-span-2">
                    <h2 class="font-semibold text-gray-900 mb-5 text-lg">Formas de pagamento</h2>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div v-for="method in paymentMethods" :key="method.name"
                            class="border border-gray-100 rounded-xl p-4 bg-gray-50">
                            <p class="text-2xl mb-2">{{ method.icon }}</p>
                            <p class="font-medium text-gray-900 text-sm">{{ method.name }}</p>
                            <p class="text-gray-500 text-xs mt-1">{{ method.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Map placeholder -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden md:col-span-2">
                    <div class="bg-gray-100 h-56 flex items-center justify-center text-gray-400 text-sm">
                        📍 Mapa — integração com Google Maps a configurar
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

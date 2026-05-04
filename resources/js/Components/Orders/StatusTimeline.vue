<script setup lang="ts">
import type { OrderStatus, OrderStatusHistory } from '@/types'

defineProps<{
    currentStatus: OrderStatus
    history: OrderStatusHistory[]
}>()

const steps: { status: OrderStatus; label: string }[] = [
    { status: 'pending',   label: 'Aguardando' },
    { status: 'confirmed', label: 'Confirmado' },
    { status: 'preparing', label: 'Preparando' },
    { status: 'shipped',   label: 'Enviado' },
    { status: 'delivered', label: 'Entregue' },
]

const ORDER: OrderStatus[] = ['pending', 'confirmed', 'preparing', 'shipped', 'delivered']

function stepState(status: OrderStatus, current: OrderStatus): 'done' | 'current' | 'upcoming' {
    if (current === 'cancelled') return status === 'pending' ? 'done' : 'upcoming'
    const ci = ORDER.indexOf(current)
    const si = ORDER.indexOf(status)
    if (si < ci) return 'done'
    if (si === ci) return 'current'
    return 'upcoming'
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(date))
}
</script>

<template>
    <div>
        <!-- Cancelled banner -->
        <div v-if="currentStatus === 'cancelled'"
            class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            Pedido cancelado
        </div>

        <!-- Progress steps -->
        <div v-else class="flex items-center gap-0 mb-6 overflow-x-auto pb-2">
            <template v-for="(step, i) in steps" :key="step.status">
                <div class="flex flex-col items-center shrink-0">
                    <div :class="[
                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors',
                        stepState(step.status, currentStatus) === 'done'    ? 'bg-green-500 border-green-500 text-white' : '',
                        stepState(step.status, currentStatus) === 'current' ? 'border-current text-current bg-white' : '',
                        stepState(step.status, currentStatus) === 'upcoming'? 'border-gray-300 text-gray-300 bg-white' : '',
                    ]" :style="stepState(step.status, currentStatus) === 'current' ? { borderColor: 'var(--color-primary)', color: 'var(--color-primary)' } : {}">
                        <span v-if="stepState(step.status, currentStatus) === 'done'">✓</span>
                        <span v-else>{{ i + 1 }}</span>
                    </div>
                    <p :class="['text-xs mt-1.5 text-center whitespace-nowrap',
                        stepState(step.status, currentStatus) === 'upcoming' ? 'text-gray-400' : 'text-gray-700']">
                        {{ step.label }}
                    </p>
                </div>
                <div v-if="i < steps.length - 1"
                    :class="['flex-1 h-0.5 mx-1', stepState(steps[i + 1].status, currentStatus) !== 'upcoming' ? 'bg-green-400' : 'bg-gray-200']" />
            </template>
        </div>

        <!-- History log -->
        <div v-if="history.length" class="space-y-2 mt-4">
            <div v-for="entry in [...history].reverse()" :key="entry.id"
                class="flex gap-3 text-sm text-gray-600">
                <span class="text-gray-400 shrink-0 text-xs pt-0.5">{{ formatDate(entry.created_at) }}</span>
                <div>
                    <span class="font-medium text-gray-800">{{ entry.status }}</span>
                    <span v-if="entry.note" class="ml-2 text-gray-500">— {{ entry.note }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

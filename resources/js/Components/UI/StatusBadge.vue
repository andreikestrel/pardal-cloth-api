<script setup lang="ts">
import type { OrderStatus } from '@/types'

defineProps<{ status: OrderStatus }>()

const config: Record<OrderStatus, { label: string; class: string }> = {
    pending:   { label: 'Aguardando', class: 'bg-gray-100 text-gray-600' },
    confirmed: { label: 'Confirmado', class: 'bg-blue-100 text-blue-700' },
    preparing: { label: 'Preparando', class: 'bg-amber-100 text-amber-700' },
    shipped:   { label: 'Enviado',    class: 'bg-purple-100 text-purple-700' },
    delivered: { label: 'Entregue',   class: 'bg-green-100 text-green-700' },
    cancelled: { label: 'Cancelado',  class: 'bg-red-100 text-red-600' },
}
</script>

<template>
    <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', config[status]?.class ?? 'bg-gray-100 text-gray-600']">
        {{ config[status]?.label ?? status }}
    </span>
</template>

import { ref, onMounted, onUnmounted } from 'vue'
import type { OrderStatus } from '@/types'

interface StatusEvent {
    orderId: string
    status: OrderStatus
    note: string | null
}

const WS_URL = import.meta.env.VITE_WS_URL as string

export function useOrderSocket(orderId: string) {
    const currentStatus = ref<OrderStatus | null>(null)
    const lastNote = ref<string | null>(null)

    // Lazily import socket.io-client so the main bundle stays lean for non-order pages
    let socket: { disconnect: () => void } | null = null

    onMounted(async () => {
        const { io } = await import('socket.io-client')

        socket = io(WS_URL, { transports: ['websocket'] })

        // Join the room scoped to this order so we only receive targeted events
        ;(socket as ReturnType<typeof io>).emit('join:order', orderId)

        ;(socket as ReturnType<typeof io>).on('order:status_updated', (event: StatusEvent) => {
            if (event.orderId !== orderId) return
            currentStatus.value = event.status
            lastNote.value = event.note
        })
    })

    onUnmounted(() => {
        socket?.disconnect()
    })

    return { currentStatus, lastNote }
}

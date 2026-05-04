import { ref, onMounted, onUnmounted } from 'vue'
import type { OrderStatus } from '@/types'

interface StatusEvent {
    status: OrderStatus
    label: string
    note: string | null
    updated_at: string
}

const WS_URL = import.meta.env.VITE_WS_URL as string

/**
 * Joins the Socket.IO room for this order on mount.
 * Disconnects and leaves room on unmount to avoid stale listeners.
 */
export function useOrderSocket(orderId: string) {
    const currentStatus = ref<OrderStatus | null>(null)
    const lastNote = ref<string | null>(null)
    const lastLabel = ref<string | null>(null)

    // Lazily imported so the socket.io bundle is excluded from pages that don't need it
    let socket: Awaited<ReturnType<typeof import('socket.io-client')>>['io'] extends (...a: any[]) => infer R ? R : never | null = null as any

    onMounted(async () => {
        const { io } = await import('socket.io-client')

        socket = io(WS_URL, { transports: ['websocket'] })

        socket.emit('join:order', { order_id: orderId })

        socket.on('order:status_updated', (event: StatusEvent) => {
            currentStatus.value = event.status
            lastNote.value = event.note
            lastLabel.value = event.label
        })
    })

    onUnmounted(() => {
        if (socket) {
            socket.emit('leave:order', { order_id: orderId })
            socket.disconnect()
        }
    })

    return { currentStatus, lastNote, lastLabel }
}

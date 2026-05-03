import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { CartLineItem, CartCalculation } from '@/types'

const STORAGE_KEY = 'pardal_cart'

const items = ref<CartLineItem[]>(loadFromStorage())
const calculation = ref<CartCalculation | null>(null)
const couponCode = ref<string | null>(null)
const isCalculating = ref(false)

function loadFromStorage(): CartLineItem[] {
    try {
        const raw = localStorage.getItem(STORAGE_KEY)
        return raw ? JSON.parse(raw) : []
    } catch {
        return []
    }
}

function persist(): void {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
}

function addItem(variationId: string, quantity = 1): void {
    const existing = items.value.find((i) => i.variation_id === variationId)

    if (existing) {
        existing.quantity += quantity
    } else {
        items.value.push({ variation_id: variationId, quantity })
    }

    persist()
}

function removeItem(variationId: string): void {
    items.value = items.value.filter((i) => i.variation_id !== variationId)
    persist()
}

function updateQuantity(variationId: string, quantity: number): void {
    if (quantity <= 0) {
        removeItem(variationId)
        return
    }

    const item = items.value.find((i) => i.variation_id === variationId)
    if (item) {
        item.quantity = quantity
        persist()
    }
}

function clearCart(): void {
    items.value = []
    calculation.value = null
    couponCode.value = null
    localStorage.removeItem(STORAGE_KEY)
}

async function calculate(): Promise<void> {
    if (items.value.length === 0) {
        calculation.value = null
        return
    }

    isCalculating.value = true

    try {
        const payload: Record<string, unknown> = {
            items: items.value.map((i) => ({ variation_id: i.variation_id, quantity: i.quantity })),
        }

        if (couponCode.value) {
            payload.coupon_code = couponCode.value
        }

        const endpoint = couponCode.value ? route('cart.apply-coupon') : route('cart.calculate')
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        })

        // Add CSRF token from meta tag
        const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content
        if (csrf) {
            (payload as Record<string, unknown>)._token = csrf
        }

        if (res.ok) {
            calculation.value = await res.json()
        }
    } finally {
        isCalculating.value = false
    }
}

async function applyCoupon(code: string): Promise<void> {
    couponCode.value = code
    await calculate()
}

function removeCoupon(): void {
    couponCode.value = null
    calculate()
}

const itemCount = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0))
const isEmpty = computed(() => items.value.length === 0)

export function useCart() {
    return {
        items,
        calculation,
        couponCode,
        isCalculating,
        itemCount,
        isEmpty,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        calculate,
        applyCoupon,
        removeCoupon,
    }
}

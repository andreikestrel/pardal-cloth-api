<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { PageProps } from '@/types'

interface Session {
    id: string
    register: string
    operator: string
    close_url: string
}

interface Variation {
    id: string
    size: string
    color: string
    price: string
    stock: number
    barcode: string | null
    sku: string
}

interface ProductResult {
    id: string
    name: string
    cover_url: string | null
    variations: Variation[]
}

interface CartLine {
    variation_id: string
    product_name: string
    size: string
    color: string
    unit_price: string
    quantity: number
    stock: number
}

const props = defineProps<{ session: Session }>()

const page = usePage<PageProps>()
const companyName = computed(() => page.props.settings?.company_name || 'PDV')

const showCloseConfirm = ref(false)

// — Mode: 'sale' | 'entry' —
const mode = ref<'sale' | 'entry'>('sale')

function switchMode(m: 'sale' | 'entry') {
    if (lines.value.length > 0 && !confirm('Trocar de modo vai limpar os itens. Continuar?')) return
    lines.value = []
    mode.value = m
}

// — Cart state —
const lines          = ref<CartLine[]>([])
const coupon         = ref('')
const couponApplied  = ref<string | null>(null)
const couponDiscount = ref('0.00')
const couponError    = ref('')
const manualDiscount = ref('0.00')
const customerName   = ref('')
const customerDoc    = ref('')
const showCustomer   = ref(false)
const saleCounter    = ref(Math.floor(Math.random() * 9000) + 1000)

// — Search state —
const searchQuery   = ref('')
const searchResults = ref<ProductResult[]>([])
const searching     = ref(false)
const showDropdown  = ref(false)
let searchTimer: ReturnType<typeof setTimeout>

// — Payment modal —
const showPayment = ref(false)
const payMethod   = ref<'pix' | 'credit_card' | 'cash'>('pix')
const amountPaid  = ref('')
const payLoading  = ref(false)
const payError    = ref('')
const receiptUrl  = ref<string | null>(null)
const lastOrderId = ref<string | null>(null)

// — Computed totals —
const subtotal = computed(() =>
    lines.value.reduce((acc, l) => bcadd(acc, bcmul(l.unit_price, String(l.quantity))), '0.00')
)

const totalDiscount = computed(() =>
    bcadd(couponDiscount.value, manualDiscount.value || '0.00')
)

const total = computed(() => {
    const t = bcsub(subtotal.value, totalDiscount.value)
    return parseFloat(t) < 0 ? '0.00' : t
})

const changeDue = computed(() => {
    if (payMethod.value !== 'cash' || !amountPaid.value) return null
    const change = bcsub(amountPaid.value, total.value)
    return parseFloat(change) >= 0 ? change : null
})

// Display-only fixed-point helpers (server always recalculates on finalize)
function bcadd(a: string, b: string, scale = 2): string {
    return (parseFloat(a) + parseFloat(b)).toFixed(scale)
}
function bcmul(a: string, b: string, scale = 2): string {
    return (parseFloat(a) * parseFloat(b)).toFixed(scale)
}
function bcsub(a: string, b: string, scale = 2): string {
    return (parseFloat(a) - parseFloat(b)).toFixed(scale)
}

function fmt(value: string): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(value))
}

// — Product search —
function blurSearch() {
    // Delay hiding so clicks on dropdown chips register before blur closes the list
    setTimeout(() => { showDropdown.value = false }, 200)
}

watch(searchQuery, (q) => {
    clearTimeout(searchTimer)
    if (!q.trim()) {
        searchResults.value = []
        showDropdown.value = false
        return
    }
    searchTimer = setTimeout(() => doSearch(q), 300)
})

async function doSearch(q: string) {
    searching.value = true
    try {
        const params = new URLSearchParams({ q, stock_only: mode.value === 'sale' ? '1' : '0' })
        const res = await fetch(route('admin.pdv.products') + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        searchResults.value = await res.json()
        showDropdown.value = true
    } finally {
        searching.value = false
    }
}

function addVariation(product: ProductResult, variation: Variation) {
    if (mode.value === 'sale' && variation.stock <= 0) return
    const existing = lines.value.find(l => l.variation_id === variation.id)
    if (existing) {
        if (existing.quantity < variation.stock) existing.quantity++
    } else {
        lines.value.push({
            variation_id: variation.id,
            product_name: product.name,
            size:         variation.size,
            color:        variation.color,
            unit_price:   String(variation.price),
            quantity:     1,
            stock:        variation.stock,
        })
    }
    searchQuery.value = ''
    showDropdown.value = false
    couponApplied.value = null
    couponDiscount.value = '0.00'
}

function removeLine(variationId: string) {
    lines.value = lines.value.filter(l => l.variation_id !== variationId)
    couponApplied.value = null
    couponDiscount.value = '0.00'
}

function setQty(variationId: string, qty: number) {
    const line = lines.value.find(l => l.variation_id === variationId)
    if (!line) return
    if (qty < 1) { removeLine(variationId); return }
    if (mode.value === 'sale' && qty > line.stock) return
    line.quantity = qty
}

// — Coupon —
function csrf(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
}

async function applyCoupon() {
    couponError.value = ''
    if (!coupon.value.trim()) return
    try {
        const res = await fetch(route('cart.calculate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                items: lines.value.map(l => ({ variation_id: l.variation_id, quantity: l.quantity })),
                coupon_code: coupon.value,
            }),
        })
        const data = await res.json()
        if (!res.ok) {
            couponError.value = data.errors?.coupon_code?.[0] ?? 'Cupom inválido.'
            return
        }
        couponApplied.value = coupon.value.toUpperCase()
        couponDiscount.value = data.discount_coupon ?? '0.00'
    } catch {
        couponError.value = 'Erro ao validar cupom.'
    }
}

function removeCoupon() {
    coupon.value = ''
    couponApplied.value = null
    couponDiscount.value = '0.00'
    couponError.value = ''
}

// — Finalize —
function openPayment() {
    payError.value = ''
    amountPaid.value = total.value
    receiptUrl.value = null
    lastOrderId.value = null
    showPayment.value = true
}

async function confirmPayment() {
    payError.value = ''
    payLoading.value = true
    try {
        const res = await fetch(route('admin.pdv.finalize'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                items:           lines.value.map(l => ({ variation_id: l.variation_id, quantity: l.quantity })),
                coupon_code:     couponApplied.value ?? undefined,
                manual_discount: parseFloat(manualDiscount.value || '0') > 0 ? manualDiscount.value : undefined,
                payment_method:  payMethod.value,
                amount_paid:     payMethod.value === 'cash' ? amountPaid.value : undefined,
                customer_name:   customerName.value || undefined,
                customer_doc:    customerDoc.value || undefined,
            }),
        })
        const data = await res.json()
        if (!res.ok) {
            payError.value = data.message ?? 'Erro ao finalizar venda.'
            return
        }
        receiptUrl.value = data.receipt_url
        lastOrderId.value = data.order_id
        resetSale()
    } catch {
        payError.value = 'Erro inesperado. Tente novamente.'
    } finally {
        payLoading.value = false
    }
}

function resetSale() {
    lines.value = []
    coupon.value = ''
    couponApplied.value = null
    couponDiscount.value = '0.00'
    couponError.value = ''
    manualDiscount.value = '0.00'
    customerName.value = ''
    customerDoc.value = ''
    saleCounter.value = Math.floor(Math.random() * 9000) + 1000
}

function cancelSale() {
    if (lines.value.length === 0 || confirm('Cancelar a venda atual?')) resetSale()
}

// — Stock entry —
const entryReason  = ref('')
const entryLoading = ref(false)
const entrySuccess = ref(false)
const entryError   = ref('')

async function submitStockEntry() {
    if (lines.value.length === 0) return
    entryError.value = ''
    entryLoading.value = true
    entrySuccess.value = false
    try {
        const res = await fetch(route('admin.pdv.stock-entry'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                items:  lines.value.map(l => ({ variation_id: l.variation_id, quantity: l.quantity })),
                reason: entryReason.value || undefined,
            }),
        })
        const data = await res.json()
        if (!res.ok) {
            entryError.value = data.message ?? 'Erro ao registrar entrada.'
            return
        }
        entrySuccess.value = true
        lines.value = []
        entryReason.value = ''
    } catch {
        entryError.value = 'Erro inesperado. Tente novamente.'
    } finally {
        entryLoading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">

        <!-- Top bar -->
        <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a :href="route('admin.pdv.index')"
                    class="flex items-center gap-1 text-sm text-gray-400 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <span class="text-gray-200">|</span>
                <span class="text-sm font-semibold text-gray-800">{{ companyName }}</span>
            </div>

            <!-- Mode toggle -->
            <div class="flex rounded-xl border border-gray-200 overflow-hidden text-xs font-medium">
                <button @click="switchMode('sale')"
                    :class="['px-4 py-1.5 transition-colors', mode === 'sale' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50']">
                    Venda
                </button>
                <button @click="switchMode('entry')"
                    :class="['px-4 py-1.5 transition-colors border-l border-gray-200', mode === 'entry' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50']">
                    Entrada
                </button>
            </div>
            <button @click="showCloseConfirm = true"
                class="text-sm font-medium text-red-600 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-50 transition-colors">
                Fechar
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 p-6">
        <div class="flex gap-5 items-start max-w-7xl mx-auto">

            <!-- LEFT — Product search + customer + cart -->
            <div class="flex flex-col flex-1 min-w-0 gap-4">

                <!-- Product search -->
                <div class="bg-white border border-gray-200 rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Adicionar produto</p>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por nome ou código de barras"
                            @focus="showDropdown = searchResults.length > 0"
                            @blur="blurSearch"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
                        />
                        <div v-if="searching" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <div class="w-4 h-4 border-2 border-gray-300 border-t-gray-600 rounded-full animate-spin"></div>
                        </div>

                        <!-- Search dropdown -->
                        <div v-if="showDropdown && searchResults.length"
                            class="absolute z-20 top-full mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden max-h-80 overflow-y-auto">
                            <div v-for="product in searchResults" :key="product.id" class="border-b border-gray-100 last:border-0">
                                <div class="px-4 py-2 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-800">{{ product.name }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 p-3">
                                    <button
                                        v-for="v in product.variations" :key="v.id"
                                        @click="addVariation(product, v)"
                                        :disabled="mode === 'sale' && v.stock <= 0"
                                        :class="['flex flex-col items-start px-3 py-2 rounded-lg border text-xs transition-colors',
                                            mode === 'entry' || v.stock > 0
                                                ? 'border-gray-200 hover:border-gray-400 hover:bg-gray-50 cursor-pointer'
                                                : 'border-gray-100 bg-gray-50 opacity-40 cursor-not-allowed']">
                                        <span class="font-medium text-gray-800">{{ v.size }} / {{ v.color }}</span>
                                        <span class="text-gray-500 mt-0.5">
                                            {{ mode === 'sale' ? fmt(String(v.price)) + ' · ' : '' }}{{ v.stock }} un.
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="showDropdown && !searching"
                            class="absolute z-20 top-full mt-1 w-full bg-white border border-gray-200 rounded-xl p-3 text-sm text-gray-400 text-center shadow-xl">
                            Nenhum produto encontrado.
                        </div>
                    </div>
                </div>

                <!-- Customer (collapsible) -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <button @click="showCustomer = !showCustomer"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Dados do cliente
                            <span class="text-gray-400">(opcional)</span>
                        </span>
                        <svg :class="['w-4 h-4 transition-transform text-gray-400', showCustomer ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="showCustomer" class="px-4 pb-4 grid grid-cols-2 gap-3 border-t border-gray-100">
                        <div class="pt-3">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">Nome</label>
                            <input v-model="customerName" type="text" placeholder="Nome do cliente"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300" />
                        </div>
                        <div class="pt-3">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">CPF</label>
                            <input v-model="customerDoc" type="text" placeholder="000.000.000-00"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300" />
                        </div>
                    </div>
                </div>

                <!-- Cart items -->
                <div class="bg-white border border-gray-200 rounded-2xl">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            {{ mode === 'entry' ? 'Itens para entrada' : 'Itens da venda' }}
                        </p>
                        <span class="text-xs font-bold text-white rounded-full px-2 py-0.5"
                            style="background-color: var(--color-primary)">
                            {{ lines.length }} {{ lines.length === 1 ? 'item' : 'itens' }}
                        </span>
                    </div>

                    <div v-if="lines.length === 0"
                        class="flex flex-col items-center justify-center py-14 text-gray-300">
                        <svg class="w-10 h-10 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-sm text-gray-400">Nenhum item adicionado</p>
                    </div>

                    <div v-for="line in lines" :key="line.variation_id"
                        class="flex items-center gap-4 px-4 py-3 border-b border-gray-100 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ line.product_name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ line.size }} · {{ line.color }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="setQty(line.variation_id, line.quantity - 1)"
                                class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-lg flex items-center justify-center transition-colors">−</button>
                            <span class="w-6 text-center text-sm font-medium text-gray-900">{{ line.quantity }}</span>
                            <button @click="setQty(line.variation_id, line.quantity + 1)"
                                class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-lg flex items-center justify-center transition-colors">+</button>
                        </div>
                        <div v-if="mode === 'sale'" class="text-right shrink-0 w-20">
                            <p class="text-sm font-semibold text-gray-900">{{ fmt(bcmul(line.unit_price, String(line.quantity))) }}</p>
                            <p class="text-xs text-gray-400">{{ fmt(line.unit_price) }} un.</p>
                        </div>
                        <button @click="removeLine(line.variation_id)"
                            class="text-gray-300 hover:text-red-500 transition-colors ml-1 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT — mode-dependent panel -->
            <div class="w-80 shrink-0 flex flex-col gap-4">

                <!-- SALE MODE: coupon + summary + actions -->
                <template v-if="mode === 'sale'">
                    <!-- Coupon -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Cupom de desconto</p>
                        <div v-if="couponApplied"
                            class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 py-2">
                            <span class="text-sm font-medium text-green-700">{{ couponApplied }}</span>
                            <button @click="removeCoupon" class="text-xs text-gray-400 hover:text-red-500">✕</button>
                        </div>
                        <div v-else class="flex gap-2">
                            <input v-model="coupon" type="text" placeholder="CODIGO25"
                                class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm uppercase placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
                                @keyup.enter="applyCoupon" />
                            <button @click="applyCoupon"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-xs font-medium text-gray-600 border border-gray-200 transition-colors">
                                Aplicar
                            </button>
                        </div>
                        <p v-if="couponError" class="text-xs text-red-500 mt-1.5">{{ couponError }}</p>
                    </div>

                    <!-- Summary -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Resumo</p>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span>{{ fmt(subtotal) }}</span>
                            </div>
                            <div v-if="couponApplied" class="flex justify-between text-green-600">
                                <span>Cupom {{ couponApplied }}</span>
                                <span>−{{ fmt(couponDiscount) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Desconto manual</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-gray-400 text-xs">R$</span>
                                    <input v-model="manualDiscount" type="number" min="0" step="0.01"
                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-gray-300" />
                                </div>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Itens</span>
                                <span>{{ lines.reduce((a, l) => a + l.quantity, 0) }}</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mt-4 pt-4 flex justify-between items-center">
                            <span class="font-bold text-base text-gray-900">Total</span>
                            <span class="font-bold text-xl text-gray-900">{{ fmt(total) }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <button disabled
                                class="flex items-center justify-center gap-1.5 border border-gray-200 rounded-xl py-2 text-xs text-gray-300 cursor-not-allowed">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Salvar venda
                            </button>
                            <button @click="cancelSale"
                                class="flex items-center justify-center gap-1.5 border border-gray-300 rounded-xl py-2 text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancelar
                            </button>
                        </div>
                        <button @click="openPayment" :disabled="lines.length === 0"
                            :class="['w-full py-3 rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2',
                                lines.length > 0 ? 'text-white hover:opacity-90' : 'bg-gray-100 text-gray-300 cursor-not-allowed']"
                            :style="lines.length > 0 ? { backgroundColor: 'var(--color-primary)' } : {}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Finalizar compra
                        </button>
                    </div>
                </template>

                <!-- ENTRY MODE: reason + confirm -->
                <template v-else>
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrada de estoque</p>

                        <div>
                            <label class="text-xs font-medium text-gray-500 mb-1 block">Razão (opcional)</label>
                            <input v-model="entryReason" type="text"
                                placeholder="ex: Recebimento NF #123"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300" />
                        </div>

                        <div class="border-t border-gray-100 pt-3 space-y-1.5 text-sm text-gray-500">
                            <div class="flex justify-between">
                                <span>Variações</span>
                                <span>{{ lines.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total de unidades</span>
                                <span class="font-semibold text-gray-900">{{ lines.reduce((a, l) => a + l.quantity, 0) }}</span>
                            </div>
                        </div>

                        <div v-if="entrySuccess"
                            class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-sm text-green-700">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Estoque atualizado com sucesso.
                        </div>
                        <p v-if="entryError" class="text-xs text-red-500">{{ entryError }}</p>

                        <button @click="submitStockEntry" :disabled="lines.length === 0 || entryLoading"
                            :class="['w-full py-3 rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2',
                                lines.length > 0 && !entryLoading ? 'text-white hover:opacity-90' : 'bg-gray-100 text-gray-300 cursor-not-allowed']"
                            :style="lines.length > 0 && !entryLoading ? { backgroundColor: 'var(--color-primary)' } : {}">
                            <div v-if="entryLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            {{ entryLoading ? 'Registrando…' : 'Confirmar entrada' }}
                        </button>
                    </div>
                </template>

                <!-- Session footer -->
                <div class="text-xs text-gray-400 space-y-0.5 px-1">
                    <p>Venda <span class="font-medium text-gray-500">#{{ saleCounter }}</span></p>
                    <p>Operador: <span class="font-medium text-gray-500">{{ session.operator }}</span></p>
                    <p>Caixa: <span class="font-medium text-gray-500">{{ session.register }}</span></p>
                </div>
            </div>
        </div><!-- /two-column -->
        </div><!-- /content -->

        <!-- Payment modal -->
        <transition name="fade">
            <div v-if="showPayment" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40"
                    @click="!payLoading && !receiptUrl && (showPayment = false)"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">

                    <!-- Success state -->
                    <div v-if="receiptUrl" class="text-center">
                        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Venda finalizada!</h3>
                        <p class="text-sm text-gray-500 mb-6">Pagamento confirmado.</p>
                        <div class="flex gap-3">
                            <a :href="receiptUrl" target="_blank"
                                class="flex-1 text-white text-sm font-medium rounded-xl py-2.5 text-center hover:opacity-90 transition-opacity"
                                style="background-color: var(--color-primary)">
                                Imprimir recibo
                            </a>
                            <button @click="showPayment = false"
                                class="flex-1 border border-gray-300 text-gray-600 text-sm rounded-xl py-2.5 hover:bg-gray-50 transition-colors">
                                Nova venda
                            </button>
                        </div>
                    </div>

                    <!-- Payment form -->
                    <template v-else>
                        <h3 class="font-semibold text-gray-900 mb-1">Forma de pagamento</h3>
                        <p class="text-sm text-gray-500 mb-5">
                            Total: <strong class="text-gray-900 text-base">{{ fmt(total) }}</strong>
                        </p>

                        <!-- Method selector -->
                        <div class="flex gap-2 mb-5">
                            <button
                                v-for="m in [{ value: 'pix', label: 'Pix' }, { value: 'credit_card', label: 'Cartão' }, { value: 'cash', label: 'Dinheiro' }]"
                                :key="m.value"
                                @click="payMethod = m.value as any"
                                :class="['flex-1 py-2.5 rounded-xl text-sm font-medium border transition-colors',
                                    payMethod === m.value
                                        ? 'border-transparent text-white'
                                        : 'border-gray-300 text-gray-500 hover:border-gray-400']"
                                :style="payMethod === m.value ? { backgroundColor: 'var(--color-primary)' } : {}">
                                {{ m.label }}
                            </button>
                        </div>

                        <!-- Pix placeholder -->
                        <div v-if="payMethod === 'pix'"
                            class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-center mb-5">
                            <div class="w-32 h-32 bg-white border border-gray-200 rounded-lg mx-auto mb-3 flex items-center justify-center">
                                <span class="text-gray-300 text-xs">QR Code</span>
                            </div>
                            <p class="text-xs text-gray-400">QR Code gerado pelo gateway será exibido aqui</p>
                        </div>

                        <!-- Card placeholder -->
                        <div v-else-if="payMethod === 'credit_card'"
                            class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-center mb-5">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <p class="text-sm text-gray-500">Passe o cartão na maquininha e confirme.</p>
                        </div>

                        <!-- Cash -->
                        <div v-else class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">Valor recebido (R$)</label>
                            <input v-model="amountPaid" type="number" min="0" step="0.01"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                            <div v-if="changeDue !== null" class="mt-3 flex justify-between text-sm">
                                <span class="text-gray-500">Troco</span>
                                <span class="font-semibold text-green-600">{{ fmt(changeDue) }}</span>
                            </div>
                        </div>

                        <p v-if="payError" class="text-xs text-red-500 mb-3">{{ payError }}</p>

                        <div class="flex gap-3">
                            <button @click="showPayment = false" :disabled="payLoading"
                                class="flex-1 border border-gray-300 text-gray-600 text-sm rounded-xl py-2.5 hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button @click="confirmPayment" :disabled="payLoading"
                                class="flex-1 text-white text-sm font-medium rounded-xl py-2.5 flex items-center justify-center gap-2 hover:opacity-90 transition-opacity disabled:opacity-60"
                                style="background-color: var(--color-primary)">
                                <div v-if="payLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                {{ payLoading ? 'Processando…' : 'Confirmar pagamento' }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </transition>

        <!-- Close session confirmation modal -->
        <transition name="fade">
            <div v-if="showCloseConfirm" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="showCloseConfirm = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Fechar caixa?</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Você será redirecionado para a tela de fechamento onde poderá conferir o saldo.
                    </p>
                    <div class="flex gap-3">
                        <button @click="showCloseConfirm = false"
                            class="flex-1 text-sm border border-gray-300 rounded-xl py-2.5 hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <a :href="session.close_url"
                            class="flex-1 text-sm font-medium text-white rounded-xl py-2.5 bg-red-600 hover:bg-red-700 transition-colors text-center">
                            Fechar caixa
                        </a>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

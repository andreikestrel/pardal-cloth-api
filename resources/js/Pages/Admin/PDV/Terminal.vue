<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { route } from 'ziggy-js'

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

// — Cart state —
const lines    = ref<CartLine[]>([])
const coupon   = ref('')
const couponApplied = ref<string | null>(null)
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
const showPayment  = ref(false)
const payMethod    = ref<'pix' | 'credit_card' | 'cash'>('pix')
const amountPaid   = ref('')
const payLoading   = ref(false)
const payError     = ref('')
const receiptUrl   = ref<string | null>(null)
const lastOrderId  = ref<string | null>(null)

// — Computed totals —
const subtotal = computed(() => {
    return lines.value.reduce((acc, l) => {
        return bcadd(acc, bcmul(l.unit_price, String(l.quantity)))
    }, '0.00')
})

const totalDiscount = computed(() => {
    return bcadd(couponDiscount.value, manualDiscount.value || '0.00')
})

const total = computed(() => {
    const t = bcsub(subtotal.value, totalDiscount.value)
    return parseFloat(t) < 0 ? '0.00' : t
})

const changeDue = computed(() => {
    if (payMethod.value !== 'cash' || !amountPaid.value) return null
    const change = bcsub(amountPaid.value, total.value)
    return parseFloat(change) >= 0 ? change : null
})

// Minimal fixed-point helpers (no backend for display-only totals)
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
        const res = await fetch(route('admin.pdv.products') + '?q=' + encodeURIComponent(q), {
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
    if (variation.stock <= 0) return
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
    if (qty > line.stock) return
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
</script>

<template>
    <!-- Full-screen dark terminal — no admin sidebar -->
    <div class="min-h-screen bg-gray-900 text-gray-100 flex flex-col">
        <!-- Top bar -->
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-700 bg-gray-800">
            <span class="text-sm font-semibold tracking-wide">PDV — Terminal</span>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span>Operador: <strong class="text-gray-200">{{ session.operator }}</strong></span>
                <span>Caixa: <strong class="text-gray-200">{{ session.register }}</strong></span>
                <a :href="session.close_url"
                    class="ml-2 text-red-400 hover:text-red-300 border border-red-800 rounded px-2 py-0.5 hover:border-red-600 transition-colors">
                    Fechar caixa
                </a>
            </div>
        </div>

        <!-- Main area -->
        <div class="flex flex-1 overflow-hidden">

            <!-- LEFT — Product search + items -->
            <div class="flex flex-col flex-1 overflow-y-auto p-5 gap-4">

                <!-- Product search -->
                <div class="bg-gray-800 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Adicionar produto</p>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por nome ou código de barras"
                            @focus="showDropdown = searchResults.length > 0"
                            @blur="setTimeout(() => showDropdown = false, 200)"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                        <div v-if="searching" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <div class="w-4 h-4 border-2 border-gray-500 border-t-blue-400 rounded-full animate-spin"></div>
                        </div>

                        <!-- Search dropdown -->
                        <div v-if="showDropdown && searchResults.length"
                            class="absolute z-20 top-full mt-1 w-full bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden max-h-80 overflow-y-auto">
                            <div v-for="product in searchResults" :key="product.id" class="border-b border-gray-700 last:border-0">
                                <div class="px-4 py-2 bg-gray-750">
                                    <p class="text-sm font-medium text-gray-200">{{ product.name }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 p-3">
                                    <button
                                        v-for="v in product.variations" :key="v.id"
                                        @click="addVariation(product, v)"
                                        :disabled="v.stock <= 0"
                                        :class="['flex flex-col items-start px-3 py-2 rounded-lg border text-xs transition-colors',
                                            v.stock > 0
                                                ? 'border-gray-600 hover:border-blue-500 hover:bg-blue-500/10 cursor-pointer'
                                                : 'border-gray-700 opacity-40 cursor-not-allowed']">
                                        <span class="font-medium">{{ v.size }} / {{ v.color }}</span>
                                        <span class="text-gray-400 mt-0.5">{{ fmt(String(v.price)) }} · {{ v.stock }} un.</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="showDropdown && !searching"
                            class="absolute z-20 top-full mt-1 w-full bg-gray-800 border border-gray-700 rounded-xl p-3 text-sm text-gray-500 text-center shadow-xl">
                            Nenhum produto encontrado.
                        </div>
                    </div>
                </div>

                <!-- Customer (collapsible) -->
                <div class="bg-gray-800 rounded-xl overflow-hidden">
                    <button @click="showCustomer = !showCustomer"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm text-gray-300 hover:text-white transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Dados do cliente <span class="text-gray-500 ml-1">(opcional)</span>
                        </span>
                        <svg :class="['w-4 h-4 transition-transform', showCustomer ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="showCustomer" class="px-4 pb-4 grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Nome</label>
                            <input v-model="customerName" type="text" placeholder="Nome do cliente"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">CPF</label>
                            <input v-model="customerDoc" type="text" placeholder="000.000.000-00"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        </div>
                    </div>
                </div>

                <!-- Cart items -->
                <div class="bg-gray-800 rounded-xl flex-1">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Itens da venda</p>
                        <span class="text-xs font-bold bg-blue-600 text-white rounded-full px-2 py-0.5">
                            {{ lines.length }} {{ lines.length === 1 ? 'item' : 'itens' }}
                        </span>
                    </div>

                    <div v-if="lines.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-600">
                        <svg class="w-12 h-12 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-sm">Nenhum item adicionado</p>
                    </div>

                    <div v-for="line in lines" :key="line.variation_id"
                        class="flex items-center gap-4 px-4 py-3 border-b border-gray-700 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ line.product_name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ line.size }} · {{ line.color }}</p>
                        </div>
                        <!-- Qty stepper -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="setQty(line.variation_id, line.quantity - 1)"
                                class="w-7 h-7 rounded-full bg-gray-700 hover:bg-gray-600 text-lg flex items-center justify-center">−</button>
                            <span class="w-6 text-center text-sm font-medium">{{ line.quantity }}</span>
                            <button @click="setQty(line.variation_id, line.quantity + 1)"
                                class="w-7 h-7 rounded-full bg-gray-700 hover:bg-gray-600 text-lg flex items-center justify-center">+</button>
                        </div>
                        <div class="text-right shrink-0 w-20">
                            <p class="text-sm font-semibold">{{ fmt(bcmul(line.unit_price, String(line.quantity))) }}</p>
                            <p class="text-xs text-gray-500">{{ fmt(line.unit_price) }} un.</p>
                        </div>
                        <button @click="removeLine(line.variation_id)"
                            class="text-gray-600 hover:text-red-400 transition-colors ml-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT — Summary + actions -->
            <div class="w-80 shrink-0 flex flex-col gap-4 p-5 border-l border-gray-700 overflow-y-auto">

                <!-- Coupon -->
                <div class="bg-gray-800 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Cupom de desconto</p>
                    <div v-if="couponApplied" class="flex items-center justify-between bg-green-900/30 border border-green-700 rounded-lg px-3 py-2">
                        <span class="text-sm font-medium text-green-400">{{ couponApplied }}</span>
                        <button @click="removeCoupon" class="text-xs text-gray-500 hover:text-red-400">✕</button>
                    </div>
                    <div v-else class="flex gap-2">
                        <input v-model="coupon" type="text" placeholder="CODIGO25"
                            class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm uppercase placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            @keyup.enter="applyCoupon" />
                        <button @click="applyCoupon"
                            class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-xs font-medium border border-gray-600 transition-colors">
                            Aplicar
                        </button>
                    </div>
                    <p v-if="couponError" class="text-xs text-red-400 mt-1">{{ couponError }}</p>
                </div>

                <!-- Summary -->
                <div class="bg-gray-800 rounded-xl p-4 flex-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Resumo</p>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span>{{ fmt(subtotal) }}</span>
                        </div>
                        <div v-if="couponApplied" class="flex justify-between text-green-400">
                            <span>Cupom {{ couponApplied }}</span>
                            <span>−{{ fmt(couponDiscount) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Desconto manual</span>
                            <div class="flex items-center gap-1">
                                <span class="text-gray-500 text-xs">R$</span>
                                <input v-model="manualDiscount" type="number" min="0" step="0.01"
                                    class="w-20 bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Itens</span>
                            <span>{{ lines.reduce((a, l) => a + l.quantity, 0) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-700 mt-4 pt-4 flex justify-between items-center">
                        <span class="font-bold text-base">Total</span>
                        <span class="font-bold text-xl text-white">{{ fmt(total) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-gray-800 rounded-xl p-4 space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Ações</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button disabled class="flex items-center justify-center gap-1.5 border border-gray-600 rounded-lg py-2 text-xs text-gray-400 opacity-50 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            Salvar venda
                        </button>
                        <button @click="cancelSale"
                            class="flex items-center justify-center gap-1.5 border border-gray-600 rounded-lg py-2 text-xs text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            Cancelar
                        </button>
                    </div>
                    <button @click="openPayment" :disabled="lines.length === 0"
                        :class="['w-full py-3 rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2',
                            lines.length > 0 ? 'bg-blue-600 hover:bg-blue-500 text-white' : 'bg-gray-700 text-gray-500 cursor-not-allowed']">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Finalizar compra
                    </button>
                </div>

                <!-- Session info footer -->
                <div class="text-xs text-gray-600 space-y-0.5 px-1">
                    <p>Venda <span class="font-medium text-gray-500">#{{ saleCounter }}</span></p>
                    <p>Operador: <span class="font-medium text-gray-500">{{ session.operator }}</span></p>
                    <p>Caixa: <span class="font-medium text-gray-500">{{ session.register }}</span></p>
                </div>
            </div>
        </div>

        <!-- Payment modal -->
        <transition name="fade">
            <div v-if="showPayment" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/60" @click="!payLoading && !receiptUrl && (showPayment = false)"></div>
                <div class="relative bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">

                    <!-- Success state -->
                    <div v-if="receiptUrl" class="text-center">
                        <div class="w-14 h-14 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-1">Venda finalizada!</h3>
                        <p class="text-sm text-gray-400 mb-6">Pagamento confirmado.</p>
                        <div class="flex gap-3">
                            <a :href="receiptUrl" target="_blank"
                                class="flex-1 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl py-2.5 text-center transition-colors">
                                Imprimir recibo
                            </a>
                            <button @click="showPayment = false"
                                class="flex-1 border border-gray-600 hover:bg-gray-700 text-gray-300 text-sm rounded-xl py-2.5 transition-colors">
                                Nova venda
                            </button>
                        </div>
                    </div>

                    <!-- Payment form -->
                    <template v-else>
                        <h3 class="font-semibold text-white mb-1">Forma de pagamento</h3>
                        <p class="text-sm text-gray-400 mb-5">Total: <strong class="text-white text-base">{{ fmt(total) }}</strong></p>

                        <!-- Method selector -->
                        <div class="flex gap-2 mb-5">
                            <button v-for="m in [{ value: 'pix', label: 'Pix' }, { value: 'credit_card', label: 'Cartão' }, { value: 'cash', label: 'Dinheiro' }]"
                                :key="m.value"
                                @click="payMethod = m.value as any"
                                :class="['flex-1 py-2.5 rounded-xl text-sm font-medium border transition-colors',
                                    payMethod === m.value
                                        ? 'bg-blue-600 border-blue-600 text-white'
                                        : 'border-gray-600 text-gray-400 hover:border-gray-500']">
                                {{ m.label }}
                            </button>
                        </div>

                        <!-- Pix placeholder -->
                        <div v-if="payMethod === 'pix'" class="bg-gray-900 rounded-xl p-5 text-center mb-5">
                            <div class="w-32 h-32 bg-white rounded-lg mx-auto mb-3 flex items-center justify-center">
                                <span class="text-gray-400 text-xs">QR Code</span>
                            </div>
                            <p class="text-xs text-gray-500">QR Code gerado pelo gateway será exibido aqui</p>
                        </div>

                        <!-- Card placeholder -->
                        <div v-else-if="payMethod === 'credit_card'" class="bg-gray-900 rounded-xl p-5 text-center mb-5">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <p class="text-sm text-gray-400">Passe o cartão na maquininha e confirme.</p>
                        </div>

                        <!-- Cash -->
                        <div v-else class="bg-gray-900 rounded-xl p-4 mb-5">
                            <label class="text-xs text-gray-400 mb-1 block">Valor recebido (R$)</label>
                            <input v-model="amountPaid" type="number" min="0" step="0.01"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            <div v-if="changeDue !== null" class="mt-3 flex justify-between text-sm">
                                <span class="text-gray-400">Troco</span>
                                <span class="font-semibold text-green-400">{{ fmt(changeDue) }}</span>
                            </div>
                        </div>

                        <p v-if="payError" class="text-xs text-red-400 mb-3">{{ payError }}</p>

                        <div class="flex gap-3">
                            <button @click="showPayment = false" :disabled="payLoading"
                                class="flex-1 border border-gray-600 text-gray-300 text-sm rounded-xl py-2.5 hover:bg-gray-700 transition-colors">
                                Cancelar
                            </button>
                            <button @click="confirmPayment" :disabled="payLoading"
                                class="flex-1 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl py-2.5 transition-colors flex items-center justify-center gap-2">
                                <div v-if="payLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                {{ payLoading ? 'Processando…' : 'Confirmar pagamento' }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

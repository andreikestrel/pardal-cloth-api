// ─── Auth ────────────────────────────────────────────────────────────────────

export interface User {
    id: string
    name: string
    email: string
    email_verified_at: string | null
    roles: string[]
}

// ─── Catalog ─────────────────────────────────────────────────────────────────

export interface Category {
    id: string
    name: string
    slug: string
    description: string | null
    active: boolean
    image_url: string | null
}

export interface ProductVariation {
    id: string
    product_id: string
    size: string
    color: string
    sku: string
    barcode: string | null
    price: string
    stock: number
    min_stock: number
}

export interface Product {
    id: string
    category_id: string
    category?: Category
    name: string
    slug: string
    description: string | null
    base_price: string
    active: boolean
    variations: ProductVariation[]
    images: MediaItem[]
}

// ─── Cart ─────────────────────────────────────────────────────────────────────

export interface CartLineItem {
    variation_id: string
    quantity: number
    product_name?: string | null
    unit_price?: string | null
    size?: string | null
    color?: string | null
    cover_url?: string | null
    discount?: string | null
    subtotal?: string | null
}

export interface PromotionApplied {
    name: string
    discount: string
}

export interface CartCalculation {
    items: CartLineItem[]
    subtotal: string
    promotions_applied: PromotionApplied[]
    discount_promotions: string
    discount_coupon: string
    total: string
}

// ─── Orders ───────────────────────────────────────────────────────────────────

export type OrderStatus =
    | 'pending'
    | 'confirmed'
    | 'preparing'
    | 'shipped'
    | 'delivered'
    | 'cancelled'

export interface ShippingAddress {
    street: string
    number: string
    complement?: string
    city: string
    state: string
    zip: string
}

export interface OrderItem {
    id: string
    variation_id: string
    variation?: ProductVariation & { product?: Pick<Product, 'name' | 'slug'> }
    quantity: number
    unit_price: string
    discount: string
    subtotal: string
}

export interface OrderStatusHistory {
    id: string
    status: OrderStatus
    note: string | null
    created_at: string
}

export interface Payment {
    id: string
    gateway: 'mercadopago' | 'asaas'
    method: 'pix' | 'boleto' | 'credit_card' | 'checkout_pro'
    status: 'pending' | 'approved' | 'rejected' | 'refunded'
    amount: string
    pix_code: string | null
    pix_qr_code: string | null
    payment_url: string | null
    boleto_url: string | null
    expires_at: string | null
    paid_at: string | null
}

export interface Order {
    id: string
    status: OrderStatus
    subtotal: string
    discount_promotions: string
    discount_coupon: string
    total: string
    shipping_address: ShippingAddress
    notes: string | null
    items: OrderItem[]
    status_history: OrderStatusHistory[]
    payment?: Payment
    created_at: string
    updated_at: string
}

// ─── Promotions & Coupons ─────────────────────────────────────────────────────

export type TriggerType = 'min_qty' | 'min_amount' | 'product' | 'category'
export type DiscountType = 'percentage' | 'fixed' | 'free_item' | 'free_shipping'

export interface Promotion {
    id: string
    name: string
    trigger_type: TriggerType
    discount_type: DiscountType
    discount_value: string
    min_items: number | null
    min_amount: string | null
    target_id: string | null
    active: boolean
    starts_at: string | null
    ends_at: string | null
    priority: number
}

export interface Coupon {
    id: string
    code: string
    discount_type: 'percentage' | 'fixed'
    discount_value: string
    min_order_amount: string | null
    max_uses: number | null
    uses_per_user: number | null
    used_count: number
    stackable: boolean
    active: boolean
    expires_at: string | null
}

// ─── Media ────────────────────────────────────────────────────────────────────

export interface MediaItem {
    id: number
    url: string
    thumb_url: string
    name: string
    order: number
}

// ─── Settings ────────────────────────────────────────────────────────────────

export interface Settings {
    company_name: string
    primary_color: string
    secondary_color: string
    accent_color: string
    logo: string | null
}

// ─── Inertia shared props ─────────────────────────────────────────────────────

export interface PageProps {
    auth: {
        user: User | null
    }
    flash: {
        success?: string
        error?: string
    }
    settings: Settings
    ziggy: Record<string, unknown>
}

# pardal-cloth

Backend and frontend monorepo for **Pardal Cloth** — a full-featured e-commerce platform for a clothing store, built as a portfolio project to demonstrate production-level fullstack skills.

## Overview

Pardal Cloth covers the complete e-commerce cycle: product catalog with size/color variations, cart with automatic promotions, coupon codes, checkout, payment processing (Mercado Pago + Asaas), order tracking with real-time status updates, and a full admin panel.

This repository is one of three independent services that make up the platform:

| Repository | Stack | Role |
|---|---|---|
| `pardal-cloth` | Laravel 12 + Inertia.js + Vue 3 | Monorepo: API + frontend **(this repo)** |
| `pardal-cloth-ws` | Node.js + Socket.IO | Real-time WebSocket service |
| `pardal-cloth-app` | Flutter | Mobile app *(planned)* |

## Tech Stack

**Backend**
- PHP 8.2 + Laravel 12
- MySQL — UUID primary keys across all tables, `decimal(10,2)` for all monetary values
- Laravel Sanctum — session-based authentication (same-origin, no JWT overhead)
- Spatie Permission — RBAC with `admin` and `customer` roles
- Spatie Media Library — image uploads for products and categories
- Laravel Queues — async jobs for email, real-time notifications and stock alerts
- DomPDF — PDF invoice generation

**Frontend**
- Vue 3 + TypeScript + Inertia.js v2
- Tailwind CSS v4
- Ziggy — typed Laravel route helpers in TypeScript

## Architecture

### Request lifecycle

```
HTTP Request
  → Route (routes/web/*.php)
  → Middleware (auth, role:admin via Spatie)
  → FormRequest (validation)
  → Controller (thin — receives validated data, calls Service)
  → Service (business logic, calls Models, dispatches Jobs)
  → Model (Eloquent)
  → Resource (transforms output)
  → Inertia::render() or JsonResponse
```

### Service layer

Business logic lives entirely in `app/Services/`, never in controllers or models:

| Service | Responsibility |
|---|---|
| `CartService` | Loads current prices from DB, applies promotions, builds `CartCalculation` |
| `PromotionService` | Evaluates active promotions in priority order, one `discount_type` per item |
| `CouponService` | Validates all coupon rules, calculates discount amount |
| `OrderService` | Orchestrates cart + coupon + stock, creates order, dispatches jobs |
| `StockService` | Reserve on order, release on cancel, manual adjustments with audit log |
| `PaymentService` | Resolves active gateway at runtime, no `if/else` per gateway |

### Payment gateways — Strategy Pattern

Both gateways implement `PaymentGatewayInterface` and extend `AbstractGateway`. `PaymentService` resolves the active one at runtime from `payment_settings` — adding a third gateway requires only implementing the interface and registering the binding in `AppServiceProvider`.

```
PaymentGatewayInterface
  ├── MercadoPagoGateway  (Checkout Pro, Pix)
  └── AsaasGateway        (Pix, Boleto, Credit Card)
```

### Real-time order updates

The WebSocket concern is intentionally decoupled into a separate Node.js service (`pardal-cloth-ws`). When an order status changes, `NotifyOrderStatusChanged` job sends an HTTP POST to the WS server with a shared secret. The WS server broadcasts the event to the customer's Socket.IO room — no persistent connections in PHP.

```
Admin updates order status
  → OrderService::updateStatus()
  → NotifyOrderStatusChanged job (queue)
  → POST /internal/notify → pardal-cloth-ws
  → io.to("order:{id}").emit("order:status_updated")
  → Customer's browser updates without reload
```

### Cart state

Cart lives on the frontend (`useCart` composable + localStorage). The server only calculates totals via `POST /cart/calculate`. At order creation, the server recalculates everything independently — the total sent by the frontend is always ignored to prevent price manipulation.

## Database

19 tables, all with UUID primary keys:

`users` · `categories` · `products` · `product_variations` · `promotions` · `coupons` · `coupon_uses` · `orders` · `order_items` · `order_status_history` · `payments` · `payment_settings` · `stock_movements` · `settings`

Plus: `roles` · `permissions` · `media` (Spatie packages) · `cache` · `jobs`

## Project Structure

```
app/
├── Contracts/          # PaymentGatewayInterface
├── DataTransferObjects/ # PaymentResult, CartCalculation, CartItemData
├── Gateways/           # MercadoPagoGateway, AsaasGateway (+ AbstractGateway)
├── Http/
│   ├── Controllers/    # Thin — delegate to Services
│   ├── Middleware/     # HandleInertiaRequests (shared props)
│   ├── Requests/       # Form validation
│   └── Resources/      # API output transformation
├── Jobs/               # SendOrderConfirmationEmail, NotifyOrderStatusChanged, AlertLowStock
├── Models/             # Eloquent + HasUuids + relationships
├── Providers/          # AppServiceProvider (gateway bindings, UUID morphs)
└── Services/           # All business logic

resources/js/
├── Pages/              # Inertia page components (Shop, Cart, Checkout, Orders, Admin)
├── Components/         # Reusable Vue components (UI, Shop, Cart, Admin)
├── Layouts/            # AppLayout, AdminLayout, AuthLayout
├── Composables/        # useCart, useOrderSocket, usePromotion
└── types/              # Shared TypeScript types (index.d.ts)
```

## Local Setup

```bash
git clone https://github.com/andreikestrel/pardal-cloth.git
cd pardal-cloth

composer install
npm install

cp .env.example .env
php artisan key:generate

# Configure .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate

php artisan db:seed          # admin + sample products + promotions

composer run dev             # starts Laravel + Vite + Queue worker + Pail
```

## Key Decisions

- **Monorepo (Laravel + Inertia)** over separate SPA — same origin, session auth, no CORS, SSR on page load.
- **WebSocket as separate service** — demonstrates polyglot architecture; Node.js handles persistent connections naturally.
- **Strategy Pattern for gateways** — new gateway = implement interface + one line in AppServiceProvider.
- **UUID PKs everywhere** — safe to expose in URLs, no enumeration attacks.
- **Server always recalculates totals** — frontend totals are display-only; any mismatch > 1 cent at order creation returns 422.
- **Credentials encrypted in DB** — admin updates keys via UI without server access; `Crypt::encrypt()` with AES-256-CBC.
- **Cart on frontend** — no `carts` table, no cleanup jobs; server recalculates at checkout.

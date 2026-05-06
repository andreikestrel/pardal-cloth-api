# Pardal Cloth

A full-featured e-commerce platform built as a portfolio project to demonstrate production-level fullstack skills. Pardal Cloth ships with everything a small-to-medium clothing store needs to operate end-to-end: storefront, checkout, payments, inventory, blog, marketing tools and a white-label admin panel — all driven from a single configurable backend.

## Highlights

- **Storefront** — catalog with size/color variations, fulltext product search, faceted filters (price/size/color), category chips, hero carousel and a side cart drawer that works on desktop and mobile.
- **Cart & Checkout** — promotions and coupons applied automatically, server-recalculated totals (frontend totals are display-only), Pix / Boleto / Credit Card via Mercado Pago or Asaas.
- **Order tracking** — real-time status updates, downloadable PDF invoices, customer order history.
- **Admin panel** — products, categories, promotions, coupons, orders, stock, reports — all in one place. Inventory adjustments with reason codes and audit log.
- **Blog module** — toggleable in settings. Tiptap rich-text editor with image paste/drag, autosave to `localStorage`, in-editor preview tab, and drag-to-reposition cover focal point. Server-side HTML sanitization via HTMLPurifier. Public blog has a featured carousel and category-filtered grid.
- **Catalog carousel** — admin-configurable hero slides with image upload, free-form link target, ordering and active toggle.
- **User invitations** — admins invite employees by email; the invitee gets a 24h link to set their own password (no temporary passwords shared).
- **Barcode scanner** — product variations editor reads EAN-13 / Code 128 / QR via the device camera (ZXing).
- **White-label theming** — company name, logo, primary/secondary/accent colors and module toggles all live in `settings`. The frontend reads CSS custom properties on the layout root, so reskinning the entire app is a settings update — no build, no code change.

## Stack

**Backend** — PHP 8.2 · Laravel 12 · MySQL (UUID PKs everywhere, `decimal(10,2)` for money) · Spatie Permission (RBAC: `admin`, `customer`) · Spatie Media Library · HTMLPurifier (rich-text sanitization) · DomPDF · Laravel Queues.

**Frontend** — Vue 3 + TypeScript · Inertia.js v2 · Tailwind CSS v4 · Tiptap (rich-text editor) · ZXing (barcode scanner) · Ziggy (typed Laravel routes in TS).

**Real-time** — Order status updates broadcast over Socket.IO on a separate Node.js service. The PHP side stays stateless: jobs HTTP-POST to the WS server, which pushes to the customer's room.

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
  → Inertia::render() or JsonResponse
```

Controllers are thin. Models hold relationships and scopes only. **All business logic lives in `app/Services/`.**

### Service layer

| Service | Responsibility |
|---|---|
| `CartService` | Loads current prices from DB, applies promotions, builds `CartCalculation` |
| `PromotionService` | Evaluates active promotions in priority order, one `discount_type` per item |
| `CouponService` | Validates coupon rules (limit, expiry, per-user usage), calculates discount |
| `OrderService` | Orchestrates cart + coupon + stock, creates order, dispatches jobs |
| `StockService` | Reserve on order, release on cancel, manual entry/exit with audit log |
| `PaymentService` | Resolves active gateway at runtime — no `if/else` per gateway |

### Payment gateways — Strategy Pattern

Both gateways implement `PaymentGatewayInterface` and extend `AbstractGateway`. `PaymentService` resolves the active one at runtime from `payment_settings`. Adding a third gateway requires only implementing the interface and registering one binding.

```
PaymentGatewayInterface
  ├── MercadoPagoGateway   (Checkout Pro, Pix)
  └── AsaasGateway         (Pix, Boleto, Credit Card)
```

Credentials are encrypted in the DB (`Crypt::encrypt()`, AES-256-CBC) and only ever returned masked to the admin UI.

### Cart state

The cart lives on the frontend (`useCart` composable + `localStorage`). The server only calculates totals via `POST /cart/calculate`. At order creation, the server recalculates everything independently — the total sent by the frontend is **always** discarded. This eliminates an entire class of price-manipulation attacks and removes the need for a `carts` table or cleanup jobs.

### Blog module

Storage is sanitized HTML, not Markdown — Tiptap produces it on the client, HTMLPurifier (profile `rich`) sanitizes it server-side before save. The same `BlogPostBody.vue` component renders both the in-editor preview and the public post, so what the author sees while writing is exactly what readers see.

The module is toggled by a single `blog_enabled` flag in `settings`. When off:
- Public `/blog` and `/blog/{slug}` return 404
- The "Blog" link disappears from the storefront nav
- Admin sidebar hides the Blog menu

### White-label theming

`settings` (cached for 1h, busted on save) is shared globally via `HandleInertiaRequests`. Layouts bind the colors as CSS custom properties on the root `<div>`:

```vue
<div :style="{
  '--color-primary':   settings.primary_color,
  '--color-secondary': settings.secondary_color,
  '--color-accent':    settings.accent_color,
}">
```

Components reference `var(--color-primary)` for dynamic-colored elements (CTAs, badges, hero). Static Tailwind classes handle layout/spacing. Logo, company name and module toggles are part of the same shared object.

### Real-time order updates

```
Admin updates order status
  → OrderService::updateStatus()
  → NotifyOrderStatusChanged job (queue)
  → POST /internal/notify → WebSocket service
  → io.to("order:{id}").emit("order:status_updated")
  → Customer's browser updates without reload
```

The PHP server never holds long-lived connections. The WS service runs independently and is reachable by the public via Socket.IO.

## Roadmap

| Status | Feature |
|---|---|
| ✅ | Storefront, cart drawer, fulltext search, filters |
| ✅ | Checkout (Mercado Pago + Asaas), order tracking |
| ✅ | Admin: products/categories/promotions/coupons/orders/stock/reports |
| ✅ | White-label theming and module toggle for blog |
| ✅ | Blog with rich-text editor, autosave, preview, cover focal-point drag, public carousel |
| ✅ | Hero carousel (admin-configurable slides) |
| ✅ | Tag landing pages — cross-links products and blog posts by tag |
| ✅ | User invitations (24h email link, no temporary passwords) |
| ✅ | Barcode scanner in product variations editor |
| ✅ | Tagging system across products, posts and search |
| ✅ | PDV (point-of-sale) — in-person sales terminal, manual payment, source-tagged orders |
| 🚧 | ERP-style modules — supplier directory, purchase orders, accounts payable, cash-flow reports |
| 🚧 | WebSocket service deployment scripts |
| 📋 | Mobile app (planned) |

Legend: ✅ shipped · 🚧 in progress · 📋 planned

## Project Structure

```
app/
├── Contracts/             # PaymentGatewayInterface
├── DataTransferObjects/   # PaymentResult, CartCalculation, CartItemData
├── Gateways/              # MercadoPagoGateway, AsaasGateway (+ AbstractGateway)
├── Http/
│   ├── Controllers/       # Thin — delegate to Services
│   ├── Middleware/        # HandleInertiaRequests (shared props)
│   ├── Requests/          # Form validation
│   └── Resources/         # API output transformation
├── Jobs/                  # SendOrderConfirmationEmail, NotifyOrderStatusChanged, AlertLowStock
├── Models/                # Eloquent + HasUuids + relationships
├── Notifications/         # InviteUserNotification
├── Providers/             # AppServiceProvider (gateway bindings, UUID morphs)
└── Services/              # All business logic

resources/js/
├── Pages/                 # Inertia page components (Shop, Cart, Checkout, Orders, Auth, Admin)
├── Components/
│   ├── Admin/             # RichTextEditor, BarcodeScanner, StockAdjustModal, ...
│   ├── Shop/              # HeroCarousel, CartDrawer, BlogPostBody
│   └── UI/                # Reusable primitives (FormField, PrimaryButton, PageHeader)
├── Layouts/               # AppLayout, AdminLayout, AuthLayout
├── Composables/           # useCart, useOrderSocket
└── types/                 # Shared TypeScript types
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
php artisan storage:link

php artisan db:seed     # admin@pardal.com / 123456 + sample products
```

Run everything with one command:

```bash
composer dev        # Laravel + Queue worker + Vite (concurrently)
# Windows shortcut:
dev.bat
```

## Email (development)

The default `MAIL_MAILER=log` writes outgoing emails to `storage/logs/laravel.log` — convenient for testing the user invitation flow without a real SMTP server. To send real emails (Gmail / Mailpit / Resend), update the `MAIL_*` keys in `.env` — no code changes required.

## Key Decisions

- **Monorepo (Laravel + Inertia)** over separate SPA — same origin, session auth, no CORS overhead, SSR on first paint.
- **WebSocket as a separate service** — Node.js handles persistent connections naturally; PHP stays stateless.
- **Strategy Pattern for gateways** — new gateway = one interface + one binding.
- **UUID PKs everywhere** — safe to expose in URLs, no enumeration attacks.
- **Server always recalculates totals** — frontend totals are display-only.
- **Credentials encrypted in DB** — admin updates keys via UI without server access.
- **Cart on frontend** — no `carts` table, no cleanup jobs.
- **Blog content as sanitized HTML, not MD** — preserves formatting fidelity, single render path for editor preview and public post.
- **White-label is a settings update** — colors, logo, module toggles change at runtime; no build step, no code change.

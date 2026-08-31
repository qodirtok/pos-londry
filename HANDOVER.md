# Handover — Londry POS Laundry (Laravel 10, PHP 8.1)

> Ringkasan struktur, progress, dan guideline untuk penerus. Spec sumber: `pos-laundry.md` (55 section).
> Stack: Laravel 10 • PHP 8.1 • SQLite (dev) / PostgreSQL (prod) • Tailwind via Vite • Blade • PWA (manifest + Service Worker + offline) — build via `nvm use 24 && npm run build`

---

## 1) Cara Jalan Cepat (5 menit)

```bash
cd /Users/zlns/personal-www/londry
cp .env.example .env          # jika belum ada
php artisan key:generate      # jika APP_KEY kosong
touch database/database.sqlite
# .env dev: DB_CONNECTION=sqlite, DB_DATABASE=/absolute/path/database/database.sqlite
php artisan migrate --force
php artisan db:seed --force            # dev: ProductionSeeder + DemoSeeder (full, idempotent)
php artisan serve --host=127.0.0.1 --port=8010
# buka http://127.0.0.1:8010/login  (form login bersih, tanpa info akun demo — login manual username/email + password)
```

**Akun seed (idempotent, `firstOrCreate`):**

| Akun | Username / Email | Password | Merchant | Cabang | Catatan |
|------|----------------|----------|----------|--------|---------|
| **Super Admin (global)** | `super_admin` / `superadmin@londry.test` | `password` | NULL (global) | MLG | `isAdmin && merchant_id==null` → lihat semua toko, bisa buat merchant |
| Admin toko 1 | `admin` / `admin@londry.test` | `password` | LONDRY-001 | MLG | Owner toko 1 |
| Kasir toko 1 | `kasir` | `password` | LONDRY-001 | MLG | |
| Admin toko 2 | `admin_toko2` / `admin2@londry.test` | `password` | TOKO-002 | SBY2 | Owner toko 2 |
| Demo Admin | `demo_admin` / `demo.admin@londry.test` | `demo123` | LONDRY-001 | DEMO | `is_demo=1` |
| Demo Kasir | `demo_kasir` | `demo123` | LONDRY-001 | DEMO | `is_demo=1` |
| Walk-in prod | `CUST-000000` Walk-in Customer | - | LONDRY-001 | MLG | |
| Walk-in demo | `DEMO-000000` Walk-in DEMO | - | LONDRY-001 | DEMO | |
| Walk-in toko2 | `CUST-TOKO2-001` Customer Toko2 | - | TOKO-002 | SBY2 | |

Halaman login **bersih tanpa info akun demo** (`resources/views/auth/login.blade.php` hanya form username/email + password). Akun demo tetap ada di DB untuk testing, tapi tidak ditampilkan di UI — login manual ketik username + password (mis. `demo_admin / demo123`, `super_admin / password`).

**Seeder untuk push ke home server (PENTING — data dummy tidak ikut prod):**

```bash
# home server produksi — hanya data real, tanpa DEMO/dummy
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force

# kalau mau demo juga di server (untuk tester)
php artisan db:seed --class=DemoSeeder --force

# dev laptop — semua
php artisan migrate:fresh --seed
# atau
php artisan migrate:fresh --force && php artisan db:seed --force
```
Order dummy via `verify_*.php` manual **bukan dari seeder** — tidak ada seeder order dummy. Di prod `orders` kosong setelah `migrate:fresh`.

**Ganti ke PostgreSQL (prod):** di `.env` set `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT=5432`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Schema sudah kompatibel (DECIMAL, FK, JSON, `merchant_id` FK, unique per merchant). Tidak perlu ubah migration.

**View/config/route cache (production):** `php artisan optimize` (config+routes), `php artisan view:cache` + `php artisan event:cache`. Habis edit Blade/config/routes di production wajib re-cache; di dev cukup `php artisan view:clear` / `php artisan optimize:clear`.

**Build frontend (Vite + Tailwind):** `bash -lc 'source ~/.nvm/nvm.sh; nvm use 24; npm run build'` (output `public/build/assets/app-*.css` ~32KB gzip 6KB + `app-*.js` ~51KB gzip 19KB). `public/build` gitignored — build di server saat deploy. Dev: `bash -lc 'source ~/.nvm/nvm.sh; nvm use 24; npm run dev'` (HMR).

**PWA:** `public/manifest.webmanifest` (icons 72-512), `public/sw.js` (cache `londry-v1`, precache offline/manifest/icons; assets cache-first, nav network-first + offline fallback), `public/icons/icon-*x*.png` + `public/apple-touch-icon.png`, `resources/views/offline.blade.php`, `resources/js/pwa.js` (register via `app.js`). Nginx `pwa.conf` + `mime.types webmanifest` sudah terpasang. Verifikasi: `curl -I https://pos.azelsq.my.id/manifest.webmanifest` → `application/manifest+json`, `/sw.js` → `application/javascript`.

**Merchant switch:** `GET /switch-merchant/{id}` (hanya Admin). Sidebar tampil `Merchant / Toko` dropdown jika >1 merchant (super admin lihat semua, admin toko difilter), `Cabang Aktif` difilter per merchant.

**Tambah merchant baru (sebagai super_admin):** `Merchants` → `+ Merchant` → isi Kode/Nama → Save → katalog default (8 kategori + 9 produk + 8 laundry types + settings) otomatis ter-seed. Lalu `Branches` → `+ Cabang` → `Users` → `+ User` role Admin/Manager/Kasir.

---

## 2) Struktur Proyek

```
londry/
├── pos-laundry.md                 # spec lengkap 55 section — sumber kebenaran
├── HANDOVER.md                    # file ini
├── app/
│   ├── Enums/                     # 10 backed-string enums PHP 8.1
│   │   ├── BranchStatus, UserStatus, ProductType, OrderStatus, PaymentStatus
│   │   ├── PaymentMethod, CashType, ShiftStatus, StockMovementType, WhatsAppStatus
│   ├── Models/                    # 21 models (casts, fillable, scopes)
│   │   ├── Merchant               # ← 000020: code, slug, owner_user_id, hasMany branches/users/customers/orders
│   │   ├── Branch, User, Role, Permission
│   │   ├── Category, Product, ProductStock, StockMovement  # merchant_id per toko (000022+000023)
│   │   ├── Customer, Order, OrderItem, Payment, PaymentMethod
│   │   ├── CashCategory, CashTransaction, CashierShift  # merchant_id (000021)
│   │   ├── Setting, AuditLog, Refund, WhatsappLog, LaundryItemType ← kategori/produk/laundry/setting per merchant
│   ├── Services/
│   │   ├── OrderService.php       # transaksi atomik Order→Items→Stock→Payment→Cash; merchant_id + laundry_details + order_status
│   │   ├── CashService.php        # openShift/closeShift merchant_id
│   │   ├── WhatsappService.php    # direk https://web.whatsapp.com/send?phone=62...&text=... + fallback wa.me
│   │   └── Support/NumberGenerator.php, AuditLogger.php
│   ├── Http/Controllers/
│   │   ├── AuthController         # login, switchBranch, switchMerchant
│   │   ├── MerchantController     # CRUD merchant (super admin lihat semua, admin toko hanya miliknya, auto-seed katalog)
│   │   ├── BranchController, UserController  # scope per merchant, guard 403 cross-merchant
│   │   ├── Category/Product/CustomerController # scope per merchant (Category/Product scoped)
│   │   ├── PosController          # index + store, inject laundryTypes+products per merchant
│   │   ├── OrderController        # scope is_demo + merchant_id + branch, 403 cross
│   │   ├── LaundryItemTypeController # CRUD jenis rincian per merchant
│   │   ├── CashController, ShiftController, ReportController, SettingController, DashboardController # scoped merchant_id
│   ├── Http/Middleware/
│   │   ├── BranchContext.php      # set session(branch_id), paksa demo ke DEMO
│   │   ├── BlockDemoFromUserManagement.php # alias block.demo → 403 untuk users/* & branches/* jika is_demo
│   │   └── MerchantContext.php    # aktif di web group, set session(merchant_id) dari user.merchant_id
│   └── helpers.php                # money(), setting()
├── database/
│   ├── migrations/                # 27 file: 2014_* (4) + 2024_01_01_000001..000023
│   │   ├── 000017_add_laundry_details_to_orders  # orders.laundry_details JSON nullable + notes TEXT
│   │   ├── 000018_create_laundry_item_types_table # code unique (legacy) → per merchant di 000023
│   │   ├── 000019_add_demo_flags            # is_demo boolean ke branches/users/customers/orders/cash_transactions/shifts
│   │   ├── 000020_create_merchants_and_add_merchant_id # merchants + merchant_id FK ke branches/users/customers/orders
│   │   ├── 000021_add_merchant_id_to_cash_tables # merchant_id FK ke cash_transactions/cashier_shifts
│   │   ├── 000022_add_merchant_id_to_catalog_tables # merchant_id FK ke categories/products/laundry_item_types/settings
│   │   └── 000023_fix_catalog_uniques_per_merchant  # (code,merchant_id) unique, (branch_id,merchant_id,key) unique + seed TOKO-002
│   └── seeders/
│       ├── DatabaseSeeder.php     # orchestrator → ProductionSeeder + DemoSeeder (dev full)
│       ├── ProductionSeeder.php   # untuk home server prod tanpa DEMO
│       ├── DemoSeeder.php         # hanya DEMO branch + demo users + DEMO customers (is_demo=1)
│       ├── MerchantSeeder.php     # buat LONDRY-001 + backfill merchant_id null
│       ├── BranchSeeder.php       # MLG + SBY
│       ├── RolePermissionSeeder.php # 4 roles: Admin/Kasir/User/Manager + 30+ perms (termasuk merchants.*)
│       ├── UserSeeder.php         # admin/kasir prod + super_admin (merchant_id null)
│       ├── CatalogSeeder.php      # 8 kategori + 9 produk + stock per branch (per merchant)
│       ├── CustomerSeeder.php     # walk-in + 2 prod customers
│       ├── SettingsSeeder.php     # payment_methods + cash_categories + laundry defaults + settings (per merchant)
│       └── LaundryItemTypeSeeder.php
├── resources/views/
│   ├── layouts/app.blade.php      # sidebar lg:flex, drawer mobile; Merchant/Toko switch + Cabang Aktif per merchant; @vite + manifest/apple-touch-icon
│   ├── layouts/guest.blade.php    # guest; @vite + manifest/apple-touch-icon
│   ├── auth/login.blade.php       # form login bersih (tanpa card demo)
│   ├── offline.blade.php          # offline fallback untuk PWA (precached oleh sw.js)
│   ├── merchants/{index,create,edit}.blade.php  # CRUD merchant
│   ├── pos/index.blade.php        # POS utama + panel Rincian Laundry collapsed (list vertical) + status Baru/Selesai
│   ├── orders/show.blade.php      # detail order + rincian dinamis + bar Aksi Struk (Cetak/WA/Close/Cetak&WA)
│   ├── orders/receipt.blade.php   # struk 320px monospace, kotak RINCIAN LAUNDRY dinamis, TANPA info cabang
│   ├── orders/print.blade.php     # sync dari receipt
│   ├── laundry-types/index.blade.php # kelola jenis rincian
│   ├── dashboard.blade.php, products/*, customers/*, branches/*, users/*, cash/*, shifts/*, reports/*, settings/*
├── routes/web.php                 # ~80 routes (lihat §5) — uri customers-search/products-search sebelum resource customers agar tidak shadowing; /customers/{customer}/show dihapus (duplikat resource); + GET /offline, /manifest.webmanifest, /sw.js untuk PWA
├── public/
│   ├── manifest.webmanifest       # PWA manifest (short_name Londry, icons 72-512, shortcuts POS/Orders/Dashboard, theme #4f46e5)
│   ├── sw.js                      # Service Worker londry-v1 (assets cache-first, nav network-first + offline, api network-first)
│   ├── icons/icon-*x*.png         # 9 PNG (72-512 + 180, PHP GD, rounded 22% #4f46e5 + L)
│   ├── apple-touch-icon.png       # iOS
│   └── build/assets/app-*.{css,js} # Vite build (gitignored) — css ~32KB js ~51KB
├── resources/
│   ├── css/app.css                # @tailwind base/components/utilities
│   ├── js/app.js + pwa.js         # bootstrap + SW register (secure context)
│   ├── tailwind.config.js         # content resources/**/*.{blade,js,vue}
│   └── postcss.config.js          # tailwind + autoprefixer
└── config/app.php                 # timezone Asia/Jakarta
```

---

## 3) Database — Ringkas

**Migrations run:** 27 (lihat `php artisan migrate:status`). Batch terbaru: `000019`–`000023`.

| Tabel | Kunci |
|-------|-------|
| `merchants` | id, code unique (LONDRY-001, TOKO-002), name, slug unique, phone/email/address/city, status, owner_user_id FK users nullable |
| `branches` | code unique, name, city, status, is_demo bool, merchant_id FK merchants nullable (MLG/SBY/DEMO→LONDRY-001, SBY2→TOKO-002) |
| `users` | username unique, phone, status, branch_id FK, merchant_id FK (NULL=super admin global), is_demo bool, last_login_at |
| `roles`, `permissions`, `role_user`, `permission_role`, `branch_user` | RBAC + multi-branch pivot; roles: Admin/Kasir/User/Manager |
| `customers` | code unique, name, phone, branch_id, merchant_id FK, is_demo bool (CUST-... prod, DEMO-... demo) |
| `categories` | code + merchant_id unique (000023), name, status, merchant_id FK (LNDRY/CKERING/... per toko terpisah) |
| `products` | sku unique, barcode unique, name, category_id FK, type, price/cost DECIMAL(15,2), merchant_id FK (SRV-*/PRD-*/SRV-2-* per toko) |
| `product_stocks` | unique(product_id,branch_id), quantity DECIMAL(12,3), minimum_stock |
| `stock_movements` | type: stock_in/stock_out/adjustment/sale/return |
| `orders` | order_number unique (MLG-YYYYMMDD-000001, SBY2-..., DEMO-...), branch/customer/cashier FK, merchant_id FK, subtotal/discount/tax/total/paid/change DECIMAL(15,2), laundry_details JSON, is_demo |
| `order_items` | quantity DECIMAL(12,3), price/discount/subtotal DECIMAL(15,2), snapshot product_name/sku/unit |
| `payments` | payment_number unique, amount DECIMAL(15,2), payment_method, paid_at |
| `payment_methods` | code unique (cash, transfer, qris, debit, credit_card, e_wallet), name, is_active |
| `cash_transactions` | branch_id, merchant_id FK (000021), user_id, type income/expense, category, amount, reference_type/id, transaction_date |
| `cashier_shifts` | branch_id, merchant_id FK (000021), cashier_id, opened_at/closed_at, opening/expected/actual/difference, status |
| `settings` | branch_id nullable, merchant_id FK (000022), key, value TEXT, unique(branch_id,merchant_id,key) (000023) |
| `audit_logs` | user/branch/action/module/reference_type/id/old_value/new_value/IP/UA |
| `refunds`, `whatsapp_logs` | refund_number unique; WA status pending/sent/failed + link |
| `laundry_item_types` | code + merchant_id unique (000023), name, icon, sort_order, status, branch_id nullable, merchant_id FK |

**Aturan DECIMAL:** uang 15,2 • quantity 12,3. Product pcs wajib integer, service boleh desimal 0.001 (kg).

---

## 4) Enums & Models

**Enums (backed string):** `BranchStatus(active,inactive)`, `UserStatus`, `ProductType(product,service)`, `OrderStatus(received,washing,drying,ironing,ready,picked_up,cancelled)`, `PaymentStatus(unpaid,partial,paid)`, `PaymentMethod(cash,transfer,qris,debit,credit_card,e_wallet)`, `CashType(income,expense)`, `ShiftStatus(open,closed)`, `StockMovementType`, `WhatsAppStatus`.

**Model highlights:**
- `Merchant` fillable `code,name,slug,phone,email,address,city,status,owner_user_id`; hasMany `branches/users/customers/orders`, belongsTo `owner`.
- `Branch`/`User`/`Customer`/`Order` fillable tambah `merchant_id,is_demo`; belongsTo `merchant`.
- `Category`/`Product`/`LaundryItemType`/`Setting` fillable tambah `merchant_id`; belongsTo `merchant`; unique per merchant (`code,merchant_id`).
- `CashTransaction`/`CashierShift` fillable tambah `merchant_id`; belongsTo `merchant`.
- `Order` casts `laundry_details => array`; `laundrySummary()` dinamis via `LaundryItemType`. Middleware `BranchContext` + `MerchantContext` set session.

---

## 5) Routes (ringkas)

```
GET  /, /login, POST /login, POST /logout, GET /switch-branch/{id}, GET /switch-merchant/{id}
GET  /dashboard
resource merchants, branches, users (middleware block.demo — demo 403)
resource categories, products, customers (+ /customers-search, /products-search, /api/customers, /api/products)
GET  /pos, POST /pos                           # PosController (products & laundryTypes per merchant)
GET  /orders, GET /orders/{order}, GET /orders/{order}/receipt, GET /orders/{order}/print
POST /orders/{order}/status, /cancel, /payment, /whatsapp
GET  /cash, GET /cash/create, POST /cash
GET  /shifts, POST /shifts/open, POST /shifts/{shift}/close
GET  /reports, /reports/sales, /payments, /cash, /products, /customers, /laundry
GET  /settings, POST /settings
GET  /offline, GET /manifest.webmanifest, GET /sw.js  # PWA (offline page, manifest, service worker)
GET  /api/laundry-types, POST /api/laundry-types, DELETE /api/laundry-types/{id}
GET  /laundry-types, POST /laundry-types, DELETE /laundry-types/{id}
```

`super_admin` (merchant_id NULL) lihat semua merchants; admin toko difilter `where merchant_id = miliknya`. `switch-merchant` hanya Admin (non-super hanya ke merchant sendiri).

Lihat `routes/web.php` untuk daftar lengkap.

---

## 6) Fitur Kunci

### A. POS + Rincian Laundry Dinamis
- **Panel POS** `resources/views/pos/index.blade.php`: search SKU/barcode, chips kategori horizontal, grid produk `grid-cols-2 sm:grid-cols-3 xl:grid-cols-4`; customer search; **Rincian Laundry** collapsed + badge + `laundryGrid flex flex-col gap-2` list vertical (icon+nama+−/＋+input+×), dropdown `laundrySelect` + `+ Tambah` + `Buat baru`.
- **Backend:** `PosController@index` kirim `products` + `laundryTypes` + `customers` semua `when($mid)` (mid dari `auth()->user()->merchant_id`). `OrderService::create()` terima `laundry_details` + `order_status` → simpan ke `orders.laundry_details` JSON + `merchant_id` dari `cashier/branch`.
- **Struk:** `orders/receipt.blade.php` + `print.blade.php` sync, kotak **RINCIAN LAUNDRY** dinamis, tanpa info cabang.

### B. Status Laundry di POS
- **2 button:** `🟡 Baru` (received) default active, `✅ Selesai` (ready). Hidden `input#orderStatus`, helper `setLaundryStatus(v)`.
- **Backend:** `OrderService::create` terima `order_status` (`received`..`picked_up`), default `received`.

### C. Cetak / WA / Close
- `orders/show` bar **Aksi Struk**: Cetak, Kirim WA, Close, Cetak & WA + POS Baru.
- **WhatsappService:** direk `https://web.whatsapp.com/send?phone=62...&text=...` (08→62), fallback `wa.me`, log `whatsapp_logs`, `OrderController@sendWhatsapp` `redirect()->away(link)`.

### D. Demo Isolation
- Migration `000019` `is_demo` di branches/users/customers/orders/cash. `DemoSeeder` DEMO branch + demo users. `BlockDemo` + `BranchContext` paksa DEMO.
- Semua Customer/Order/Dashboard/Report/Cash/Shift filter `where is_demo` + `where merchant_id`.

### E. Multi-Merchant (1 Admin → Sub Admin + Kasir per Toko) — **isolasi penuh per toko**

**DB:** `merchants` + `merchant_id` FK di `branches/users/customers/orders` (000020), `cash_transactions/cashier_shifts` (000021), `categories/products/laundry_item_types/settings` (000022) + unique per merchant `(code,merchant_id)` / `(branch_id,merchant_id,key)` (000023). Backfill LONDRY-001, seed TOKO-002 lengkap (8 kat, 9 produk, 8 laundry, settings).

**Model:** `Merchant` + semua katalog belongsTo `merchant`. `Category/Product/LaundryItemType/Setting/Cash*` semua `merchant_id` fillable.

**Middleware & Auth:** `MerchantContext` aktif di web group; `AuthController@login` set `session(merchant_id)` + `session(branch_id)`; `switchBranch` cek `merchant_id` match; `switchMerchant` hanya Admin.

**Isolasi (pertoko hanya lihat data tokonya):**

| Layer | Scope | Guard |
|-------|-------|-------|
| `MerchantController` | super_admin lihat semua, admin toko `where id = merchant_id` | create hanya super_admin atau tanpa merchant; update/destroy cek `authorizeMerchant` |
| `BranchController` | `where merchant_id` | `authorizeBranch` 403 cross |
| `UserController` | `where is_demo + where merchant_id`, branch list filtered | `authorizeUser` + cek branch merchant di store/update |
| `CategoryController` | `when mid where merchant_id`, create `merchant_id` auto, code cek per merchant | 403 cross di edit/update/destroy |
| `ProductController` | `when mid where merchant_id`, create/edit list kategori filtered, `merchant_id` on create, cek kategori milik toko | 403 cross di edit/update/destroy/show/search |
| `CustomerController` | `when branch_id + when mid + where is_demo`, store `merchant_id` dari branch/user | 403 cross |
| `PosController` | products + laundryTypes + customers semua `when mid` | — |
| `OrderController` | `where merchant_id + where is_demo + when branch_id`, `assertOrderAccess` cek `merchant_id` | 403 cross di show/receipt/print/payment/whatsapp |
| `Dashboard/Report` | `when mid` di semua query (today_customers, cash_income/expense, sales7, byStatus, recent, products, laundry weight) | — |
| `Cash/Shift` | `where merchant_id`, openShift set `mid` dari branch, closeShift filter `when merchant_id` | — |
| `LaundryItemTypeController` | `when mid`, create `merchant_id` auto, code unique per merchant | 403 cross di destroy |
| `SettingController` | `when mid whereNull branch_id`, branch list filtered, update `merchant_id` | — |
| `OrderService` | `merchant_id` dari `cashier->merchant_id ?? branch->merchant_id ?? session(merchant_id)` → `Order` + `CashTransaction` + `StockMovement` | — |

**UI:** Sidebar `Merchant / Toko` dropdown (super admin semua, admin toko single `code — name`), `Cabang Aktif` difilter per merchant, Admin menu `🏪 Merchant`. `users/create` hint `Manager (sub admin)`.

**Super Admin:** `super_admin / superadmin@londry.test / password` (`merchant_id=NULL`, role Admin, `isSuper=isAdmin && merchant_id===null`). Satu-satunya yang bisa `+ Merchant` & lihat semua toko. Buat merchant baru → katalog otomatis ter-seed (SKU `SRV-{mid}-*` / `PRD-{mid}-*`).

**Role per toko:**
- `Admin` (owner) = full access di toko tersebut
- `Manager` (sub-admin) = users.view/create/update, customers, categories/products create/update, pos, orders, payments, cash, reports.view, settings.view (tidak delete user/branch/merchant, tidak settings.update)
- `Kasir` = dashboard, customers, pos, orders, payments, cash, reports.view
- `User` = dashboard, orders.view (limited)
- Demo terpisah via `is_demo`

Verifikasi: TOKO-002 katalog tidak terlihat di LONDRY-001 dan sebaliknya; POS products terseleksi per merchant; order `SBY2-... mid=2` vs `MLG-... mid=1` guard 403 cross OK.

### F. Responsive (HP/Tablet/Laptop)
- `layouts/app.blade.php` sidebar `hidden lg:flex` 260px, drawer mobile, bottom nav 5 tab, `viewport-fit=cover`. POS `flex-col lg:flex-row`, laundry list vertical, tables `overflow-x-auto min-w-[520px]` + cards `sm:hidden`. Struk 58/80mm.

### H. PWA (Installable + Offline Shell)
- **Manifest** `public/manifest.webmanifest`: `display standalone`, `theme #4f46e5`, `background #f8fafc`, icons maskable 72-512, shortcuts POS/Orders/Dashboard.
- **Service Worker** `public/sw.js` cache `londry-v1`: precache `offline/manifest/icons`; `build/icons/manifest` → cache-first, navigations → network-first + offline fallback, `/api/*` → network-first. Update via `skipWaiting` + `clients.claim`.
- **Layouts** `@vite` + `<link rel=manifest>` + `apple-touch-icon` + `theme-color`; `pwa.js` register hanya `https`/`localhost`.
- **Infra:** Vite build `nvm use 24 npm run build` (Tailwind via PostCSS), `public/build` gitignored; Nginx `pwa.conf` types `webmanifest` + `location = /sw.js` `no-cache`.
- **Offline page** `GET /offline` (guest layout).

### G. Order / Payment / Stock / Cash
- **NumberGenerator:** `MLG-YYYYMMDD-000001` / `SBY2-...` / `DEMO-...` per cabang, `PAY-...`, `CUST-...`.
- **OrderService** `DB::transaction`: Order → OrderItems (snapshot) → StockMovement sale → Payment → CashTransaction (merchant_id). Validasi pcs integer, discount ≤ subtotal.
- **Cash/Shift:** `CashService` open/close dengan `merchant_id`.

---

## 7) Progress vs Spec `pos-laundry.md` (55 section, 9 phase)

| Phase (pos-laundry.md §53) | Status | Catatan |
|-----------------------------|--------|---------|
| 1 Foundation (Laravel, DB, Auth, Role/Permission, Branch, Settings) | ✅ Done | Auth username/email, BranchContext+MerchantContext, 4 roles, permissions, settings per merchant, merchants, super_admin |
| 2 Master Data (Customer, Category, Product + SKU/barcode) | ✅ Done | CRUD per merchant (unique per merchant), search scoped, barcode scan → cart |
| 3 POS (Cart, Order, Discount, Payment, Receipt) | ✅ Done | Cart decimal, discount/tax, payment methods, receipt 58/80mm tanpa cabang, status Baru/Selesai, katalog per merchant |
| 4 Laundry flow (OrderStatus received→picked_up, Cancel/Refund) | ✅ Done | Status Baru/Selesai di POS, update status, cancel reverse stock, merchant scoped |
| 5 Cash/Shift, Inventory | ✅ Done | Cash in/out + shift open/close merchant_id, stock per branch |
| 6 Reports (Sales, Payment, Cash, Product, Customer, Laundry) | ✅ Done | 6 report + dashboard graphs scoped per merchant + branch + is_demo |
| 7 WA Receipt | ✅ Done | `WhatsappService` direk `web.whatsapp.com/send` + wa.me fallback, log |
| 8 Rincian Laundry dinamis + masuk struk | ✅ Done | `laundry_item_types` per merchant + JSON, panel POS collapsed list vertical dropdown, struk dinamis |
| 9 Audit Log, Authorization, Security, Performance, Index, Testing | ⚠️ Partial | AuditLogger, policies per branch/merchant/is_demo, validation, DECIMAL, CSRF, hash; index + unique per merchant; BlockDemo; testing manual lolos, automated suite belum |

**Section 1..55 coverage:** 1-51 terimplementasi, 52 testing manual ✅, 53 phases ✅, 54 AI rules diikuti (transaction, DECIMAL, branch/merchant scope), 55 multi-branch+merchant + super admin ready.

**Yang belum / bisa lanjut:**
- Receipt A4 + barcode/QR di struk (setting `receipt_size`, `show_barcode` belum render QR).
- Queue jobs (`SendReceiptWhatsAppJob`) — sekarang sync.
- Rate limiting & upload logo cabang/merchant.
- Automated tests `tests/Feature` untuk §52 (auth, merchant isolation, catalog per merchant).
- Upload logo merchant & per-merchant receipt header (settings sudah per merchant, tinggal UI).

---

## 8) Guideline untuk Penerus

### Tambah Jenis Rincian Laundry
- Via POS: dropdown `+ Tambah` / `Buat baru` → POST `/api/laundry-types`, atau via `/laundry-types`. Code auto slug per merchant (same code boleh beda merchant).
- Via tinker: `LaundryItemType::create(['code'=>'bedcover','name'=>'Bed Cover','icon'=>'🛏️','sort_order'=>20,'status'=>'active','merchant_id'=>auth()->user()->merchant_id])`.

### Tambah Payment Method
- `PaymentMethod::create(['code'=>'dana','name'=>'DANA','is_active'=>true])` (global, belum per merchant).

### Konfigurasi WA
- `settings` keys: `whatsapp_enabled` (0/1), `whatsapp_api_url`, `whatsapp_api_key`. Kosongkan `api_url` untuk pakai `web.whatsapp.com/send` + `wa.me` link saja. Sekarang per merchant (`merchant_id`).

### Seeder — Jangan Salah di Server!
- **Prod home server:** `php artisan db:seed --class=ProductionSeeder` (tanpa demo). Butuh demo tester → `php artisan db:seed --class=DemoSeeder`.
- **Dev:** `php artisan db:seed` (full) atau `migrate:fresh --seed`.
- Semua seeder idempotent (`firstOrCreate` + `syncWithoutDetaching`), aman re-run.
- Tidak ada seeder order dummy.

### Super Admin & Tambah Merchant / Sub Admin / Kasir per Toko

**Super Admin:** `super_admin / superadmin@londry.test / password` (`merchant_id=NULL`, role Admin). Hanya super admin bisa:
- `Merchants` → `+ Merchant` (Kode `TOKO-003`, Nama `Londry Toko 3`) → katalog otomatis terisi (8 kategori, 9 produk `SRV-3-*`, 8 laundry, settings)
- Lihat semua cabang/user lintas merchant, `Switch Merchant`

**Flow tambah toko baru (sebagai super_admin):**
```php
// Via UI (rekomen): Merchants + Branches + Users
// Via tinker:
$merchant = Merchant::create(['code'=>'TOKO-003','name'=>'Londry Toko 3','slug'=>Str::slug('Londry Toko 3').'-toko-003','status'=>'active','owner_user_id'=>auth()->id()]);
// lalu simpan di MerchantController@store akan auto-seed katalog; atau manual:
$m = Merchant::where('code','TOKO-003')->first();
$branch = Branch::create(['code'=>'T3A','name'=>'Cabang Toko 3 A','city'=>'Malang','status'=>'active','merchant_id'=>$m->id]);
$admin = User::create(['name'=>'Admin Toko 3','username'=>'admin_toko3','email'=>'admin3@londry.test','password'=>Hash::make('password'),'branch_id'=>$branch->id,'merchant_id'=>$m->id,'is_demo'=>false]);
$admin->roles()->sync([Role::where('name','Admin')->value('id')]);
$kasir = User::create(['name'=>'Kasir Toko 3','username'=>'kasir_toko3','email'=>'kasir3@londry.test','password'=>Hash::make('password'),'branch_id'=>$branch->id,'merchant_id'=>$m->id,'is_demo'=>false]);
$kasir->roles()->sync([Role::where('name','Kasir')->value('id')]);
$manager = User::create(['name'=>'Manager Toko 3','username'=>'manager_toko3','email'=>'manager3@londry.test','password'=>Hash::make('password'),'branch_id'=>$branch->id,'merchant_id'=>$m->id,'is_demo'=>false]);
$manager->roles()->sync([Role::where('name','Manager')->value('id')]);
```

| Role | Scope | Hak di tokonya |
|------|-------|----------------|
| **Super Admin** (`Admin`, `merchant_id NULL`) | Semua merchant | CRUD Merchant/Branch/User semua toko, switch merchant |
| **Admin** (`merchant_id=X`) | Hanya merchant X | Full access di toko X |
| **Manager** (sub-admin) | Hanya merchant X | Buat/edit Kasir, kelola Produk/Kategori/Customer/Order/POS, laporan — tidak hapus User/Branch, tidak settings.update |
| **Kasir** | Hanya merchant X | dashboard.view, customers, pos.access, orders, payments, cash, reports.view |
| **User** | Hanya merchant X | dashboard.view, orders.view |

Demo terisolasi via `is_demo=1` + DEMO branch — tidak bisa cross ke prod meski satu merchant.

### Isolasi Katalog — Penting!
- `categories/products/laundry_item_types/settings` **sudah per merchant** (`merchant_id` + unique per merchant). Jangan query tanpa `where merchant_id` di controller baru — gunakan `when($mid, fn($q)=>$q->where('merchant_id',$mid))`.
- Kode `LNDRY/CKERING/SRV-.../baju` boleh sama antar toko (beda `merchant_id`), tidak tabrakan.

### Branch/Merchant/Demo Scope — Jangan Lupa!
- Semua query finansial: `->when($branchId, fn($q)=>$q->where('branch_id',$branchId))->when($mid, fn($q)=>$q->where('merchant_id',$mid))->where('is_demo', auth()->user()->is_demo)`.
- `BranchContext` paksa demo ke DEMO, `MerchantContext` set `session(merchant_id)`.

### Responsive — Konvensi
- Tailwind CDN. Breakpoints `sm:640`, `lg:1024`. POS laundry `flex flex-col gap-2` list vertical. Input `py-3` (44px). Tabel `overflow-x-auto min-w-[520px]` + cards `sm:hidden`.

### Validasi Uang & Quantity
- DECIMAL(15,2) uang, DECIMAL(12,3) quantity. Product pcs integer.

---

## 9) Verifikasi Terakhir (real execution, 2026-08-31)

```bash
php artisan migrate:status  # 27 Ran (2014_*:4 + 000001..000023)
php artisan tinker --execute="echo App\Models\Merchant::count();"  # 2
php artisan db:seed --class=ProductionSeeder --force  # idempotent
php artisan db:seed --class=DemoSeeder --force        # idempotent
```

- Prod only (`ProductionSeeder` fresh): branches 2 (MLG,SBY) demo 0, users admin,kasir, customers 3 demo 0, merchants 1 ✅
- After `DemoSeeder`: branches 3, users admin,demo_admin,demo_kasir,kasir, customers demo 4 ✅
- Merchant TOKO-002 (SBY2): merchants 2, branches LONDRY-001→MLG,SBY,DEMO vs TOKO-002→SBY2; users/categories/products/laundry/settings per merchant terpisah (8 cats/9 prods/8 laundry per toko) ✅
- Katalog isolation: `TEST2` toko2 tidak terlihat di toko1; `products_m1=9 m2=9` scoped; `LaundryItemType` per merchant; `Setting` per merchant ✅
- POS scoped: products/laundryTypes/customers difilter `where merchant_id`; order `SBY2-20260831-000001 mid=2` vs `MLG-20260831-000001 mid=1` guard 403 cross ✅
- Super admin `super_admin / password` (merchant_id NULL) `isSuper=yes` lihat semua merchants ✅
- Demo order `DEMO-... is_demo=1`, prod `MLG-... is_demo=0`, cross 403 ✅
- POS status Baru `received` vs Selesai `ready` ✅
- WA `https://web.whatsapp.com/send?phone=62...&text=Halo... STRUK LAUNDRY ... TOTAL` via `web.whatsapp.com` ✅
- Login form bersih tanpa card demo (`resources/views/auth/login.blade.php` hanya form) ✅ (sebelumnya card amber demo)
- Receipt tanpa cabang: hanya `Company name` + `No/Tgl/Kasir/Cust` ✅
- `GET /login 200`, `GET /pos 200`, `GET /dashboard 200` (serve :8010) ✅
- PWA installable: `manifest.webmanifest` 200 `application/manifest+json`, `sw.js` 200 `application/javascript` no-cache, `/offline` 200, `login` render `@vite` + manifest/icons, `GET /offline` via guest layout ✅
- Vite build `nvm use 24 npm run build` css 32KB gzip 6KB js 51KB gzip 19KB, `@vite` di `app/guest` layouts (hapus `cdn.tailwindcss.com`) ✅
- Icons `public/icons/icon-*` 72-512 PNG (PHP GD, maskable) + `apple-touch-icon.png` ✅
- Nginx `pwa.conf` + global `mime.types webmanifest`, `php artisan about` production CACHED (config/route/view/event) ✅
- `php -l` semua seeders/controllers/models OK, `view:clear` OK.

---

## 10) File Penting untuk Dibaca Dulu

1. `pos-laundry.md` — spec
2. `routes/web.php` — peta fitur (+ merchants, switch-merchant; + PWA /offline, /manifest.webmanifest, /sw.js)
3. `app/Services/OrderService.php` — inti transaksi (laundry_details + order_status + merchant_id)
4. `app/Models/Merchant.php` + `Order.php` + `LaundryItemType.php` + `Category.php` + `Product.php`
5. `resources/views/pos/index.blade.php` — POS (list vertical + Baru/Selesai button + dropdown rincian per merchant)
6. `resources/views/auth/login.blade.php` — form login bersih (tanpa card demo) + guest layout `@vite`
7. `resources/views/orders/receipt.blade.php` — struk tanpa cabang + RINCIAN LAUNDRY dinamis
8. `app/Services/WhatsappService.php` + `OrderController@sendWhatsapp` — direk web.whatsapp.com
9. `public/manifest.webmanifest` + `public/sw.js` + `public/icons/` + `resources/js/pwa.js` — PWA
10. `database/seeders/ProductionSeeder.php` + `DemoSeeder.php` + `MerchantSeeder.php`
11. `database/migrations/2024_01_01_000020_create_merchants_and_add_merchant_id.php` + `000021` + `000022` + `000023`
12. `app/Http/Controllers/MerchantController.php` + `app/Http/Middleware/MerchantContext.php`

---

## 11) Troubleshooting

| Gejala | Solusi |
|--------|--------|
| `No such file database.sqlite` | `touch database/database.sqlite` + `php artisan migrate` |
| `laundryTypes is not defined` di POS | Pastikan `PosController@index` kirim `laundryTypes` (`when mid`), Blade ada `let laundryTypes = @json($laundryTypes);` |
| Struk tidak muncul rincian | Cek `Order::latest()->first()->laundry_details`, dan `receipt.blade.php` dynamic block |
| WA tidak kirim API | Kosongkan `whatsapp_api_url` → pakai `web.whatsapp.com/send` + `wa.me` link; cek `whatsapp_logs` |
| View tidak update habis edit | `php artisan view:clear` + hard refresh `Cmd+Shift+R` |
| Port 8010 bentrok | `lsof -i :8010` kill atau `php artisan serve --port=8011` |
| Seeder demo ikut ke produksi | Di server prod jangan `db:seed` (full), pakai `db:seed --class=ProductionSeeder` saja |
| Login tidak ada akun prod | Login manual ketik `admin/password`, `kasir/password`, `super_admin/password` — login tidak tampilkan info akun |
| Struk masih tampil cabang | `receipt.blade.php` sudah hapus — `view:clear`, cek bukan cache `print.blade.php` |
| `UNIQUE constraint failed: categories.code` | Sudah diperbaiki 000023: unique jadi `(code,merchant_id)` — `php artisan migrate --force` |
| Toko baru tidak ada produk/kategori | Merchant baru auto-seed via `MerchantController@store`; untuk tinker lama jalankan `php artisan migrate` akan backfill TOKO-002 |
| Admin toko lihat data toko lain | Seharusnya tidak — semua controller sudah `when merchant_id`; cek `auth()->user()->merchant_id` terisi, `MerchantContext` aktif di Kernel web group |
| Tidak bisa buat merchant | Hanya `super_admin` (merchant_id NULL) bisa `+ Merchant`; admin toko (`merchant_id terisi`) akan 403 |
| PWA tidak installable | Cek `https`, `manifest.webmanifest` `application/manifest+json`, `sw.js` ter-register di DevTools Application → Manifest/Service Workers |
| `Vite manifest not found` | Jalankan `bash -lc 'source ~/.nvm/nvm.sh; nvm use 24; npm run build'` di server, `public/build` gitignored |
| `sw.js` 404 atau MIME salah | Cek `public/sw.js` ada, Nginx `pwa.conf` + `mime.types webmanifest` terpasang, `curl -I https://pos.azelsq.my.id/sw.js` |
| Offline tidak muncul | `GET /offline` harus 200 (guest), `sw.js` precache `OFFLINE_URL`, buka DevTools → offline checkbox |

---

*Last updated: 2026-08-31 (PWA installable + offline shell — Tailwind via Vite, manifest + SW londry-v1, icons, @vite layouts, nginx pwa.conf — plus hapus card demo & fix route:cache) — oleh Hermes Agent. Jangan commit `.env` & `database.sqlite` ke repo publik. Seeder Production/Demo idempotent, aman re-run.*

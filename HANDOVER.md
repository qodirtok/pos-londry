# Handover — Londry POS Laundry (Laravel 10, PHP 8.1)

> Ringkasan struktur, progress, dan guideline untuk penerus. Spec sumber: `pos-laundry.md` (55 section).
> Stack: **Production**: Laravel 10 • PHP 8.2-FPM • **MySQL** (`pos_londry`) • **nginx port 80** • Tailwind via Vite • Blade • PWA (manifest + Service Worker + offline)  
> Stack: **Dev lokal**: Laravel 10 • PHP • SQLite (`database/database.sqlite`) • `php artisan serve` (default :8000)  
> Build frontend: `nvm use 24 && npm run build` (output: `public/build/`) — `public/build` **tidak** dilacak git (gitignored)

---

## 1) Cara Jalan Cepat (5 menit)

### Production (server VPS, nginx + MySQL + port 80)
```bash
# nginx sudah berjalan di port 80, PHP-FPM 8.2, MySQL sudah terhubung
cd /root/www/pos-londry
php artisan config:cache
php artisan route:cache
php artisan view:cache          # opsional, untuk performa
# buka di browser: http://localhost atau http://<IP-server>
```

### Dev lokal (SQLite, artisan serve)
```bash
cd /Users/zlns/personal-www/londry   # repo lokal
php artisan serve --host=127.0.0.1 --port=8000   # Laravel serve
npm run dev                                       # Vite HMR port 5173 (WAJIB jalan, jangan npm run build)
# login http://127.0.0.1:8000/login — kasir/password
# JANGAN: npm run build (hanya prod)
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
│   ├── orders/show.blade.php      # detail order + rincian dinamis + bar Aksi Struk (Cetak/WA/Close/Cetak&WA) + **Edit di POS** (/pos/edit/{id}) + Edit Cepat modal + status dropdown rollback ready→received
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
- `Order` casts `laundry_details => array`; **`isLaundry(): bool`** helper (true bila `laundry_details` terisi atau ada item `type=service`); `laundrySummary()` dinamis via `LaundryItemType`. Middleware `BranchContext` + `MerchantContext` set session.

---

## 5) Routes (ringkas)

```
GET  /, /login, POST /login, POST /logout, GET /switch-branch/{id}, GET /switch-merchant/{id}
GET  /dashboard
resource merchants, branches, users (middleware block.demo — demo 403)
resource categories, products, customers (+ /customers-search, /products-search, /api/customers, /api/products)
GET  /pos, POST /pos                           # PosController (products & laundryTypes per merchant); qty check removed (stok boleh minus)
GET  /pos/edit/{order}, POST /pos/edit/{order} # Edit order via POS (items/qty/harga/diskon/laundry/status) — POS-style full edit
GET  /orders, GET /orders/{order}, GET /orders/{order}/receipt, GET /orders/{order}/print
POST /orders/{order}/status, /cancel, /payment, /customer, /items, /whatsapp
GET  /settings, POST /settings                   # SettingController (app/company/currency/struk); GET /settings/backup (Admin only) → mysqldump zip download
GET  /antrian, POST /antrian/{order}/update    # Antrian (laundry received) — uses OrderService::updateStatus (rollback allowed)
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
- **Panel POS** `resources/views/pos/index.blade.php`: search SKU/barcode, chips kategori; grid produk; customer search; **Rincian Laundry** modal popup + list vertical; status Baru/Selesai; **Mode Edit** (`GET /pos/edit/{order}`) pre-fills cart/customer/laundry → tombol `SIMPAN PERUBAHAN` (POS-style full edit item/qty/harga/diskon/laundry/status).
- **Backend:** `PosController@index` kirim `products` + `laundryTypes` + `customers` semua `when($mid)` (mid dari `auth()->user()->merchant_id`). `OrderService::create()` terima `laundry_details` + `order_status` → simpan ke `orders.laundry_details` JSON + `merchant_id` dari `cashier/branch`.
- **Struk:** `orders/receipt.blade.php` + `print.blade.php` sync, kotak **RINCIAN LAUNDRY** dinamis, tanpa info cabang.

### B. Status Order (laundry 3, product → complete)
|  - **Laundry flow:** `received → ready → picked_up` (3 status). `complete` otomatis untuk penjualan barang (product-only).
|  - **POS:** btn Baru (received) / Selesai (ready), hidden `orderStatus`. Product-only auto `complete`. Stok boleh minus (qty check removed).
|  - **Rollback:** `ready → received` **diperbolehkan** (koreksi input) di orders/show, `/antrian/{order}/update`, & POS mode edit. `picked_up/complete/cancelled` terkunci.

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
| `PosController` | products + laundryTypes + customers semua `when mid`. Methods: `index()`, `edit(Order)` (preload + POS view mode edit), `store()` (qty check removed — stok boleh minus), `update()` (`POST /pos/edit/{order}` → `OrderService::updateFromPos()`). Routes: `GET|POST /pos/edit/{order}`. | — |
| `OrderController` | `where merchant_id + where is_demo + when branch_id`, `assertOrderAccess` cek `merchant_id`. Branch fallback `session('branch_id') ?? auth()->user()->branch_id`. New: `updateItems()` (POST `/orders/{order}/items`). | 403 cross di show/receipt/print/payment/whatsapp/edit |
| `Dashboard/Report` | `when mid` di semua query (today_customers, cash_income/expense, sales7, byStatus, recent, products, laundry weight) | — |
| `Cash/Shift` | `where merchant_id`, openShift set `mid` dari branch, closeShift filter `when merchant_id` | — |
| `LaundryItemTypeController` | `when mid`, create `merchant_id` auto, code unique per merchant | 403 cross di destroy |
| `SettingController` | `when mid whereNull branch_id`, branch list filtered, update `merchant_id` | — |
| `OrderService` | `merchant_id` dari `cashier->merchant_id ?? branch->merchant_id ?? session(merchant_id)` → `Order` + `CashTransaction` + `StockMovement`. Methods: `create()`, `updateStatus()` (rollback `ready→received` allowed, product auto-`complete`, 3 laundry statuses), `updateItems()` (edit blocked saat ready/picked_up/complete/cancelled), `updateFromPos()` (full edit via POS), `cancel()`, `addPayment()`. Qty check removed → stok boleh minus. | — |

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
- **Mobile cart drawer (POS)**: panel keranjang `#cartDrawer` di `/pos` — di desktop tetap kolom kanan 400px; di mobile (<1024px) jadi bottom-sheet tersembunyi (`position:fixed;bottom:0;height:82vh;transform:translateY(105%)`), dibuka via FAB `#cartFab` (indigo kanan-bawah + badge jumlah qty) + overlay `#cartDrawerOverlay`. Buka/tutup via `toggleCartDrawer()` (toggle `.cart-open` + `.show` + `body.cart-open` scroll lock; tutup juga membersihkan modal `is-open`), badge disinkron `updateCartUI()` di `renderCart`/`calc()`. **PENTING**: media query desktop (`min-width:1024px`) wajib reset `#cartDrawer` ke `position:relative!important;transform:none!important` dll — kalau tidak, style fixed mobile bocor ke desktop dan menghancurkan layout 2-kolom POS. Z-index drawer 60 < modal backdrop 80 → checkout/receipt modal tetap tampil di atas drawer.

### H. PWA (Installable + Offline Shell)
- **Manifest** `public/manifest.webmanifest`: `display standalone`, `theme #4f46e5`, `background #f8fafc`, icons maskable 72-512, shortcuts POS/Orders/Dashboard.
- **Service Worker** `public/sw.js` cache `londry-v1`: precache `offline/manifest/icons`; `build/icons/manifest` → cache-first, navigations → network-first + offline fallback, `/api/*` → network-first. Update via `skipWaiting` + `clients.claim`.
- **Layouts** `@vite` + `<link rel=manifest>` + `apple-touch-icon` + `theme-color`; `pwa.js` register hanya `https`/`localhost`.
- **Infra:** Vite build `nvm use 24 npm run build` (Tailwind via PostCSS), `public/build` gitignored; Nginx `pwa.conf` types `webmanifest` + `location = /sw.js` `no-cache`.
- **Offline page** `GET /offline` (guest layout).

### G. Order / Payment / Stock / Cash
### I. Database Backup (Settings → Backup Database (Admin))

- **Route**: `GET /settings/backup` → `SettingController@backup` (name `settings.backup`), di dalam auth + `block.demo` middleware group.
- **Akses**: hanya role `Admin` (`User::hasRole('Admin')`). Non-admin → `abort(403)`. Kasir melihat halaman Settings tapi tombol backup munya, klik → 403.
- **Backend** (`SettingController@backup`):
  - Baca `config('database.connections.mysql')` untuk host/port/db/username/password.
  - Resolve `mysqldump` binary: path absolut brew (`/opt/homebrew/opt/mysql-client/bin/mysqldump`), fallback `/usr/bin/mysqldump` + `command -v mysqldump`.
  - Jika tidak ketemu → redirect back dengan error *Backup gagal: mysqldump binary tidak ditemukan*.
  - Jalankan dump via `Symfony\Component\Process\Process` → file `storage/app/backups/db_backup_<YYYY-mm-dd_His>.sql`.
  - Zip dengan `ZipArchive` → `db_backup_<timestamp>.zip` (hapus .sql asli).
  - Return `Storage::disk('local')->download($file)` → response `Content-Disposition: attachment; filename=...zip`, magic bytes `PK`.
  - Guard kosong: kalau dump gagal/empty → hapus file, redirect back error.
- **Frontend** (`resources/views/settings/index.blade.php`): tombol amber "Backup Database (Admin)" + `<p>` kecil. Tidak perlu tambahan JS; link langsung trigger download.
- **Notes**: hanya MySQL (production & local dev). Untuk SQLite dev, dump via `sqlite3` belum di-support. Semua file backup tak dilacak git (`storage/app/backups/` gitignored).
- **Production pitfall**: `file_exists()` pada candidate path (`/opt/homebrew/...`) diblokir `open_basedir`, melempar `ErrorException` → 500. Solusi: (1) tambah `/usr/bin:/bin` ke `.user.ini` `open_basedir` supaya binary bisa ditemukan & dieksekusi; (2) gunakan `@file_exists()` pada semua probe supaya open_basedir blockage return `false` bersih, bukan Exception. `mysqldump` di production server adalah symlink `/usr/bin/mysqldump → mariadb-dump`.

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
- Tailwind via **Vite** (bukan CDN sejak 2026-09). Build: `npm run build`; dev: `npm run dev`. Breakpoints `sm:640`, `lg:1024`. Input `py-3` (44px min tap target). Tabel `overflow-x-auto` + cards `sm:hidden`.

### Validasi Uang & Quantity
- DECIMAL(15,2) uang, DECIMAL(12,3) quantity. Product pcs integer.

### Warna & Tipografi — Baca DESIGN.md DULU
- **Arah desain ada di `DESIGN.md`.** Jangan mulai ubah tampilan tanpa membacanya. Semua keputusan warna/tipografi/radius ada di sana beserta alasannya.
- **Warna lewat token, bukan hex langsung.** Aksen: `teal-600` (#0f766e). Netral: `paper-*`. Status: `emerald` (sukses/lunas), `amber` (siap diambil / belum bayar), `rose` (error / wajib diisi). Jangan pakai warna baru tanpa alasan tertulis.
- **Aturan aksen**: teal hanya di tombol aksi utama, angka total, dan link aktif. Kalau halaman terasa terlalu banyak teal, aksennya bocor.
- **Anti-pattern yang sudah dibuang** (jangan dikembalikan tanpa alasan): indigo sebagai warna brand, emoji sebagai icon, radius 16px seragam, Inter/Google Fonts, gradient sebagai warna utama, glow, grid/dot pattern di latar, dark mode sebagai gaya, `rounded-full` di semua elemen.
- **Icon**: pakai `icon('nama')` dari `app/helpers.php` (SVG line-icon). Kalau butuh icon baru, tambahkan ke set PHP itu — bukan bikin SVG inline ad-hoc di view.
- **Kontras**: WAJIB ukur sebelum dipakai. Teks kecil min 4.5:1, teks besar min 3:1. Warna netral baru harus dicek terhadap background yang benar-benar dipakai, karena abu terang di atas terang GAGAL sedangkan abu terang di atas gelap LOLOS (lihat pitfall sidebar di §9).
- **Angka rupiah** pakai `tabular-nums` (sudah default di body). Jangan pakai font web baru — system font dipakai demi alasan kecepatan (lihat DESIGN.md §3).

### UX untuk Kasir Pemula
- Audience-nya kasir laundry, banyak yang non-teknis, berdiri, dan sering di bawah matahari. Konsekuensi: target sentuh besar, kontras tinggi, tidak ada jargon.
- **Jujur soal langkah berikutnya.** Jangan tulis "BAYAR & CETAK" kalau yang terjadi cuma buka modal. Label tombol = apa yang benar-benar terjadi.
- **Sembunyikan yang biasanya tidak dipakai.** Diskon/pajak/warga tidak wajib → di balik toggle, tampil hanya saat dipakai. Field kosong yang selalu tampil bikin ragu.
- **Peringatkan sebelum, bukan sesudah.** Instruksi tampil dari awal (misal "Belum ada customer dipilih"), bukan muncul setelah gagal. Error setelah kejadian tetap ada, tapi jangan-andalkan itu saja.
- **Hindari kata ambigu.** "Baru/Selesai" tidak jelas; pakai "Masih dicuci/Sudah selesai".
- **Empty state** = kenapa kosong + apa yang harus dilakukan berikutnya. Bukan "No data".
- **Hindari emoji di teks UI** dan ikon dekoratif. Emoji ≠ tidak cocok untuk UI ini, bukan karena Tren, tapi karena rendering beda per OS dan menyentuh hierarki visual.

---

### 2026-09-26 — Rincian laundry POS untuk kasir pemula, plus guard em dash yang diperlebar
- **Rincian laundry** (`pos/index.blade.php` + `LaundryItemTypeController`): baris laundry dirakit ulang pakai `createElement`/`textContent` (sebelumnya `innerHTML` + nama mentah, jadi tag di nama jenis jadi DOM). Nama jenis kini ditolak di server juga lewat `not_regex`, bukan cuma bergantung pada penanganan sisi klien. Tombol step dan hapus dinaikkan ke target sentuh 2.5rem, dan footer modal stack penuh di bawah 640px supaya label "Simpan dan Tutup" tidak terpotong di ponsel sempit.
- **Copy dibetulkan sesuai aturan "label = apa yang benar-benar terjadi"**: "Simpan & Tutup" jadi "Simpan dan Tutup", "Ket. Lainnya" jadi "Keterangan lain", dan ditambahkan catatan bahwa rincian laundry hanya catatan struk, jadi tidak mengubah total.
- **Celah guard em dash ditemukan dan ditutup**: `test_no_em_dash_in_any_rendered_ui_text` hanya memindai halaman preview, padahal copy baru masuk lewat view dan controller. Akibatnya 17 baris em dash menetap di POS, detail order, sidebar, halaman offline, form user, dan pesan WhatsApp tanpa suite complains. Semua dibersihkan, dan test baru `test_no_em_dash_anywhere_in_frontend_or_app_sources` menyapu `resources/{views,css,js}` plus `app/Controllers` dan `app/Services`. **Guard ini sudah dibuktikan menangkap bug**: em dash ditanam di `products/index.blade.php` -> test gagal, dipulihkan -> test hijau.
- Markdown (`HANDOVER.md`, `DESIGN.md`, `CLAUDE.md`) sengaja tidak ikut sapuan itu, karena em dash di sana dipakai sebagai pemisah tabel dan tidak pernah sampai ke layar kasir. Test hanya memverifikasi dokumen itu masih ada.
- Verifikasi: `php artisan test` **26 passed (188 assertions)**, `npm run build` OK, `php -l` bersih 5 file, `node --check` `app.js` OK, 6 route 200 lewat login HTTP, dan audit HTML ter-render: palet terlarang 0, em dash 0, CJK 0, kebocoran docblock 0.

## 9) Recent Changes (2026-09-02/03)

### 2026-09-26 — Component layer (Blade) + halaman preview, dengan guard anti-regresi
- **Kenapa**: tombol, badge, field input, dan tabel masih ditulis ulang di tiap view. 4 halaman punya versi "loading" dan "error" yang beda-beda, jadi tidak konsisten dan mudah salah. Sekarang ada satu sumber.
- **`resources/views/components/`** (9 file Blade): `button`, `badge`, `field`, `table`, `th`, `td`, `table-row`, `card`, plus `components-preview.blade.php` yang merakit semuanya. Preview ada di route **`/components-preview`**, **di luar middleware auth** (supaya bisa dibuka tanpa login) tapi **di-404-kan di production** supaya tidak pernah tampil ke kasir.
- Aturan yang dipegang: komponen tidak boleh bringing own color/hex (pakai token `paper-*`/`teal-*`), tidak boleh pakai emoji, tidak boleh pakai em dash, dan label tombol = apa yang benar-benar terjadi.
- **Bug yang ketemu & diperbaiki di sesi ini**: docblock PHP di 8 file komponen bocor ke HTML output (`/** ... */` tampil di atas markup). Test `test_component_templates_do_not_leak_php_docblocks` guardingnya sudah ditulis dan **sudah dibuktikan menangkap bug** (sengaja dirusak 1 file → test gagal, dipulihkan → test hijau lagi).
- **Test baru `tests/Feature/ComponentLayerTest.php`** (21 test / 176 assertion total suite): variant tombol, focus ring terlihat untuk keyboard, tone badge bermakna, label field di atas input + wire error, tabel punya empty/loading/error state, `data-label` untuk mode card, `<th>/<td>` valid di dalam `<table>`, tidak ada em dash, tidak ada label bahasa Inggris di copy, komponen pakai token bukan hex, preview diblokir di production, dan tidak ada kebocoran docblock.
- Verifikasi: `php artisan test` **21 passed (176 assertions)**, `npm run build` OK, screenshot desktop + mobile `components-preview` dicek bersih.

### 2026-09-26 — Fix 6 bug yang memblokir kasir (audit anti-slop 001)
Audit dulu: `anti-slop/audit-001-2026-09-26.md` (15 temuan, 6 di antaranya HIGH / Hard Gate). Semuanya bug fungsi nyata, bukan estetika.
- **`saveNewLaundryType()` crash** (`pos/index.blade.php:1304`): fungsi tanpa parameter tapi manggil `e.target.querySelector(...)` → `ReferenceError` sebelum `fetch`. Tombol "Simpan" di modal *Jenis Laundry Baru* mati total. Fix: `document.getElementById('ltSaveBtn')` + guard null.
- **Enter di form customer = data hilang** (`:438`): `<form id="newCustomerForm">` tidak punya tombol submit di dalamnya, tombolnya di footer modal → Enter = native submit = reload, input hilang. Fix: tombol jadi `type="submit" form="newCustomerForm"` (atribut `form` mengikat tombol luar form ke form, tanpa membongkar struktur modal).
- **`addLaundryTypeToOrder()` panggil elemen mati** (`:1303`): `laundryPanel` + `toggleLaundry()` sudah dihapus dari DOM (panel pindah ke modal). Diam-diam tidak error. Fix: hapus 2 baris.
- **`clearLaundry()` dobel** (`:849` & `:976`): definisi kedua menimpa yang pertama; yang menang **tidak** hapus localStorage → tombol "Kosongkan rincian" tidak benar-benar mengosongkan, dan draft order lama muncul lagi di order berikutnya. Fix: hapus duplikat, sisakan yang juga `localStorage.removeItem(LAUNDRY_KEY)`.
- **Kontras gagal AA** (`pos/index.blade.php` CSS): `#94a3b8` di atas `#f1f5f9` = **2.34:1** (butuh 4.5), dipakai di `.pos-empty` (label "Keranjang kosong"), `.ci-remove`, `.ll-remove`, `.pos-search-icon`. 8 selector diganti ke `#475569` (6.92:1) / `#64748b` (4.72:1).
- **Flash message tak pernah tampil** (`layouts/app.blade.php:40-70`): session flash + validation error dikirim ke `console.log` saja, nol markup. Kasir tidak pernah tahu "berhasil disimpan". Fix: toast sungguhan, `role="status"` + `aria-live="polite"`, auto-dismiss 6 detik untuk sukses, error/warning nunggu ditutup manual.
- **XSS yang saya bawa sendiri lalu perbaiki**: flash sempat ditulis `{!! $m !!}`. `LaundryItemTypeController@store:37` menempelkan `$name` dari user ke flash, validasinya cuma `string|max:30` (tidak menyaring HTML). Sebelumnya aman karena hanya `json_encode` ke console; merender ke DOM = stored XSS. Fix: `{{ $m }}`. Terverifikasi: payload `<img src=x onerror=alert(1)>` → ter-render jadi `&lt;img...&gt;`, tag tidak terbentuk.
- **Bug yang saya buat lalu perbaiki di sesi sama**: script toast sempat di `<head>` tanpa `DOMContentLoaded` → `querySelectorAll` jalan sebelum body ada, auto-dismiss tidak pernah jalan.
- Verifikasi: `php -l` bersih 2 file, `npm run build` OK, `phpunit` **7/7 OK (26 assertions)**, `node --check` inline script POS OK, `php artisan serve` + curl → `GET /pos` 200 dan keenam fix terverifikasi di HTML render.

### 2026-09-26 — Redesign visual: indigo → teal, emoji → SVG, system font, dashboard baru
Arah desain tertulis di **`DESIGN.md`** (wajib dibaca sebelum ubah UI). R-37 mensyaratkan arah eksplisit sebelum building.
- **Audit**: `anti-slop/audit-001-2026-09-26.md`. Temuan 7-11 (MEDIUM) dikerjakan di sini.
- **`DESIGN.md`** (baru): identitas (POS laundry untuk kasir yang berdiri, audience non-teknis), palet (kertas `#FFFDF9` / tinta `#2B2320` / aksen teal `#0F766E` / status amber `#B45309`), tipografi, radius, motif identitas ("kartu bertanda": nomor order mono + garis putus-putus), motion, dials **ENERGY 1 / RHYTHM 2 / MOTION 1**.
- **`tailwind.config.js`**: tambah palet `teal` (9 shade) + `paper` (9 shade + `on-dark`/`on-dark-dim`) + `borderRadius` (sm 4px, md 6px, 2xl 12px) + `fontFamily` system. **`borderRadius.DEFAULT` sengaja TIDAK diubah** — `rounded` bawaan Tailwind (8px) sudah cocok untuk kartu; mengubah DEFAULT akan diam-diam mengubah `rounded` di semua halaman.
- **Palet**: 126 kemunculan kelas indigo (7 shade) + hex `#4f46e5/#4338ca/#818cf8/#6366f1` + rgba `79,70,229` di **34 file** diganti teal. Termasuk `theme-color` meta, SweetAlert `confirmButtonColor`, shadow rgba. Semua shade teal diuji kontrasnya sebelum dipakai (`#0f766e` di putih = 5.47:1, di `#f0fdfa` = 5.25:1).
- **Netral slate → paper** di semua view. **Pitfall yang dihindari**: mapping awal `text-slate-400` → `paper-500` pertama = **2.47:1** (gagal AA). Palet paper ditulis ulang ke `#6b6357` (5.54:1) sebelum skrip dijalankan, jadi tidak sempat merusak apa pun. **Pitfall kedua**: sidebar berlatar gelap butuh abu terang — `paper-500` di `#1a1512` hanya 3.06:1, jadi 18 label sidebar diperbaiki ke shade baru `paper-on-dark` (`#a89e8f`, 6.86:1). Pergantian **dibatasi baris 79-142**; mobile topbar (baris 195) dan bottom nav (baris 211) berlatar putih dan tidak boleh ikut diganti.
- **[Koreksi 2026-09-26] Klaim "semua view" di atas tidak berlaku penuh.** Scan ulang menemukan inline CSS di `pos/index`, `orders/show`, `queue/index`, dan `products/index` masih memuat hex slate (`#0f172a`, `#475569`, `#64748b`, `#94a3b8`, `#e2e8f0`, `#f1f5f9`, `#f8fafc`, `#fafbfc`) karena penggantian pertama hanya menyapu class Tailwind, bukan CSS inline. Semuanya sudah diganti ke token paper di commit `13ef0a2`.
- **[Koreksi 2026-09-26] Guard lama tidak menutup celah ini.** `test_components_use_paper_and_teal_tokens_not_raw_hex` hanya menyapu `views/components/`, jadi hex bocor di view lain lolos. Diganti `test_no_banned_palette_colors_survive_anywhere_in_frontend` yang memindai **seluruh** `resources/views` + `css` + `js` + `tailwind.config.js` terhadap 17 hex indigo/slate, plus `test_paper_and_teal_tokens_are_defined_in_tailwind_config` (kalau token dihapus, semua halaman jatuh ke warna default tanpa error). Guard baru langsung menangkap satu yang tertinggal: ikon empty-state di `products/index` masih `#cbd5e1`. Diukur di atas `bg-white` = **1.48:1**, di bawah batas 3:1 untuk grafik, jadi diganti `paper-500` (**5.92:1**). `paper-400` dicoba lebih dulu dan ditolak, hanya 1.59:1.
- **Pelajaran untuk sesi berikutnya**: ganti warna harus menyapu Tailwind class **dan** inline CSS `<style>` di view, karena view ini punya dua sumber warna sekaligus. Verifikasi dengan grep hex, bukan dengan menyapu class Tailwind saja. Dan guard harus masuk ke seluruh sumber yang bisa menyimpan warna, bukan hanya ke direktori yang paling mudah dijaga.
- **Icon**: `app/helpers.php` `icon()` ganti dari emoji (`📊🧾📋👥📦💰…`, 38× dipakai di layout) ke **SVG line-icon stroke 1.5** server-side, 30 icon. Semua nama yang dipakai terdaftar. Helper menerima `size`/`class`/`style`/`color`/`label` (backward-compat dengan `offline.blade.php` yang kirim `color`). Alasan stroke 1.5 bukan 2: supaya icon tidak terlihat lebih berat dari teksnya. `resources/js/app.js` masih punya set JS terpisah untuk sisi browser — **dua sumber icon**, belum disatukan.
- **Tipografi**: Inter (AI default, dari Google Fonts) diganti system font stack `system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',sans-serif` + `font-variant-numeric: tabular-nums`. Alasan: POS dipakai di koneksi Indonesia yang tidak selalu stabil, jadi font harus tampil tanpa menunggu unduhan; system font sudah punya angka tabular untuk kolom rupiah. Google Fonts request dihapus dari `layouts/app.blade.php` dan `layouts/guest.blade.php`.
- **Radius**: `rounded-2xl` 16px → 12px. Pill hanya filter kategori + FAB. Kartu pakai `rounded` (8px).
- **Dashboard dibangun ulang** (`dashboard.blade.php` + `DashboardController`): sebelumnya persis template default (4 stat card seragam → chart → tabel). Sekarang: omzet sebagai blok utama, lalu **kolom kiri 2/3 = antrian kerja (fokus)**, kolom kanan 1/3 = kas/piutang/penjualan/status. Alasan: omzet dipantau akhir hari, antrian itu pekerjaan yang sedang berjalan. Order `ready` dipisah dari `received` (aksinya berbeda: hubungi customer) → controller tambah query `readyList`, dan `queueList` limit 5 → 20. Empty state menyebut kenapa kosong + apa selanjutnya, bukan "No data".
- Verifikasi: `npm run build` OK, `phpunit` 7/7, 13 route load 200, SVG 38-89/halaman, emoji 0, indigo 0. Scan 446 kelas: 70 "missing" adalah class kustom inline (`pos-cart-item`, `ll-name`, `nav-link`, dll) yang memang begitu sejak awal, bukan bug.

### 2026-09-26 — Cart drawer POS dirombak untuk kasir pemula
- **Kontrol kartu**: `.pos-cart-item` dari 1 baris inline jadi **dua tingkat** (`.ci-top` nama+harga / `.ci-bottom` kontrol). Tombol ± 30px → **40px** (2.5rem), tombol hapus ikon tong sampah 30px → **bertulis "Hapus"** dengan border. Alasan: kasir sambil berdiri, target sentuh kecil + ikon easy-to-miss adalah sumber salah ketuk.
- **Tombol bayar tidak berbohong**: "BAYAR & CETAK" → **"Lanjut ke pembayaran"**, karena yang terjadi cuma buka modal checkout, tidak mencetak. Ditambah keterangan "Struk dan pilihan cetak muncul di layar berikutnya." (Jujur soal langkah berikutnya lebih baik daripada janji yang tidak ditepati.)
- **Diskon & pajak disembunyikan** di balik baris "Tambah diskon atau pajak" (`toggleOptionalFields()`), tombolnya **hanya muncul saat cart berisi barang** (`syncOptionalVisibility()`). Begitu ada isinya, baris `#discountLine`/`#taxLine` muncul di area total supaya hasil langsung terlihat tanpa menggulir. Alasan: field kosong yang selalu tampil bikin pemula ragu "isi atau nggak?".
- **Kembalian kondisional**: `#changeRow` tampil hanya kalau `change > 0`. "Rp 0" yang selalu tampil cuma noise.
- **Status dihafalkan**: "Baru"/"Selesai" → **"Masih dicuci"/"Sudah selesai"** + helper "Pilih saat laundry sudah selesai". "Baru" ambigu: status cucian atau status order?
- **Customer tidak reaktif**: kotak "Belum ada customer dipilih" (rose) tampil dari awal, bukan muncul setelah gagal bayar. Tombol hapus (`clearCustomer`) disembunyikan saat belum ada pilihan. Emoji `⚠️` dibuang dari teks UI.
- **Teks yang salah arah**: "Tap produk di atas" → "Ketuk produk untuk menambah" (salah di mobile, drawer menutupi produk). "Expand untuk isi pcs" → "Isi jumlah per jenis, misalnya Baju 3 pcs" (itu modal, bukan expand).
- **`javascript:void(0)` dihapus** (temuan audit #13) → `href="#" onclick="event.preventDefault();..."`.
- **Pitfall JS**: `MutationObserver` dengan `attributeFilter:['value']` **tidak pernah memicu** — `value` bukan DOM attribute untuk input. Diganti `input` event listener yang sudah ada.
- ID yang dipakai `PosPageTest` (`cartDrawer`, `cartFab`, `cartDrawerOverlay`, `cartCount`, `toggleCartDrawer`, `updateCartUI`) **tidak disentuh** → 7/7 test tetap lulus. Kontras tombol baru: 5.47:1, rose 7.3:1, teal muda 7.27:1.

### 2026-09-14 — Tombol "Tandai Lunas" (Paid) di detail order + fix bug "Sesi anda telah habis" palsu
- **`resources/views/orders/show.blade.php`**: tombol emerald **"Tandai Lunas — Bayar Rp {sisa}"** di section Pembayaran untuk order belum `paid` & tidak `cancelled` — sekali klik submit hidden form `amount` = sisa total + `payment_method=cash` ke `orders.payment`, `OrderService::addPayment` otomatis cap → status `paid`. Form "Tambah Bayar" manual tetap ada di bawahnya.
- **Bug fix (semua form di orders/show)**: pesan palsu "⚠️ Sesi anda telah habis" saat submit sukses. Akar: `handleSubmitForm` memakai heuristik `text.includes('login')` untuk deteksi session expired — tapi halaman orders/show **sendiri berisi kata "login"** dari kode alert itu → selalu true → false positive. `fetch` juga sudah follow redirect `back()`. Fix: cek `content-type` JSON dulu; non-JSON + `res.redirected` → cek `res.url.includes('/login')` untuk session expired, selain itu `location.reload()` (flash success tampil normal); 419 → sesi habis. Heuristik `text.includes('DOCTYPE')`/`text.includes('login')` dihapus.
- Verifikasi browser: klik "Tandai Lunas" order partial Rp 8.000 (bayar 1.000) → **tanpa alert sesi habis**, status `paid`, Dibayar Rp 8.000 ✓. `view:cache` OK, `php artisan test` 7 passed.

### 2026-09-14 — Unit test + UI/UX test POS (mobile cart drawer)
- **`phpunit.xml`**: aktifkan `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` agar test tidak menyentuh DB dev/prod. (Sebelumnya commented-out → `RefreshDatabase` berbahaya.)
- **`tests/Feature/PosPageTest.php`** (baru, 6 test): `/pos` butuh login; `/` redirect saat unauthenticated; render POS berisi `#cartDrawer/#cartFab/#cartDrawerOverlay/#cartCount` + `toggleCartDrawer/updateCartUI` + `cart-open`; CSS media queries `@media(max-width:1023.5px)` sebelum `@media(min-width:1024px)` + `#cartDrawer.cart-open` + `#cartFab.hidden`; `POST /pos` bikin order (kasir, product). Seed via `ProductionSeeder` (RefreshDatabase).
- **`tests/Feature/ExampleTest.php`**: diperbaiki dari asumsi `/` 200 → 302 redirect (root butuh auth).
- **Bug UX ditemukan & diperbaiki** (`pos/index.blade.php`): setelah checkout sukses, mobile drawer masih `cart-open` + FAB masih tampil (cart sudah dikosongkan tapi UI tidak di-reset). Fix: di `confirmCheckout()` setelah `closeModal('modalCheckout')` tambah reset `.cart-open`/`.show`/`body.cart-open` + FAB hidden + cartCount 0.
- **Verifikasi browser (UI/UX)**: mobile 390×844 — FAB hidden saat cart kosong → muncul `flex` count 1 setelah tambah produk → tap FAB drawer slide-up (`translateY(0)`) + overlay + body scroll lock → tap overlay tutup ✓. Desktop 1440×900 — FAB none, `#cartDrawer` `position:relative;width:400px;transform:none`, item inline ✓. Checkout end-to-end dari drawer: pilih customer → BAYAR → modal checkout z-80 di atas drawer → bayar → receipt `MLG-...` muncul, drawer reset (tertutup, FAB hidden, count 0, body unlock) ✓. Test via CDP browser: 7 phpunit passed (26 assertions).
- Catatan: alert/confirm native (sesuai preferensi Londry stabilitas) memblokir CDP automation — di-test dengan stub `window.alert/confirm`; perilaku asli tidak berubah.

### 2026-09-14 — Mobile cart drawer di POS (responsive mobile mode)
- **`resources/views/pos/index.blade.php`**: panel keranjang diberi `id="cartDrawer"`. Di mobile (<1024px) jadi bottom-sheet drawer (`position:fixed;bottom:0;height:82vh;transform:translateY(105%)` → `.cart-open` slide-up), desktop tetap kolom 400px.
- **FAB** `#cartFab` (indigo, kanan-bawah, badge `#cartCount` jumlah qty) + overlay `#cartDrawerOverlay` — muncul di mobile saat cart berisi item (`updateCartUI()`), tap untuk buka/tutup, scroll body terkunci saat terbuka.
- **JS**: `updateCartUI()` (badge count + FAB visibility), `toggleCartDrawer()` (toggle `.cart-open`/`.show`/`body.cart-open`, tutup juga close semua modal `is-open`), overlay click listener. Dipanggil di `renderCart()` (kedua path: kosong & isi) — FAB hilang saat cart kosong.
- **Pitfall diatasi**: `#cartDrawer` ID selector mengalahkan Tailwind utilities → breakpoint desktop `min-width:1024px` reset `position:relative!important;transform:none!important;width:400px!important` dll supaya layout 2-kolom POS tidak rusak. `#cartFab.hidden{display:none}` agar toggle class `.hidden` benar-benar menyembunyikan FAB (ID `display:flex` saja meng-override).
- Verifikasi: `view:cache` OK, login curl admin/rooter@123 → `GET /pos` 200, elemen `cartDrawer/cartFab/cartDrawerOverlay/cartCount` ada di HTML render, `node --check` inline script OK. `git diff --stat` 1 file (+46/−3).

### 2026-09-03 — Edit item & qty lewat POS (full edit mode)
- **PosController@edit**: preload order ke tampilan POS (cart/customer/rincian laundry/diskon/status), tombol jadi `SIMPAN PERUBAHAN` POST ke `pos.update`
- **OrderService::updateFromPos**: reverse stock lama → delete items → insert baru → re-apply stock → recompute subtotal/discount/tax/total; guard ready/picked_up/complete/cancelled
- **routes**: `GET|POST /pos/edit/{order}` (pos.edit, pos.update)
- **Status rollback** `ready→received` diizinkan di semua path (form, antrian, POS) via OrderService::updateStatus; QueueController delegs ke service
- **orders/show**: tombol **Edit di POS** (+ modal Edit Cepat), status dropdown semua 5 status
- **qty check removed**: stok boleh minus (lihat memori Londry POS gotchas)
- Verifikasi: `POST /pos/edit/2 qty=2` 200 total 14000 (revert 7000 ✓); `ready→received` rollback 200 ✓; `picked_up/complete/cancelled` still block edit 403/422 ✓; `php -l` + `view:cache` ✓; routes registered

### 2026-09-03 — Fitur Backup Database (Settings → Backup Database (Admin))
- **SettingController@backup**: `GET /settings/backup`, hanya role `Admin` (kasir → 403); dump MySQL via `mysqldump` (resolve path brew + fallback) → `.sql` → zip → `Storage::disk('local')->download`.
- Error-safe: binary tak ketemu / dump kosong → redirect back error, bukan 500.
- Frontend: tombol amber di `settings/index.blade.php`, download otomatis via `<a>` langsung.
- Verifikasi: kasir → 403 ✓; admin → 200, `db_backup_<ts>.zip`, magic bytes `PK`, 10KB ✓; `php -l` + `route:list` ✓



## 10) Responsive — Konvensi

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

## 11) File Penting untuk Dibaca Dulu

1. **`DESIGN.md`** — arah desain (wajib dibaca sebelum ubah UI/Tampilan). Palet, tipografi, radius, motif, motion, dials. Lihat §8 "Guideline untuk Penerus" untuk aturan main.
2. `pos-laundry.md` — spec
3. `routes/web.php` — peta fitur (+ merchants, switch-merchant; + PWA /offline, /manifest.webmanifest, /sw.js)
4. `app/Services/OrderService.php` — inti transaksi (laundry_details + order_status + merchant_id)
5. `app/Models/Merchant.php` + `Order.php` + `LaundryItemType.php` + `Category.php` + `Product.php`
6. `resources/views/pos/index.blade.php` — POS (list vertical + Baru/Selesai button + dropdown rincian per merchant)
7. `app/helpers.php` — `setting()`, `money()`, `current_branch()`, `icon()` (SVG line-icon server-side, 30 icon)
8. `resources/views/auth/login.blade.php` — form login bersih (tanpa card demo) + guest layout `@vite`
9. `resources/views/orders/receipt.blade.php` — struk tanpa cabang + RINCIAN LAUNDRY dinamis
10. `app/Services/WhatsappService.php` + `OrderController@sendWhatsapp` — direk web.whatsapp.com
11. `public/manifest.webmanifest` + `public/sw.js` + `public/icons/` + `resources/js/pwa.js` — PWA
12. `tailwind.config.js` — token desain: palet `teal`/`paper`, `borderRadius`, `fontFamily` system. Single source of truth untuk warna.
10. `database/seeders/ProductionSeeder.php` + `DemoSeeder.php` + `MerchantSeeder.php`
11. `database/migrations/2024_01_01_000020_create_merchants_and_add_merchant_id.php` + `000021` + `000022` + `000023`
12. `app/Http/Controllers/MerchantController.php` + `app/Http/Middleware/MerchantContext.php`

---

## 12) Troubleshooting

| Gejala | Solusi |
|--------|--------|
| `No such file database.sqlite` | `touch database/database.sqlite` + `php artisan migrate` |
| `laundryTypes is not defined` di POS | Pastikan `PosController@index` kirim `laundryTypes` (`when mid`), Blade ada `let laundryTypes = @json($laundryTypes);` |
| Struk tidak muncul rincian | Cek `Order::latest()->first()->laundry_details`, dan `receipt.blade.php` dynamic block |
| WA tidak kirim API | Kosongkan `whatsapp_api_url` → pakai `web.whatsapp.com/send` + `wa.me` link; cek `whatsapp_logs` |
|| View tidak update habis edit | `php artisan view:clear` + hard refresh `Cmd+Shift+R`; Vite dev jalan (`npm run dev`, bukan `npm run build`) |
|| Edit item/qty tidak bisa disimpan | Order harus `received`; rollback `ready→received` via form atau `/antrian/{id}/update` |
|| qty check stok error | Sudah dihilangkan — stok boleh minus (lihat OrderService) |
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

## 13) Deploy (production VPS, nginx + PHP-FPM + Cloudflare)

**Deploy script otomatis:** `deploy.sh` di repo root. Jalankan kapanpun setelah `git pull`:

```bash
cd /root/www/pos-londry
./deploy.sh
```

Script melakukan: git pull latest → composer install → npm ci + build → fix permission storage/www-data → cache config+routes+views+events → migrate → reload PHP-FPM + nginx.

### Troubleshooting deploy khas (lihat juga §12)

|| Gejala | Akar | Solusi |
||--------|------|--------|
|| `500 Server Error` setelah git pull | storage/views/file cache dimilik `root` (bukan www-data); atau route cache kadaluarsa (`Route [queue.index] not defined`) | `chown -R www-data:www-data storage bootstrap/cache` lalu `php artisan route:cache` (gunakan deploy.sh) |
||| 419 / CSRF token mismatch di login | Cloudflare CDN meng-cache halaman `/login` statis → token CSRF tidak sync dengan cookie session | Tambahkan di nginx: `location ~ ^/(login|register|logout|password|admin/login) { add_header Cache-Control "no-store, no-cache, must-revalidate, max-age=0"; }` lalu `nginx -s reload` |

*Last updated: 2026-09-03 — oleh Hermes Agent. Jangan commit `.env` & `database.sqlite` ke repo publik. Seeder Production/Demo idempotent, aman re-run.

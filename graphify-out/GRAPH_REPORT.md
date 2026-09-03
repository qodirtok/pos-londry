# Graph Report - londry  (2026-09-03)

## Corpus Check
- 201 files · ~57,869 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 841 nodes · 1163 edges · 169 communities (139 shown, 30 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 71 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c6efd91e`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Merchant
- Order
- Illuminate\Database\Eloquent\Model
- pos-laundry.md
- Handover — Londry POS Laundry (Laravel 10, PHP 8.1)
- Product
- package.json
- Customer
- Controller
- User
- Illuminate\Http\Request
- CashierShift
- Branch
- Closure
- RedirectIfAuthenticated.php
- CashTransaction
- 54. AI Agent Development Rules
- Illuminate\Support\ServiceProvider
- composer.json
- scripts
- 53. Development Priority
- README.md
- require-dev
- 46. Important Business Rules
- UserFactory
- config
- psr-4
- TestCase
- 52. Testing
- Kernel
- require
- POS harus memiliki:
- 35. Settings
- EventServiceProvider
- sw.js
- Handler
- Authenticate
- TrustHosts
- AuthServiceProvider
- ExampleTest
- 5. Role & Permission
- Product
- Kernel
- EncryptCookies
- PreventRequestsDuringMaintenance
- TrimStrings
- TrustProxies
- ValidateSignature
- VerifyCsrfToken
- autoload-dev
- extra
- Illuminate\Foundation\Application
- 10. Quantity
- CLAUDE.md
- 12. Laundry Order
- 15. Order Item
- 18. Payment
- Laundry POS Web Application
- 20. Cash Management
- 21. Cashier Shift
- 25. Inventory / Stock
- 27. Reports
- 3. Multi Branch
- 4. User Management
- 6. Customer Management
- 7. Category Management

## God Nodes (most connected - your core abstractions)
1. `Order` - 45 edges
2. `Customer` - 30 edges
3. `Product` - 30 edges
4. `Merchant` - 24 edges
5. `User` - 24 edges
6. `Branch` - 22 edges
7. `Controller` - 20 edges
8. `LaundryItemType` - 18 edges
9. `OrderService` - 17 edges
10. `Category` - 16 edges

## Surprising Connections (you probably didn't know these)
- `canAccessCategory()` --references--> `User`  [EXTRACTED]
  verify_merchant_all.php → app/Models/User.php
- `canAccessCategory()` --references--> `Category`  [EXTRACTED]
  verify_merchant_all.php → app/Models/Category.php
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AuthController.php → app/Http/Controllers/Controller.php
- `BranchController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/BranchController.php → app/Http/Controllers/Controller.php
- `CashController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/CashController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (169 total, 30 thin omitted)

### Community 0 - "Merchant"
Cohesion: 0.06
Nodes (18): MerchantController, Merchant, Permission, Role, BranchSeeder, CatalogSeeder, CustomerSeeder, DatabaseSeeder (+10 more)

### Community 1 - "Order"
Cohesion: 0.08
Nodes (8): OrderController, QueueController, Order, OrderItem, ProductStock, StockMovement, OrderService, AuditLogger

### Community 2 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.08
Nodes (11): setting(), SettingController, AuditLog, CashCategory, Payment, PaymentMethod, Refund, Setting (+3 more)

### Community 3 - "pos-laundry.md"
Cohesion: 0.06
Nodes (34): 11. Price & Money, 13. Order Number, 14. Order Status, 17. Discount, 19. Partial Payment / Debt, 22. Receipt / Struk, 23. WhatsApp Receipt, 24. Receipt Template (+26 more)

### Community 4 - "Handover — Londry POS Laundry (Laravel 10, PHP 8.1)"
Cohesion: 0.06
Nodes (33): 10) Responsive — Konvensi, 11) File Penting untuk Dibaca Dulu, 12) Troubleshooting, 1) Cara Jalan Cepat (5 menit), 2026-09-03 — Edit item & qty lewat POS (full edit mode), 2) Struktur Proyek, 3) Database — Ringkas, 4) Enums & Models (+25 more)

### Community 5 - "Product"
Cohesion: 0.10
Nodes (5): CategoryController, ProductController, Category, Product, canAccessCategory()

### Community 6 - "package.json"
Cohesion: 0.06
Nodes (32): autoprefixer, axios, laravel-vite-plugin, author, dependencies, sweetalert2, @vitejs/plugin-vue, description (+24 more)

### Community 7 - "Customer"
Cohesion: 0.11
Nodes (4): CustomerController, DashboardController, Customer, NumberGenerator

### Community 8 - "Controller"
Cohesion: 0.11
Nodes (8): Controller, LaundryItemTypeController, PosController, LaundryItemType, LaundryItemTypeSeeder, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Foundation\Validation\ValidatesRequests, Illuminate\Routing\Controller

### Community 9 - "User"
Cohesion: 0.14
Nodes (6): UserController, User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 10 - "Illuminate\Http\Request"
Cohesion: 0.20
Nodes (3): AuthController, ReportController, Illuminate\Http\Request

### Community 11 - "CashierShift"
Cohesion: 0.21
Nodes (4): money(), ShiftController, CashierShift, CashService

### Community 13 - "Closure"
Cohesion: 0.21
Nodes (5): BlockDemoFromUserManagement, BranchContext, CheckPermission, MerchantContext, Closure

### Community 14 - "RedirectIfAuthenticated.php"
Cohesion: 0.20
Nodes (4): RedirectIfAuthenticated, RouteServiceProvider, Illuminate\Foundation\Support\Providers\RouteServiceProvider, Symfony\Component\HttpFoundation\Response

### Community 16 - "54. AI Agent Development Rules"
Cohesion: 0.18
Nodes (11): 10. Selalu perhatikan multi-branch, 1. Jangan langsung membuat kode besar, 2. Jangan mengubah requirement tanpa alasan, 3. Jangan menaruh business logic kompleks di Controller, 4. Semua perubahan database harus melalui Migration, 54. AI Agent Development Rules, 5. Semua data penting harus memiliki timestamp, 6. Gunakan database transaction (+3 more)

### Community 17 - "Illuminate\Support\ServiceProvider"
Cohesion: 0.24
Nodes (4): current_branch(), AppServiceProvider, BroadcastServiceProvider, Illuminate\Support\ServiceProvider

### Community 18 - "composer.json"
Cohesion: 0.20
Nodes (9): description, keywords, license, minimum-stability, name, prefer-stable, type, framework (+1 more)

### Community 19 - "scripts"
Cohesion: 0.20
Nodes (10): scripts, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan key:generate --ansi, @php artisan package:discover --ansi (+2 more)

### Community 20 - "53. Development Priority"
Cohesion: 0.20
Nodes (10): 53. Development Priority, Phase 1 — Foundation, Phase 2 — Master Data, Phase 3 — POS, Phase 4 — Laundry, Phase 5 — Cash, Phase 6 — Inventory, Phase 7 — Reports (+2 more)

### Community 21 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 22 - "require-dev"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit, spatie/laravel-ignition

### Community 23 - "46. Important Business Rules"
Cohesion: 0.25
Nodes (8): 46. Important Business Rules, Rule 1, Rule 2, Rule 3, Rule 4, Rule 5, Rule 6, Rule 7

### Community 24 - "UserFactory"
Cohesion: 0.38
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 25 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 26 - "psr-4"
Cohesion: 0.29
Nodes (7): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\, app/helpers.php

### Community 27 - "TestCase"
Cohesion: 0.33
Nodes (4): CreatesApplication, Illuminate\Foundation\Testing\TestCase, ExampleTest, TestCase

### Community 28 - "52. Testing"
Cohesion: 0.29
Nodes (7): 52. Testing, Authentication, Authorization, Cash, POS, Product, Stock

### Community 29 - "Kernel"
Cohesion: 0.40
Nodes (3): Kernel, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 30 - "require"
Cohesion: 0.33
Nodes (6): require, guzzlehttp/guzzle, laravel/framework, laravel/sanctum, laravel/tinker, php

### Community 31 - "POS harus memiliki:"
Cohesion: 0.33
Nodes (6): 16. POS, Cart, Customer, POS harus memiliki:, Product Search, Total

### Community 32 - "35. Settings"
Cohesion: 0.33
Nodes (6): 35. Settings, Branch Settings, General Settings, Numbering Settings, POS Settings, WhatsApp Settings

### Community 41 - "5. Role & Permission"
Cohesion: 0.50
Nodes (4): 5. Role & Permission, Admin, Kasir, User

### Community 42 - "Product"
Cohesion: 0.50
Nodes (4): 8. Product Management, Product, Product Type, Unit

### Community 50 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 51 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 80 - "10. Quantity"
Cohesion: 0.67
Nodes (3): 10. Quantity, Product biasa, Service laundry

## Knowledge Gaps
- **187 isolated node(s):** `name`, `type`, `description`, `laravel`, `framework` (+182 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **30 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Order` connect `Order` to `Controller`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Model`, `Customer`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Why does `Customer` connect `Customer` to `Merchant`, `Order`, `Illuminate\Database\Eloquent\Model`, `Product`, `Controller`, `User`, `Illuminate\Http\Request`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Why does `Merchant` connect `Merchant` to `Illuminate\Database\Eloquent\Model`, `Product`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Are the 4 inferred relationships involving `Order` (e.g. with `.index()` and `.laundry()`) actually correct?**
  _`Order` has 4 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `type`, `description` to the rest of the system?**
  _187 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Merchant` be split into smaller, more focused modules?**
  _Cohesion score 0.05505952380952381 - nodes in this community are weakly interconnected._
- **Should `Order` be split into smaller, more focused modules?**
  _Cohesion score 0.08144796380090498 - nodes in this community are weakly interconnected._
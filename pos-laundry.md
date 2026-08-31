# Laundry POS Web Application

## 1. Overview

Membangun aplikasi **Point of Sale (POS) berbasis Laravel** untuk bisnis laundry dan penjualan produk pendukung laundry.

Aplikasi harus mendukung:

* Multi-user
* Multi-role
* Multi-cabang
* Transaksi laundry
* Penjualan produk biasa
* Quantity decimal untuk service laundry
* Manajemen customer
* Manajemen produk dan kategori
* Kas masuk dan kas keluar
* Pembayaran
* Cetak struk
* Pengiriman struk melalui WhatsApp
* Laporan penjualan
* Laporan keuangan
* Pengaturan per cabang
* Audit transaksi

Aplikasi harus dirancang agar **mudah dikembangkan**, memiliki struktur database yang baik, dan dapat digunakan oleh AI Agent/developer lain untuk melanjutkan development.

---

# 2. Technology Stack

Gunakan teknologi berikut:

* Backend: **Laravel**
* Database: **PostgreSQL**
* ORM: **Laravel Eloquent**
* Authentication: Laravel Authentication
* API: Laravel REST API apabila diperlukan
* Frontend: gunakan Laravel Blade/Livewire atau stack frontend yang ditentukan pada tahap implementasi
* Queue: Laravel Queue
* Cache: Laravel Cache
* Scheduler: Laravel Scheduler

Gunakan prinsip:

* Clean Code
* SOLID
* Separation of Concerns
* Service Layer
* Form Request Validation
* Policy/Gate untuk authorization
* Database Transaction untuk transaksi penting
* Migration untuk seluruh perubahan database
* Seeder untuk data awal
* Factory untuk testing
* Feature Test dan Unit Test

---

# 3. Multi Branch

Aplikasi harus mendukung banyak cabang.

## Branch

Field minimal:

* id
* code
* name
* phone
* email
* address
* city
* province
* postal_code
* status
* created_at
* updated_at

Status:

* active
* inactive

Setiap transaksi harus memiliki `branch_id`.

User juga harus dapat dikaitkan dengan satu cabang atau beberapa cabang sesuai kebutuhan role.

---

# 4. User Management

## User

Field minimal:

* id
* name
* username
* email
* password
* phone
* status
* branch_id
* last_login_at
* created_at
* updated_at

Status:

* active
* inactive

Password harus disimpan menggunakan hashing Laravel.

Jangan pernah menyimpan password dalam bentuk plaintext.

---

# 5. Role & Permission

Minimal memiliki role:

### Admin

Memiliki akses penuh terhadap aplikasi.

### User

Akses terbatas sesuai permission.

### Kasir

Fokus pada:

* POS
* Customer
* Transaksi
* Pembayaran
* Cetak struk
* Kas masuk/keluar

Sistem sebaiknya menggunakan permission granular sehingga nantinya role dapat dikembangkan.

Contoh permission:

```text
dashboard.view

users.view
users.create
users.update
users.delete

branches.view
branches.create
branches.update
branches.delete

customers.view
customers.create
customers.update
customers.delete

categories.view
categories.create
categories.update
categories.delete

products.view
products.create
products.update
products.delete

pos.access

orders.view
orders.create
orders.update
orders.cancel

payments.create
payments.refund

cash.view
cash.create
cash.update

reports.view

settings.view
settings.update
```

---

# 6. Customer Management

Customer digunakan untuk menyimpan data pelanggan laundry.

## Customer

Field minimal:

* id
* code
* name
* phone
* email
* address
* notes
* status
* branch_id
* created_at
* updated_at

Fitur:

* Create customer
* Update customer
* Delete/nonaktifkan customer
* Search customer
* Search berdasarkan nomor telephone
* Riwayat transaksi customer
* Total transaksi customer
* Total pembayaran customer

Nomor telephone sebaiknya dapat digunakan untuk pencarian cepat pada POS.

---

# 7. Category Management

Digunakan untuk mengelompokkan product/service.

## Category

Field minimal:

* id
* name
* code
* description
* status
* created_at
* updated_at

Contoh:

```text
Laundry
Cuci Kering
Cuci Setrika
Setrika
Express
Minuman
Parfum
Packaging
Lainnya
```

---

# 8. Product Management

Product harus mendukung dua tipe:

1. Product biasa
2. Service laundry

## Product

Field minimal:

* id
* sku
* barcode
* name
* category_id
* type
* price
* cost
* unit
* status
* description
* created_at
* updated_at

### Product Type

```text
product
service
```

### Unit

Contoh:

```text
pcs
kg
meter
liter
```

Untuk service laundry, unit dapat menggunakan:

```text
kg
pcs
meter
```

---

# 9. SKU

SKU dapat:

* dibuat otomatis oleh sistem
* diinput secara manual

Contoh auto SKU:

```text
PRD-000001
PRD-000002
SRV-000001
SRV-000002
```

SKU harus unique.

Jika user mengisi SKU secara manual, sistem harus melakukan validasi agar tidak terjadi duplicate SKU.

---

# 10. Quantity

Quantity harus mendukung decimal.

## Product biasa

Contoh:

```text
1 pcs
2 pcs
10 pcs
```

Quantity tidak boleh menggunakan decimal.

Contoh invalid:

```text
1.5 pcs
2.3 pcs
```

## Service laundry

Quantity dapat menggunakan decimal.

Contoh:

```text
1 kg
1.2 kg
1.5 kg
2.75 kg
```

Gunakan tipe numeric/decimal pada database.

Contoh:

```text
quantity DECIMAL(12,3)
```

Jangan menggunakan floating point untuk nilai uang.

---

# 11. Price & Money

Semua nilai uang harus menggunakan tipe decimal/numeric.

Contoh:

```text
DECIMAL(15,2)
```

Jangan menggunakan `float` atau `double` untuk harga dan nominal transaksi.

Field harga:

* cost
* price
* subtotal
* discount
* tax
* total
* paid
* change
* refund

---

# 12. Laundry Order

Ini merupakan fitur utama aplikasi.

Customer dapat melakukan order laundry.

## Order

Field minimal:

* id
* order_number
* branch_id
* customer_id
* cashier_id
* order_date
* pickup_date
* completed_at
* subtotal
* discount
* tax
* total
* paid_amount
* change_amount
* payment_status
* order_status
* notes
* created_at
* updated_at

---

# 13. Order Number

Nomor transaksi harus unique.

Contoh:

```text
TRX-20260830-000001
TRX-20260830-000002
```

Nomor transaksi sebaiknya dapat dibedakan berdasarkan cabang.

Contoh:

```text
MLG-20260830-000001
MLG-20260830-000002
```

---

# 14. Order Status

Laundry order memiliki status.

Minimal:

```text
received
washing
drying
ironing
ready
picked_up
cancelled
```

Flow normal:

```text
RECEIVED
    ↓
WASHING
    ↓
DRYING
    ↓
IRONING
    ↓
READY
    ↓
PICKED UP
```

Status dapat dikembangkan sesuai kebutuhan.

---

# 15. Order Item

Satu order dapat memiliki banyak item.

## Order Item

Field:

* id
* order_id
* product_id
* product_name
* sku
* quantity
* unit
* price
* discount
* subtotal
* notes
* created_at
* updated_at

Simpan snapshot:

* product_name
* sku
* price
* unit

pada order item agar perubahan data product di masa depan tidak mengubah histori transaksi lama.

---

# 16. POS

POS merupakan halaman utama untuk kasir.

## POS harus memiliki:

### Product Search

Kasir dapat mencari product berdasarkan:

* nama
* SKU
* barcode

### Customer

Kasir dapat:

* memilih customer existing
* membuat customer baru
* menggunakan customer umum/walk-in customer

### Cart

Cart menampilkan:

```text
Product
Quantity
Unit
Price
Discount
Subtotal
```

### Total

Hitung:

```text
Subtotal
- Discount
+ Tax
= Grand Total
```

Jika tidak menggunakan pajak:

```text
Subtotal
- Discount
= Grand Total
```

---

# 17. Discount

POS harus mendukung discount.

Minimal:

* Discount nominal
* Discount percentage

Contoh:

```text
Discount: Rp10.000
```

atau:

```text
Discount: 10%
```

Discount harus tervalidasi agar tidak melebihi subtotal.

Jika diperlukan, sistem dapat mendukung:

* discount per item
* discount per transaksi

---

# 18. Payment

POS harus mendukung pembayaran.

Minimal:

```text
Cash
Transfer
QRIS
Debit
Credit Card
E-Wallet
```

Payment method harus dibuat configurable.

## Payment

Field minimal:

* id
* order_id
* payment_number
* payment_method
* amount
* paid_at
* cashier_id
* reference_number
* notes
* status

---

# 19. Partial Payment / Debt

Aplikasi sebaiknya mendukung pembayaran sebagian.

Contoh:

Total:

```text
Rp50.000
```

Customer membayar:

```text
Rp30.000
```

Sisa:

```text
Rp20.000
```

Payment status:

```text
unpaid
partial
paid
```

Jika bisnis tidak membutuhkan hutang/piutang, fitur ini dapat dimatikan melalui setting.

---

# 20. Cash Management

Sistem harus memiliki kas masuk dan kas keluar.

## Cash Transaction

Jenis:

```text
income
expense
```

Contoh kas masuk:

```text
Modal awal
Penjualan
Pembayaran hutang customer
Pendapatan lainnya
```

Contoh kas keluar:

```text
Pembelian detergent
Listrik
Transportasi
Gaji
Operasional
Pengeluaran lainnya
```

Field:

* id
* branch_id
* user_id
* type
* category
* amount
* reference_type
* reference_id
* description
* transaction_date
* created_at
* updated_at

---

# 21. Cashier Shift

Sebaiknya POS memiliki sistem shift kasir.

## Shift

Field:

* id
* branch_id
* cashier_id
* opened_at
* closed_at
* opening_cash
* expected_cash
* actual_cash
* difference
* status
* notes

Status:

```text
open
closed
```

Saat kasir membuka shift:

```text
Opening Cash
```

Saat menutup shift:

```text
Opening Cash
+ Cash Sales
+ Cash Income
- Cash Expense
- Refund
= Expected Cash
```

Kemudian kasir memasukkan:

```text
Actual Cash
```

Sistem menghitung:

```text
Difference = Actual Cash - Expected Cash
```

---

# 22. Receipt / Struk

Setiap transaksi dapat menghasilkan struk.

Struk harus configurable.

Setting:

* nama POS
* nama perusahaan
* nama cabang
* alamat
* nomor telephone
* logo
* footer
* header
* format nomor transaksi
* tampilkan customer
* tampilkan cashier
* tampilkan barcode/QR
* ukuran kertas

Ukuran:

```text
58mm
80mm
A4
```

POS harus menyediakan pilihan:

```text
Print
Skip Print
```

---

# 23. WhatsApp Receipt

Sistem dapat mengirim struk melalui WhatsApp.

Flow:

```text
Transaction Complete
        ↓
Customer Phone Available?
        ↓
Yes
        ↓
Generate Receipt
        ↓
Send WhatsApp
```

WhatsApp integration harus configurable.

Setting:

```text
enabled
provider
api_url
api_key
sender
```

Jangan hardcode API key.

API credential harus disimpan secara aman menggunakan environment/configuration management.

Jika WhatsApp gagal dikirim, transaksi tetap dianggap berhasil.

Status pengiriman dapat dicatat:

```text
pending
sent
failed
```

---

# 24. Receipt Template

Struk harus dapat menampilkan:

```text
Nama POS
Nama Cabang
Alamat
Telephone

Nomor Transaksi
Tanggal
Kasir
Customer

--------------------------------
Laundry Cuci Kering
1.50 kg x Rp7.000
Rp10.500

Laundry Setrika
2.00 kg x Rp8.000
Rp16.000
--------------------------------

Subtotal
Rp26.500

Discount
Rp0

TOTAL
Rp26.500

Bayar
Rp30.000

Kembalian
Rp3.500

--------------------------------

Terima kasih
```

---

# 25. Inventory / Stock

Untuk product biasa yang menggunakan stock, aplikasi sebaiknya memiliki inventory sederhana.

Contoh:

```text
Detergent
Plastic
Parfum
Packaging
```

## Product Stock

Field minimal:

* product_id
* branch_id
* quantity
* minimum_stock

Transaksi stock:

```text
stock_in
stock_out
adjustment
sale
return
```

Service laundry seperti:

```text
Cuci Kering
Cuci Setrika
Setrika
```

tidak harus mengurangi stock.

Inventory harus bersifat per-cabang.

---

# 26. Stock Adjustment

Admin dapat melakukan adjustment stock.

Contoh:

```text
System Stock : 100
Actual Stock : 97
Adjustment   : -3
```

Semua adjustment harus dicatat.

Simpan:

* user
* branch
* product
* old quantity
* new quantity
* difference
* reason
* timestamp

---

# 27. Reports

Aplikasi harus memiliki reporting.

## Sales Report

Filter:

* tanggal
* cabang
* cashier
* customer
* product
* category

Informasi:

```text
Total Transaction
Gross Sales
Discount
Net Sales
```

---

# 28. Payment Report

Menampilkan:

```text
Cash
Transfer
QRIS
Debit
Credit Card
E-Wallet
```

Dengan total masing-masing payment method.

---

# 29. Cash Report

Menampilkan:

```text
Opening Balance
Cash Income
Cash Expense
Cash Sales
Refund
Closing Balance
Difference
```

---

# 30. Product Report

Menampilkan:

```text
Product
Quantity Sold
Revenue
Discount
Net Revenue
```

---

# 31. Customer Report

Menampilkan:

```text
Customer
Total Transaction
Total Spending
Last Transaction
Outstanding Payment
```

---

# 32. Laundry Report

Khusus laundry:

```text
Total Order
Total Weight
Total Revenue
Completed Order
Pending Order
Ready Order
Picked Up Order
Cancelled Order
```

Contoh:

```text
Total Laundry
125.5 KG
```

---

# 33. Dashboard

Dashboard harus menampilkan informasi ringkas.

Contoh:

```text
Today's Sales
Today's Orders
Today's Customers
Today's Cash Income
Today's Cash Expense
Outstanding Payment
Pending Laundry
Ready Laundry
```

Grafik:

* Sales per day
* Sales by category
* Payment method
* Laundry order status

Dashboard harus mengikuti branch yang sedang digunakan user.

---

# 34. Branch Access

User harus memiliki akses berdasarkan cabang.

Contoh:

```text
Admin
 ├── Branch A
 ├── Branch B
 └── Branch C

Cashier A
 └── Branch A

Cashier B
 └── Branch B
```

Kasir Branch A tidak boleh melihat transaksi Branch B.

Semua query yang berhubungan dengan data cabang harus menerapkan branch scope/access control.

Admin tertentu dapat diberikan akses seluruh cabang.

---

# 35. Settings

Setting dibagi menjadi:

## General Settings

```text
Application Name
Company Name
Timezone
Currency
Date Format
Number Format
```

## Branch Settings

```text
Branch Name
Address
Phone
Email
Logo
```

## POS Settings

```text
Receipt Size
Auto Print
Allow Discount
Allow Partial Payment
Allow Debt
Default Payment Method
```

## WhatsApp Settings

```text
Enabled
Provider
API URL
API Key
Sender
```

## Numbering Settings

```text
Order Prefix
Customer Prefix
Product Prefix
Payment Prefix
```

---

# 36. Audit Log

Semua aktivitas penting harus dapat dilacak.

Contoh:

```text
User login
Create transaction
Update transaction
Cancel transaction
Create payment
Refund
Stock adjustment
Cash expense
Cash income
Change setting
```

Audit log minimal:

* user_id
* branch_id
* action
* module
* reference_type
* reference_id
* old_value
* new_value
* IP address
* user agent
* created_at

---

# 37. Transaction Rules

Transaksi penting harus menggunakan database transaction.

Contoh saat membuat order:

```text
Create Order
    ↓
Create Order Items
    ↓
Create Payment
    ↓
Update Stock
    ↓
Create Cash Transaction
    ↓
Commit
```

Jika salah satu proses gagal:

```text
ROLLBACK
```

Jangan sampai terjadi kondisi:

```text
Order berhasil
Payment gagal
Stock sudah berkurang
```

---

# 38. Cancellation

Transaksi tidak boleh langsung dihapus dari database.

Gunakan status:

```text
cancelled
```

Ketika transaksi dibatalkan:

* simpan alasan pembatalan
* simpan user yang membatalkan
* simpan waktu pembatalan
* reverse stock jika diperlukan
* reverse cash transaction jika diperlukan
* reverse payment jika diperlukan

Data transaksi tetap tersimpan untuk audit.

---

# 39. Refund

Sistem sebaiknya mendukung refund.

Contoh:

```text
Transaction
Rp100.000

Refund
Rp30.000

Remaining
Rp70.000
```

Refund harus tercatat sebagai transaksi tersendiri.

Jangan mengubah nominal transaksi lama secara langsung.

---

# 40. Security

Aplikasi harus menerapkan:

* Authentication
* Authorization
* CSRF Protection
* Password Hashing
* Rate Limiting
* Input Validation
* SQL Injection Protection
* XSS Protection
* Secure Session
* Secure File Upload
* Permission Check
* Audit Log

Jangan memberikan akses berdasarkan hanya menyembunyikan menu di frontend.

Authorization harus dilakukan di backend.

---

# 41. Database Design

Relasi utama:

```text
branches
    │
    ├── users
    ├── customers
    ├── products
    ├── orders
    ├── cash_transactions
    └── shifts

categories
    │
    └── products

customers
    │
    └── orders

products
    │
    └── order_items

orders
    │
    ├── order_items
    ├── payments
    └── cash_transactions

users
    │
    ├── orders
    ├── payments
    ├── shifts
    └── audit_logs
```

---

# 42. Recommended Database Tables

Minimal database table:

```text
users
roles
permissions
role_user
permission_role

branches

customers

categories
products
product_stocks
stock_movements

orders
order_items

payments
payment_methods

cash_transactions
cash_categories

cashier_shifts

settings

receipt_templates

whatsapp_logs

audit_logs

refunds
refund_items
```

---

# 43. Recommended Laravel Structure

Gunakan struktur Laravel yang terorganisir.

Contoh:

```text
app/
├── Actions/
├── Console/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Services/
├── Repositories/
├── Jobs/
├── Listeners/
└── Support/
```

Gunakan Service/Action untuk business logic yang kompleks.

Contoh:

```text
CreateOrderAction
CreatePaymentAction
CompleteOrderAction
CancelOrderAction
RefundOrderAction
OpenCashierShiftAction
CloseCashierShiftAction
AdjustStockAction
SendReceiptWhatsAppAction
```

Controller sebaiknya tidak berisi business logic yang terlalu kompleks.

---

# 44. API Design

Jika aplikasi membutuhkan API, gunakan REST API.

Contoh:

```text
POST   /api/login
POST   /api/logout

GET    /api/products
POST   /api/products
PUT    /api/products/{id}

GET    /api/customers
POST   /api/customers

GET    /api/orders
POST   /api/orders
GET    /api/orders/{id}
POST   /api/orders/{id}/cancel

POST   /api/orders/{id}/payments
POST   /api/orders/{id}/refund

GET    /api/reports/sales
GET    /api/reports/cash
GET    /api/reports/products
```

---

# 45. Validation Rules

Semua input harus divalidasi.

Contoh Product:

```text
name       required
sku        required|unique
category   required|exists
type       required|in:product,service
price      required|numeric|min:0
```

Order:

```text
customer_id optional
items       required
quantity    required|numeric|min:0.001
```

Quantity harus divalidasi berdasarkan product type.

Contoh:

```text
type = product
quantity harus integer

type = service
quantity boleh decimal
```

---

# 46. Important Business Rules

## Rule 1

Product/service yang sudah pernah digunakan pada transaksi tidak boleh dihapus secara permanen.

Gunakan:

```text
status = inactive
```

atau Soft Delete jika sesuai.

## Rule 2

Order yang sudah dibayar tidak boleh diubah sembarangan.

Perubahan harus memiliki authorization.

## Rule 3

Order cancelled tidak boleh digunakan untuk pembayaran baru.

## Rule 4

Payment yang sudah confirmed tidak boleh dihapus.

Gunakan reversal/refund.

## Rule 5

Stock tidak boleh menjadi negatif kecuali fitur negative stock diaktifkan.

## Rule 6

Data antar cabang harus terisolasi.

## Rule 7

Semua perubahan finansial harus tercatat.

---

# 47. UI / UX

POS harus dibuat cepat digunakan kasir.

Prioritas:

```text
Fast Search
Keyboard Friendly
Minimal Click
Responsive
Mobile Friendly
Desktop Friendly
```

POS layout:

```text
┌─────────────────────────────────────────────┐
│ Search Product / Barcode                    │
├──────────────────────┬──────────────────────┤
│                      │                      │
│ Product List         │ Cart                 │
│                      │                      │
│ [Product]            │ Product A    1 KG    │
│ [Product]            │ Product B    2 PCS   │
│ [Product]            │                      │
│ [Product]            │ Subtotal             │
│                      │ Discount             │
│                      │ TOTAL                │
│                      │                      │
│                      │ [PAY]                │
└──────────────────────┴──────────────────────┘
```

---

# 48. Barcode

Product biasa dapat menggunakan barcode.

Kasir dapat:

```text
Scan Barcode
      ↓
Find Product
      ↓
Add To Cart
```

Jika barcode tidak ditemukan:

```text
Barcode not found
```

---

# 49. Customer Quick Search

Pada POS:

```text
Search Customer
```

dapat menggunakan:

```text
Nama
Nomor telephone
Customer code
```

Jika customer belum ada:

```text
+ New Customer
```

---

# 50. Notifications

Gunakan notification untuk:

```text
Order Ready
Low Stock
Payment Failed
WhatsApp Failed
Cashier Shift Warning
```

Notification dapat dikembangkan menjadi:

* internal notification
* email
* WhatsApp

---

# 51. Queue & Background Jobs

Proses yang tidak harus dilakukan secara synchronous sebaiknya menggunakan queue.

Contoh:

```text
Send WhatsApp Receipt
Generate Large Report
Send Notification
Generate PDF
```

Contoh:

```text
SendReceiptWhatsAppJob
GenerateSalesReportJob
SendOrderReadyNotificationJob
```

---

# 52. Testing

Minimal test:

## Authentication

* Login berhasil
* Login gagal
* User inactive tidak dapat login

## Product

* Create product
* Update product
* Duplicate SKU
* Decimal service quantity
* Integer product quantity

## POS

* Create order
* Multiple items
* Discount
* Payment
* Change
* Partial payment

## Stock

* Stock out
* Stock adjustment
* Prevent negative stock

## Cash

* Open shift
* Cash income
* Cash expense
* Close shift
* Calculate difference

## Authorization

* Cashier tidak dapat mengakses admin
* Cashier tidak dapat melihat cabang lain

---

# 53. Development Priority

Development harus dilakukan bertahap.

## Phase 1 — Foundation

```text
Laravel setup
Database
Authentication
User
Role
Permission
Branch
Settings
```

## Phase 2 — Master Data

```text
Customer
Category
Product
Product Type
SKU
Barcode
```

## Phase 3 — POS

```text
Cart
Product Search
Customer Search
Order
Order Item
Discount
Payment
Receipt
```

## Phase 4 — Laundry

```text
Laundry Status
Received
Washing
Drying
Ironing
Ready
Picked Up
```

## Phase 5 — Cash

```text
Cashier Shift
Cash Income
Cash Expense
Cash Closing
```

## Phase 6 — Inventory

```text
Stock
Stock Movement
Stock Adjustment
Low Stock
```

## Phase 7 — Reports

```text
Sales
Payment
Cash
Product
Customer
Laundry
```

## Phase 8 — Integration

```text
WhatsApp
PDF
Printer
Barcode
Notification
```

## Phase 9 — Audit & Hardening

```text
Audit Log
Authorization
Security
Performance
Database Index
Testing
```

---

# 54. AI Agent Development Rules

AI Agent yang mengerjakan project ini harus mengikuti aturan berikut.

### 1. Jangan langsung membuat kode besar

Sebelum implementasi:

1. Analisis requirement
2. Tentukan database schema
3. Tentukan relationship
4. Tentukan business rule
5. Tentukan migration
6. Baru implementasi kode

### 2. Jangan mengubah requirement tanpa alasan

Jika terdapat requirement yang ambigu atau berpotensi menyebabkan masalah architecture, jelaskan terlebih dahulu.

### 3. Jangan menaruh business logic kompleks di Controller

Gunakan:

```text
Actions
Services
Domain logic
```

### 4. Semua perubahan database harus melalui Migration

Jangan mengubah database secara manual tanpa migration.

### 5. Semua data penting harus memiliki timestamp

Gunakan:

```text
created_at
updated_at
```

Jika diperlukan:

```text
deleted_at
```

### 6. Gunakan database transaction

Untuk proses:

```text
Order
Payment
Refund
Stock
Cash
```

### 7. Jangan menggunakan float untuk uang

Gunakan:

```text
DECIMAL
```

### 8. Jangan hardcode configuration

Gunakan:

```text
.env
config/*.php
database settings
```

### 9. Jangan menghapus transaksi finansial

Gunakan:

```text
cancel
void
refund
reversal
```

sesuai kebutuhan.

### 10. Selalu perhatikan multi-branch

Setiap query harus mempertimbangkan:

```text
branch_id
```

dan authorization user.

---

# 55. Expected Result

Hasil akhir adalah sebuah **Laundry POS Web Application** yang dapat digunakan oleh:

```text
Owner
Admin
Manager
Cashier
```

dengan kemampuan:

```text
Login
    ↓
Select/Access Branch
    ↓
Dashboard
    ↓
Master Data
    ├── Customer
    ├── Category
    └── Product
    ↓
POS
    ↓
Laundry Order
    ↓
Payment
    ↓
Receipt
    ├── Print
    └── WhatsApp
    ↓
Cash Management
    ↓
Reports
```

Aplikasi harus siap dikembangkan menjadi sistem laundry multi-cabang yang lebih besar tanpa perlu melakukan redesign database secara besar-besaran.

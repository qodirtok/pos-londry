<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LaundryItemTypeController;
use App\Http\Controllers\MerchantController;

Route::get('/', fn()=> redirect('/dashboard'));
Route::view('/offline', 'offline')->name('offline');
Route::get('/manifest.webmanifest', fn() => response()->file(public_path('manifest.webmanifest'), ['Content-Type'=>'application/manifest+json']))->name('pwa.manifest');
Route::get('/sw.js', fn() => response()->file(public_path('sw.js'), ['Content-Type'=>'application/javascript','Cache-Control'=>'no-cache']))->name('pwa.sw');
Route::get('/login', [AuthController::class,'showLogin'])->name('login');
Route::post('/login', [AuthController::class,'login']);
Route::post('/logout', [AuthController::class,'logout'])->name('logout');
Route::get('/logout', [AuthController::class,'logout']);

Route::middleware(['auth'])->group(function(){
    Route::get('/switch-branch/{id}', [AuthController::class,'switchBranch'])->name('branch.switch');
    Route::get('/switch-merchant/{id}', [AuthController::class,'switchMerchant'])->name('merchant.switch');
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    // merchants, branches & users (admin) — demo diblokir (is_demo)
    Route::middleware('block.demo')->group(function(){
        Route::resource('merchants', MerchantController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('users', UserController::class);
    });
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::get('/customers-search', [CustomerController::class,'search'])->name('customers.search');
    Route::get('/products-search', [ProductController::class,'search'])->name('products.search');
    Route::resource('customers', CustomerController::class);

    // POS
    Route::get('/pos', [PosController::class,'index'])->name('pos.index');
    Route::post('/pos', [PosController::class,'store'])->name('pos.store');

    // Orders
    Route::get('/orders', [OrderController::class,'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class,'show'])->name('orders.show');
    Route::get('/orders/{order}/receipt', [OrderController::class,'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}/print', [OrderController::class,'print'])->name('orders.print');
    Route::post('/orders/{order}/status', [OrderController::class,'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/cancel', [OrderController::class,'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/payment', [OrderController::class,'addPayment'])->name('orders.payment');
    Route::post('/orders/{order}/customer', [OrderController::class,'updateCustomer'])->name('orders.customer');

    // Cash & Shift
    Route::get('/cash', [CashController::class,'index'])->name('cash.index');
    Route::get('/cash/create', [CashController::class,'create'])->name('cash.create');
    Route::post('/cash', [CashController::class,'store'])->name('cash.store');

    Route::get('/shifts', [ShiftController::class,'index'])->name('shifts.index');
    Route::post('/shifts/open', [ShiftController::class,'open'])->name('shifts.open');
    Route::post('/shifts/{shift}/close', [ShiftController::class,'close'])->name('shifts.close');

    // Reports
    Route::get('/reports', [ReportController::class,'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class,'sales'])->name('reports.sales');
    Route::get('/reports/payments', [ReportController::class,'payments'])->name('reports.payments');
    Route::get('/reports/cash', [ReportController::class,'cash'])->name('reports.cash');
    Route::get('/reports/products', [ReportController::class,'products'])->name('reports.products');
    Route::get('/reports/customers', [ReportController::class,'customers'])->name('reports.customers');
    Route::get('/reports/laundry', [ReportController::class,'laundry'])->name('reports.laundry');

    // Settings
    Route::get('/settings', [SettingController::class,'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class,'update'])->name('settings.update');

    // Laundry item types (dinamis, tersimpan)
    Route::get('/api/laundry-types', [LaundryItemTypeController::class,'apiIndex'])->name('api.laundry-types.index');
    Route::post('/api/laundry-types', [LaundryItemTypeController::class,'store'])->name('api.laundry-types.store');
    Route::delete('/api/laundry-types/{id}', [LaundryItemTypeController::class,'apiDestroy'])->name('api.laundry-types.destroy');
    Route::get('/laundry-types', [LaundryItemTypeController::class,'index'])->name('laundry-types.index');
    Route::post('/laundry-types', [LaundryItemTypeController::class,'store'])->name('laundry-types.store');
    Route::delete('/laundry-types/{id}', [LaundryItemTypeController::class,'destroy'])->name('laundry-types.destroy');

    // WA struk
    Route::get('/orders/{order}/whatsapp', [App\Http\Controllers\OrderController::class,'sendWhatsapp'])->name('orders.whatsapp');
    Route::post('/orders/{order}/whatsapp', [App\Http\Controllers\OrderController::class,'sendWhatsapp']);

    // API JSON for POS
    Route::get('/api/customers', [CustomerController::class,'search']);
    Route::post('/api/customers', [CustomerController::class,'storeApi'])->name('api.customers.store');
    Route::get('/api/products', [ProductController::class,'search']);
});

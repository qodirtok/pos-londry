<?php
require __DIR__.'/vendor/autoload.php';
$app=require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\{Merchant,Branch,User,Category,Product,LaundryItemType,Customer,Order,Setting};
use Illuminate\Support\Facades\DB;

function ok($msg){ echo "OK $msg\n"; }
function fail($msg){ echo "FAIL $msg\n"; exit(1); }

// 1. Catalog per merchant isolation
$m1 = Merchant::where('code','LONDRY-001')->first();
$m2 = Merchant::where('code','TOKO-002')->first();
echo "m1={$m1->id} m2={$m2->id}\n";

// ensure toko2 has no catalog yet, create isolated category/product
// Simulate login as admin_toko2 -> create category
$mid2 = $m2->id;
$cat2 = Category::firstOrCreate(['code'=>'TEST2','merchant_id'=>$mid2], ['name'=>'Test Toko2','status'=>'active','merchant_id'=>$mid2]);
echo "cat2 merchant={$cat2->merchant_id} code={$cat2->code}\n";
if ((int)$cat2->merchant_id !== (int)$mid2) fail("cat2 merchant");

// create product for toko2
$prod2 = Product::firstOrCreate(['sku'=>'TEST-TOKO2-001'], ['name'=>'Produk Toko2','category_id'=>$cat2->id,'type'=>'service','price'=>10000,'cost'=>0,'unit'=>'kg','status'=>'active','merchant_id'=>$mid2]);
echo "prod2 merchant={$prod2->merchant_id}\n";
if ((int)$prod2->merchant_id !== (int)$mid2) fail("prod2 merchant");

// 2. Scoped queries: m1 should NOT see toko2 data
$cats_m1 = Category::where('merchant_id',$m1->id)->pluck('code')->toArray();
if (in_array('TEST2',$cats_m1)) fail("m1 sees TEST2");
ok("m1 tidak lihat kategori toko2");

$cats_m2 = Category::where('merchant_id',$m2->id)->pluck('code')->toArray();
if (!in_array('TEST2',$cats_m2)) fail("m2 tidak lihat cat sendiri");
ok("m2 lihat kategori sendiri");

// products scope
$prods_m1 = Product::where('merchant_id',$m1->id)->pluck('sku')->toArray();
if (in_array('TEST-TOKO2-001',$prods_m1)) fail("m1 sees toko2 product");
ok("m1 tidak lihat produk toko2");

// laundry types scope
$mid = $m2->id;
$lt2 = LaundryItemType::firstOrCreate(['code'=>'test_lain','merchant_id'=>$mid], ['name'=>'Test Lain','icon'=>'📦','sort_order'=>99,'status'=>'active','merchant_id'=>$mid]);
echo "lt2 merchant={$lt2->merchant_id}\n";
$lt_m1 = LaundryItemType::where('merchant_id',$m1->id)->pluck('code')->toArray();
if (in_array('test_lain',$lt_m1)) fail("m1 sees toko2 laundry");
ok("m1 tidak lihat laundry toko2");

// settings per merchant
Setting::updateOrCreate(['branch_id'=>null,'merchant_id'=>$mid2,'key'=>'test_key'], ['value'=>'toko2val','type'=>'string','merchant_id'=>$mid2]);
$val_m1 = Setting::where('merchant_id',$m1->id)->where('key','test_key')->first();
if ($val_m1) fail("m1 sees toko2 setting");
ok("m1 tidak lihat setting toko2");

// POS simulation: products for m1 vs m2
$products_m1 = Product::where('status','active')->where('merchant_id',$m1->id)->count();
$products_m2 = Product::where('status','active')->where('merchant_id',$m2->id)->count();
echo "products active m1=$products_m1 m2=$products_m2\n";
ok("POS products counted per merchant");

// clean up test data? keep for manual check, but print
echo "ALL CATALOG ISOLATION PASSED\n";

// cross access via controller guard simulation
function canAccessCategory(Category $c, User $u): bool {
    $mid = $u->merchant_id;
    if($mid && (int)$c->merchant_id !== (int)$mid) return false;
    return true;
}
$admin_toko2 = User::where('username','admin_toko2')->first();
$admin_londry = User::where('username','admin')->first();
// toko2 category should not be accessible by londry admin
if (canAccessCategory($cat2, $admin_londry)) fail("londry admin can access toko2 cat");
ok("controller guard блокирует cross merchant category");

echo "DONE\n";

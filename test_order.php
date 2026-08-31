<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\{Branch,Customer,Product,User};
use App\Services\OrderService;

$branch=Branch::where('code','MLG')->first();
$user=User::where('username','admin')->first();
$cust=Customer::where('code','CUST-000001')->first();
$svc=new OrderService();

echo "Branch: {$branch->code} User: {$user->username}\n";

$order=$svc->create([
  'branch_id'=>$branch->id,
  'customer_id'=>$cust->id,
  'items'=>[
    ['product_id'=>Product::where('sku','SRV-000001')->first()->id, 'quantity'=>1.5, 'price'=>7000],
    ['product_id'=>Product::where('sku','SRV-000002')->first()->id, 'quantity'=>2.75, 'price'=>8000],
    ['product_id'=>Product::where('sku','PRD-000001')->first()->id, 'quantity'=>2, 'price'=>25000],
  ],
  'discount'=>5000, 'discount_type'=>'fixed', 'tax'=>0, 'paid_amount'=> 50000, 'payment_method'=>'cash',
], $user);
echo "ORDER OK: {$order->order_number} total={$order->total} status={$order->payment_status} items=".$order->items->count()."\n";

try{
  $svc->create(['branch_id'=>$branch->id,'customer_id'=>$cust->id,'items'=>[['product_id'=>Product::where('sku','PRD-000001')->first()->id,'quantity'=>1.5,'price'=>25000]],'paid_amount'=>0], $user);
  echo "FAIL: should reject decimal pcs\n";
} catch(Illuminate\Validation\ValidationException $e){ echo "Decimal validation OK: ".json_encode($e->errors())."\n"; }

$svc->updateStatus($order, 'washing', $user); echo "Status washing OK\n";
$svc->updateStatus($order, 'ready', $user); echo "Status ready OK\n";
echo "Stock remaining: ".App\Models\ProductStock::where('product_id', Product::where('sku','PRD-000001')->first()->id)->where('branch_id',$branch->id)->first()->quantity." pcs\n";
echo "ALL TESTS PASSED\n";

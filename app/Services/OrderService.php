<?php
namespace App\Services;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Support\NumberGenerator;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService {
    public function create(array $data, $cashier): Order {
        return DB::transaction(function() use ($data, $cashier){
            $branchId = $data['branch_id'] ?? $cashier->branch_id ?? session('branch_id');
            if(!$branchId) throw ValidationException::withMessages(['branch_id'=>'Cabang belum dipilih']);
            $branch = \App\Models\Branch::find($branchId);
            $merchantId = $cashier->merchant_id ?? $branch?->merchant_id ?? session('merchant_id');

            $items = $data['items'] ?? [];
            if(empty($items)) throw ValidationException::withMessages(['items'=>'Cart kosong']);

            // calculate
            $subtotal = 0;
            $prepared = [];
            foreach($items as $it){
                $product = Product::findOrFail($it['product_id']);
                $qty = (float)$it['quantity'];
                // quantity validation
                if($product->type==='product' && floor($qty) != $qty){
                    throw ValidationException::withMessages(['quantity'=>"Produk {$product->name} harus integer quantity"]);
                }
                if($qty <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity harus >0']);
                $price = (float)($it['price'] ?? $product->price);
                $discount = (float)($it['discount'] ?? 0);
                $lineSubtotal = round($qty * $price - $discount, 2);
                if($lineSubtotal < 0) throw ValidationException::withMessages(['discount'=>'Discount melebihi subtotal item']);
                $subtotal += $lineSubtotal;
                $prepared[] = compact('product','qty','price','discount','lineSubtotal') + ['notes'=>$it['notes']??null,'unit'=>$product->unit];
            }

            $discountTotal = (float)($data['discount'] ?? 0);
            $tax = (float)($data['tax'] ?? 0);
            // handle discount_type percent
            if(($data['discount_type']??null)==='percent'){
                $discountTotal = round($subtotal * $discountTotal / 100, 2);
            }
            if($discountTotal > $subtotal) throw ValidationException::withMessages(['discount'=>'Discount melebihi subtotal']);
            $total = round($subtotal - $discountTotal + $tax, 2);

            // laundry details dinamis: semua key numerik >0 disimpan, catatan/lainnya_desc string; support item tambahan via LaundryItemType
            $laundryDetails = null;
            if(isset($data['laundry_details']) && is_array($data['laundry_details'])){
                $filtered=[];
                foreach($data['laundry_details'] as $k=>$v){
                    if(in_array($k, ['catatan','lainnya_desc'])){
                        $trim = trim((string)$v);
                        if($trim !== '') $filtered[$k] = $trim;
                    } else {
                        // treat as item pcs: coerce to int, only keep >0
                        if($v === '' || $v === null) continue;
                        $int = (int)$v;
                        if($int > 0) $filtered[$k] = $int;
                    }
                }
                if(!empty($filtered)) $laundryDetails=$filtered;
            }
            // backward compat flat laundry_* fields
            if(!$laundryDetails){
                $flat=[];
                foreach($data as $k=>$v){
                    if(str_starts_with($k, 'laundry_') && $v !== '' && $v !== null){
                        $code = substr($k, 8);
                        if(in_array($code, ['catatan','lainnya_desc'])) continue;
                        $int=(int)$v; if($int>0) $flat[$code]=$int;
                    }
                }
                if(!empty($flat)) $laundryDetails=$flat;
            }

            $paid = (float)($data['paid_amount'] ?? 0);
            $allowPartial = setting('allow_partial_payment', '1') !== '0';
            if(!$allowPartial && $paid < $total && $paid != 0){
                throw ValidationException::withMessages(['paid_amount'=>'Partial payment tidak diizinkan']);
            }
            $paymentStatus = 'unpaid';
            if($paid >= $total && $total>0) $paymentStatus='paid';
            elseif($paid > 0) $paymentStatus='partial';
            $change = $paid > $total ? round($paid - $total, 2) : 0;

            $branch = \App\Models\Branch::find($branchId);
            $orderNumber = NumberGenerator::orderNumber($branch);

            $allowedStatus = ['received','washing','drying','ironing','ready','picked_up'];
            $orderStatus = $data['order_status'] ?? 'received';
            if(!in_array($orderStatus, $allowedStatus)) throw ValidationException::withMessages(['order_status'=>'Status tidak valid']);
            $isDemo = (bool)($cashier->is_demo ?? false);
            // jika branch demo juga tandai demo
            try { if(!$isDemo && $branch && (bool)$branch->is_demo) $isDemo = true; } catch(\Throwable $e){}
            $order = Order::create([
                'order_number'=>$orderNumber,
                'branch_id'=>$branchId,'merchant_id'=>$merchantId,
                'customer_id'=>$data['customer_id'] ?? null,
                'cashier_id'=>$cashier->id,
                'is_demo'=>$isDemo,
                'order_date'=> $data['order_date'] ?? now()->toDateString(),
                'pickup_date'=> $data['pickup_date'] ?? null,
                'completed_at'=> $orderStatus==='picked_up' ? now() : null,
                'subtotal'=>$subtotal,'discount'=>$discountTotal,'discount_type'=>$data['discount_type']??null,
                'tax'=>$tax,'total'=>$total,'paid_amount'=> $paymentStatus==='unpaid'?0:$paid,'change_amount'=>$change,
                'payment_status'=>$paymentStatus,'order_status'=>$orderStatus,'notes'=>$data['notes']??null,
                'laundry_details'=>$laundryDetails,
            ]);

            foreach($prepared as $p){
                OrderItem::create([
                    'order_id'=>$order->id,'product_id'=>$p['product']->id,
                    'product_name'=>$p['product']->name,'sku'=>$p['product']->sku,
                    'quantity'=>$p['qty'],'unit'=>$p['unit'],'price'=>$p['price'],
                    'discount'=>$p['discount'],'subtotal'=>$p['lineSubtotal'],'notes'=>$p['notes'],
                ]);
                // stock handling for product type only
                if($p['product']->type==='product'){
                    $stock = ProductStock::firstOrCreate(['product_id'=>$p['product']->id,'branch_id'=>$branchId], ['quantity'=>0,'minimum_stock'=>0]);
                    $old = (float)$stock->quantity;
                    $new = $old - $p['qty'];
                    if($new < 0 && setting('allow_negative_stock','0')==='0'){
                        throw ValidationException::withMessages(['stock'=>"Stok {$p['product']->name} tidak cukup (sisa {$old})"]);
                    }
                    $stock->update(['quantity'=>$new]);
                    StockMovement::create([
                        'product_id'=>$p['product']->id,'branch_id'=>$branchId,'merchant_id'=>$merchantId,'user_id'=>$cashier->id,
                        'type'=>'sale','quantity'=>$p['qty'],'old_quantity'=>$old,'new_quantity'=>$new,'difference'=>-$p['qty'],'reason'=>'Sale order '.$orderNumber
                    ]);
                }
            }

            // payment record if paid >0
            if($paid > 0){
                $payAmount = min($paid, $total);
                Payment::create([
                    'order_id'=>$order->id,'payment_number'=>NumberGenerator::paymentNumber(),
                    'payment_method'=>$data['payment_method']??'cash','amount'=>$payAmount,
                    'paid_at'=>now(),'cashier_id'=>$cashier->id,'status'=>'completed','notes'=>$data['payment_notes']??null,
                ]);
                // cash transaction for sales (only cash payments create cash income; others also recorded but type income)
                CashTransaction::create([
                    'branch_id'=>$branchId,'merchant_id'=>$merchantId,'user_id'=>$cashier->id,'type'=>'income','category'=>'Penjualan',
                    'amount'=>$payAmount,'reference_type'=>'order','reference_id'=>$order->id,
                    'description'=>'Pembayaran order '.$orderNumber,'transaction_date'=>now()->toDateString(),
                ]);
            }

            AuditLogger::log('create','orders', 'Order', $order->id, null, $order->toArray());
            return $order->load(['items','customer','branch']);
        });
    }

    public function updateStatus(Order $order, string $status, $user){
        $allowed = ['received','washing','drying','ironing','ready','picked_up','cancelled'];
        if(!in_array($status, $allowed)) throw ValidationException::withMessages(['order_status'=>'Status tidak valid']);
        // prevent invalid transitions
        $flow = ['received'=>0,'washing'=>1,'drying'=>2,'ironing'=>3,'ready'=>4,'picked_up'=>5];
        $current = $order->order_status;
        if($current==='cancelled' || $current==='picked_up') throw ValidationException::withMessages(['order_status'=>'Order sudah selesai/cancel tidak bisa diubah']);
        if($status==='cancelled'){
            return $this->cancel($order, 'Status changed to cancelled', $user);
        }
        // allow forward only, or same
        if(isset($flow[$status]) && isset($flow[$current]) && $flow[$status] < $flow[$current]){
            throw ValidationException::withMessages(['order_status'=>'Tidak bisa kembali ke status sebelumnya']);
        }
        $old = $order->order_status;
        $order->update(['order_status'=>$status, 'completed_at'=> $status==='picked_up'?now():$order->completed_at]);
        AuditLogger::log('update_status','orders','Order',$order->id, ['order_status'=>$old], ['order_status'=>$status]);
        return $order;
    }

    public function cancel(Order $order, ?string $reason, $user){
        if($order->order_status==='cancelled') throw ValidationException::withMessages(['order_status'=>'Sudah cancelled']);
        if($order->payment_status==='paid' && !$user->hasPermission('orders.cancel')){
            throw ValidationException::withMessages(['orders.cancel'=>'Tidak punya permission cancel order berbayar']);
        }
        DB::transaction(function() use ($order, $reason, $user){
            // reverse stock
            foreach($order->items as $item){
                $product = $item->product;
                if($product && $product->type==='product'){
                    $stock = ProductStock::where('product_id',$product->id)->where('branch_id',$order->branch_id)->first();
                    if($stock){
                        $old = (float)$stock->quantity;
                        $new = $old + (float)$item->quantity;
                        $stock->update(['quantity'=>$new]);
                        StockMovement::create([
                            'product_id'=>$product->id,'branch_id'=>$order->branch_id,'user_id'=>$user->id,
                            'type'=>'return','quantity'=>$item->quantity,'old_quantity'=>$old,'new_quantity'=>$new,'difference'=>$item->quantity,'reason'=>'Cancel order '.$order->order_number
                        ]);
                    }
                }
            }
            $order->update(['order_status'=>'cancelled','cancelled_at'=>now(),'cancelled_by'=>$user->id,'cancel_reason'=>$reason]);
            AuditLogger::log('cancel','orders','Order',$order->id, null, ['reason'=>$reason]);
        });
        return $order;
    }

    public function addPayment(Order $order, array $data, $cashier){
        if($order->order_status==='cancelled') throw ValidationException::withMessages(['order'=>'Order cancelled tidak bisa dibayar']);
        $remaining = (float)$order->total - (float)$order->paid_amount;
        if($remaining <= 0) throw ValidationException::withMessages(['payment'=>'Order sudah lunas']);
        $amount = (float)$data['amount'];
        if($amount <=0) throw ValidationException::withMessages(['amount'=>'Nominal harus >0']);
        if($amount > $remaining) $amount = $remaining; // cap
        return DB::transaction(function() use ($order,$data,$cashier,$amount,$remaining){
            $payment = Payment::create([
                'order_id'=>$order->id,'payment_number'=>NumberGenerator::paymentNumber(),
                'payment_method'=>$data['payment_method']??'cash','amount'=>$amount,
                'paid_at'=>now(),'cashier_id'=>$cashier->id,'reference_number'=>$data['reference_number']??null,'notes'=>$data['notes']??null,
                'status'=>'completed'
            ]);
            $newPaid = (float)$order->paid_amount + $amount;
            $status = $newPaid >= (float)$order->total ? 'paid' : 'partial';
            $order->update(['paid_amount'=>$newPaid,'payment_status'=>$status]);
            CashTransaction::create([
                'branch_id'=>$order->branch_id,'merchant_id'=>$order->merchant_id,'user_id'=>$cashier->id,'type'=>'income','category'=>'Penjualan',
                'amount'=>$amount,'reference_type'=>'payment','reference_id'=>$payment->id,
                'description'=>'Pembayaran order '.$order->order_number,'transaction_date'=>now()->toDateString(),
            ]);
            AuditLogger::log('payment','orders','Payment',$payment->id, null, $payment->toArray());
            return $payment;
        });
    }
}

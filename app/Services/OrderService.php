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

            // Determine order type: laundry has laundry_details or service items; product-only orders auto 'complete'
            $hasLaundry = !empty($laundryDetails) || collect($prepared)->contains(fn($p)=> $p['product']->type==='service');
            $allowedStatus = $hasLaundry
                ? ['received','ready','picked_up']
                : ['complete','received','ready','picked_up']; // allow complete for product sales
            $orderStatus = $data['order_status'] ?? ($hasLaundry ? 'received' : 'complete');
            if(!in_array($orderStatus, $allowedStatus)) throw ValidationException::withMessages(['order_status'=>'Status tidak valid untuk tipe order ini']);
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
                'completed_at'=> in_array($orderStatus, ['picked_up','complete']) ? now() : null,
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
                // stock handling for product type only (qty check dihilangkan — stok boleh minus)
                if($p['product']->type==='product'){
                    $stock = ProductStock::firstOrCreate(['product_id'=>$p['product']->id,'branch_id'=>$branchId], ['quantity'=>0,'minimum_stock'=>0]);
                    $old = (float)$stock->quantity;
                    $new = $old - $p['qty'];
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
        $allowed = ['received','ready','picked_up','complete','cancelled'];
        if(!in_array($status, $allowed)) throw ValidationException::withMessages(['order_status'=>'Status tidak valid']);
        // Determine laundry vs product flow for validation
        $isLaundry = (bool)$order->isLaundry();
        $flowLaundry = ['received'=>0,'ready'=>1,'picked_up'=>2];
        $flowProduct = ['complete'=>0,'picked_up'=>0,'received'=>0]; // product only uses complete; others allowed but map flexible
        $flow = $isLaundry ? $flowLaundry : $flowProduct;
        $current = $order->order_status;
        if(in_array($current, ['cancelled','picked_up','complete'])) throw ValidationException::withMessages(['order_status'=>'Order sudah selesai/cancel tidak bisa diubah']);
        if($status==='cancelled'){
            return $this->cancel($order, 'Status changed to cancelled', $user);
        }
        // allow rollback to 'received' from 'ready' (input error correction), but keep forward flow otherwise
        $isRollbackToReceived = ($status==='received' && $current==='ready');
        // laundry must follow forward flow (received->ready->picked_up), except rollback to received
        if($isLaundry && in_array($status, ['received','ready','picked_up']) && isset($flow[$status]) && isset($flow[$current]) && $flow[$status] < $flow[$current] && !$isRollbackToReceived){
            throw ValidationException::withMessages(['order_status'=>'Tidak bisa kembali ke status sebelumnya']);
        }
        $old = $order->order_status;
        $order->update(['order_status'=>$status, 'completed_at'=> in_array($status, ['picked_up','complete'])?now():$order->completed_at]);
        AuditLogger::log('update_status','orders','Order',$order->id, ['order_status'=>$old], ['order_status'=>$status]);
        return $order;
    }

    public function updateItems(Order $order, array $items, $user){
        if(in_array($order->order_status, ['ready','picked_up','complete','cancelled'])){
            throw ValidationException::withMessages(['order_status'=>'Order sudah ready/picked_up/complete/cancel tidak bisa edit item']);
        }
        if(empty($items)) throw ValidationException::withMessages(['items'=>'Item tidak boleh kosong']);
        return DB::transaction(function() use ($order,$items,$user){
            // Reverse stock for existing product items
            $order->load('items.product');
            foreach($order->items as $item){
                $product = $item->product;
                if($product && $product->type==='product'){
                    $stock = ProductStock::where('product_id',$product->id)->where('branch_id',$order->branch_id)->first();
                    if($stock){
                        $old = (float)$stock->quantity;
                        $new = $old + (float)$item->quantity;
                        $stock->update(['quantity'=>$new]);
                        StockMovement::create(['product_id'=>$product->id,'branch_id'=>$order->branch_id,'merchant_id'=>$order->merchant_id,'user_id'=>$user->id,'type'=>'return','quantity'=>$item->quantity,'old_quantity'=>$old,'new_quantity'=>$new,'difference'=>$item->quantity,'reason'=>'Edit order '.$order->order_number]);
                    }
                }
            }
            // Clear old items
            $order->items()->delete();
            $subtotal = 0;
            foreach($items as $it){
                $product = Product::findOrFail($it['product_id']);
                $qty = (float)$it['quantity'];
                if($product->type==='product' && floor($qty) != $qty) throw ValidationException::withMessages(['quantity'=>"Produk {$product->name} harus integer quantity"]);
                if($qty <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity harus >0']);
                $price = (float)($it['price'] ?? $product->price);
                $discount = (float)($it['discount'] ?? 0);
                $lineSubtotal = round($qty * $price - $discount, 2);
                if($lineSubtotal < 0) throw ValidationException::withMessages(['discount'=>'Discount melebihi subtotal item']);
                $subtotal += $lineSubtotal;
                OrderItem::create(['order_id'=>$order->id,'product_id'=>$product->id,'product_name'=>$product->name,'sku'=>$product->sku,'quantity'=>$qty,'unit'=>$product->unit,'price'=>$price,'discount'=>$discount,'subtotal'=>$lineSubtotal,'notes'=>$it['notes']??null]);
                if($product->type==='product'){
                    $stock = ProductStock::firstOrCreate(['product_id'=>$product->id,'branch_id'=>$order->branch_id], ['quantity'=>0,'minimum_stock'=>0]);
                    $old = (float)$stock->quantity;
                    $new = $old - $qty;
                    $stock->update(['quantity'=>$new]);
                    StockMovement::create(['product_id'=>$product->id,'branch_id'=>$order->branch_id,'merchant_id'=>$order->merchant_id,'user_id'=>$user->id,'type'=>'sale','quantity'=>$qty,'old_quantity'=>$old,'new_quantity'=>$new,'difference'=>-$qty,'reason'=>'Edit order '.$order->order_number]);
                }
            }
            // Recalc order totals keeping original discount/tax (or recalc discount if percent)
            $discountTotal = (float)$order->discount;
            if($order->discount_type==='percent'){ $discountTotal = round($subtotal * (float)$order->discount / 100, 2); }
            if($discountTotal > $subtotal) $discountTotal = $subtotal;
            $total = round($subtotal - $discountTotal + (float)$order->tax, 2);
            $order->update(['subtotal'=>$subtotal,'discount'=>$discountTotal,'total'=>$total]);
            AuditLogger::log('update_items','orders','Order',$order->id, null, ['total'=>$total,'subtotal'=>$subtotal]);
            return $order->load(['items','customer','branch']);
        });
    }

    public function updateFromPos(Order $order, array $data, $user): Order {
        if(in_array($order->order_status, ['ready','picked_up','complete','cancelled'])){
            throw ValidationException::withMessages(['order_status'=>'Order sudah ready/picked_up/complete/cancel tidak bisa diedit']);
        }
        return DB::transaction(function() use ($order,$data,$user){
            // allow customer change
            if(isset($data['customer_id'])){
                $customer = \App\Models\Customer::find($data['customer_id']);
                if(!$customer) throw ValidationException::withMessages(['customer_id'=>'Customer tidak ditemukan']);
                $order->customer_id = $customer->id;
            }
            // laundry_details
            $laundryDetails = $order->laundry_details;
            if(array_key_exists('laundry_details', $data)){
                $raw = $data['laundry_details'];
                if($raw === null){
                    $laundryDetails = null;
                } elseif(is_array($raw)){
                    $filtered=[];
                    foreach($raw as $k=>$v){
                        if(in_array($k, ['catatan','lainnya_desc'])){
                            $trim = trim((string)$v);
                            if($trim !== '') $filtered[$k] = $trim;
                        } else {
                            if($v === '' || $v === null) continue;
                            $int = (int)$v;
                            if($int > 0) $filtered[$k] = $int;
                        }
                    }
                    $laundryDetails = !empty($filtered) ? $filtered : null;
                }
            }
            // flat laundry_* compat
            if($laundryDetails===null){
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
            // handle items: reverse stock, delete, recreate
            $newItems = $data['items'] ?? null;
            $subtotal = (float)$order->subtotal;
            if($newItems !== null){
                if(empty($newItems)) throw ValidationException::withMessages(['items'=>'Item tidak boleh kosong']);
                $order->load('items.product');
                foreach($order->items as $item){
                    $product = $item->product;
                    if($product && $product->type==='product'){
                        $stock = ProductStock::where('product_id',$product->id)->where('branch_id',$order->branch_id)->first();
                        if($stock){
                            $old = (float)$stock->quantity;
                            $new = $old + (float)$item->quantity;
                            $stock->update(['quantity'=>$new]);
                            StockMovement::create(['product_id'=>$product->id,'branch_id'=>$order->branch_id,'merchant_id'=>$order->merchant_id,'user_id'=>$user->id,'type'=>'return','quantity'=>$item->quantity,'old_quantity'=>$old,'new_quantity'=>$new,'difference'=>$item->quantity,'reason'=>'Edit order '.$order->order_number]);
                        }
                    }
                }
                $order->items()->delete();
                $subtotal = 0;
                foreach($newItems as $it){
                    $product = Product::findOrFail($it['product_id']);
                    $qty = (float)$it['quantity'];
                    if($product->type==='product' && floor($qty) != $qty) throw ValidationException::withMessages(['quantity'=>"Produk {$product->name} harus integer quantity"]);
                    if($qty <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity harus >0']);
                    $price = (float)($it['price'] ?? $product->price);
                    $discount = (float)($it['discount'] ?? 0);
                    $lineSubtotal = round($qty * $price - $discount, 2);
                    if($lineSubtotal < 0) throw ValidationException::withMessages(['discount'=>'Discount melebihi subtotal item']);
                    $subtotal += $lineSubtotal;
                    OrderItem::create(['order_id'=>$order->id,'product_id'=>$product->id,'product_name'=>$product->name,'sku'=>$product->sku,'quantity'=>$qty,'unit'=>$product->unit,'price'=>$price,'discount'=>$discount,'subtotal'=>$lineSubtotal,'notes'=>$it['notes']??null]);
                    if($product->type==='product'){
                        $stock = ProductStock::firstOrCreate(['product_id'=>$product->id,'branch_id'=>$order->branch_id], ['quantity'=>0,'minimum_stock'=>0]);
                        $old = (float)$stock->quantity;
                        $new = $old - $qty;
                        $stock->update(['quantity'=>$new]);
                        StockMovement::create(['product_id'=>$product->id,'branch_id'=>$order->branch_id,'merchant_id'=>$order->merchant_id,'user_id'=>$user->id,'type'=>'sale','quantity'=>$qty,'old_quantity'=>$old,'new_quantity'=>$new,'difference'=>-$qty,'reason'=>'Edit order '.$order->order_number]);
                    }
                }
            }
            // discount/tax
            $discountType = $data['discount_type'] ?? $order->discount_type;
            $discountInput = array_key_exists('discount', $data) ? (float)$data['discount'] : (float)$order->discount;
            // if percent, discountInput is percent value; keep as stored discount? Order stores computed discount amount, not percent?
            // For edit via POS we store computed amount: if type percent compute
            $discountTotal = $discountInput;
            if($discountType==='percent'){
                $discountTotal = round($subtotal * $discountInput / 100, 2);
            }
            if($discountTotal > $subtotal) $discountTotal = $subtotal;
            $tax = array_key_exists('tax', $data) ? (float)$data['tax'] : (float)$order->tax;
            $total = round($subtotal - $discountTotal + $tax, 2);
            // status
            $orderStatus = $data['order_status'] ?? $order->order_status;
            $hasLaundry = !empty($laundryDetails) || OrderItem::where('order_id',$order->id)->whereHas('product', fn($q)=>$q->where('type','service'))->exists();
            // also check new items if provided for hasLaundry fallback
            if($newItems !== null){
                $hasLaundry2 = !empty($laundryDetails);
                if(!$hasLaundry2){
                    foreach($newItems as $it){
                        $prod = Product::find($it['product_id']);
                        if($prod && $prod->type==='service'){ $hasLaundry2=true; break; }
                    }
                }
                $hasLaundry = $hasLaundry2;
            }
            $allowedStatus = $hasLaundry ? ['received','ready','picked_up'] : ['complete','received','ready','picked_up'];
            if(!in_array($orderStatus, $allowedStatus)) throw ValidationException::withMessages(['order_status'=>'Status tidak valid untuk tipe order ini']);
            // notes
            $notes = $data['notes'] ?? $order->notes;
            // recompute payment_status vs total keeping paid_amount
            $paidAmount = (float)$order->paid_amount;
            $paymentStatus = $order->payment_status;
            if($total <= 0) $paymentStatus='paid';
            elseif($paidAmount >= $total) $paymentStatus='paid';
            elseif($paidAmount > 0) $paymentStatus='partial';
            else $paymentStatus='unpaid';
            $changeAmount = $paidAmount > $total ? round($paidAmount - $total, 2) : 0;
            $order->update([
                'subtotal'=>$subtotal,
                'discount'=>$discountTotal,
                'discount_type'=>$discountType,
                'tax'=>$tax,
                'total'=>$total,
                'paid_amount'=>$paymentStatus==='unpaid'?0:$paidAmount,
                'change_amount'=>$changeAmount,
                'payment_status'=>$paymentStatus,
                'order_status'=>$orderStatus,
                'laundry_details'=>$laundryDetails,
                'notes'=>$notes,
                'completed_at'=> in_array($orderStatus, ['picked_up','complete']) ? ($order->completed_at ?? now()) : null,
            ]);
            AuditLogger::log('update_pos','orders','Order',$order->id, null, ['total'=>$total,'subtotal'=>$subtotal]);
            return $order->load(['items','customer','branch']);
        });
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

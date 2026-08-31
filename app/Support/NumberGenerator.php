<?php
namespace App\Support;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Str;
class NumberGenerator {
    public static function orderNumber(?Branch $branch = null): string {
        $prefix = $branch ? $branch->code : 'TRX';
        $date = now()->format('Ymd');
        $seq = Order::where('order_number','like', $prefix.'-'.$date.'-%')->count() + 1;
        return sprintf('%s-%s-%06d', $prefix, $date, $seq);
    }
    public static function paymentNumber(): string {
        return 'PAY-'.now()->format('Ymd').'-'.str_pad(Payment::count()+1, 6, '0', STR_PAD_LEFT);
    }
    public static function customerCode(): string {
        return 'CUST-'.str_pad(Customer::count()+1, 6, '0', STR_PAD_LEFT);
    }
    public static function sku(string $type): string {
        $prefix = $type==='service'?'SRV':'PRD';
        $count = Product::where('type',$type)->count()+1;
        return sprintf('%s-%06d',$prefix,$count);
    }
    public static function refundNumber(): string {
        return 'RFD-'.now()->format('Ymd').'-'.Str::random(6);
    }
}

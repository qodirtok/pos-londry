<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Customer;
use App\Models\LaundryItemType;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
class PosController extends Controller {
    public function index(Request $r){
        $mid=auth()->user()->merchant_id;
        $products = Product::with('category')->where('status','active')->when($mid, fn($q)=>$q->where('merchant_id',$mid))->latest()->limit(24)->get();
        $isDemo=(bool)auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id;
        $customers = Customer::when(session('branch_id'), fn($q,$id)=>$q->where('branch_id',$id))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo',$isDemo)->limit(5)->get();
        $laundryTypes = LaundryItemType::active()->when($mid, fn($q)=>$q->where('merchant_id',$mid))->orderBy('sort_order')->orderBy('name')->get();
        return view('pos.index', compact('products','customers','laundryTypes'));
    }
    public function store(Request $r, OrderService $svc){
        $r->validate([
            'customer_id'=>'nullable|exists:customers,id',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required|numeric|min:0.001',
            'paid_amount'=>'nullable|numeric|min:0',
            'discount'=>'nullable|numeric|min:0',
            'tax'=>'nullable|numeric|min:0',
            'order_status'=>'nullable|string|in:received,washing,drying,ironing,ready,picked_up',
        ]);
        $order = $svc->create($r->all(), auth()->user());
        if($r->wantsJson()) return response()->json($order);
        return redirect()->route('orders.show', $order->id)->with('success','Order berhasil: '.$order->order_number);
    }
}

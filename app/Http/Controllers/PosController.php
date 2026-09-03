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
    public function edit(Order $order){
        $isDemo=(bool)auth()->user()->is_demo;
        if((bool)$order->is_demo !== $isDemo) abort(403, 'Tidak punya akses order ini');
        $mid = auth()->user()->merchant_id;
        if($mid && (int)$order->merchant_id !== (int)$mid) abort(403,'Bukan order merchant Anda');
        if(in_array($order->order_status, ['ready','picked_up','complete','cancelled'])){
            abort(403, 'Order sudah '.$order->order_status.' tidak bisa diedit');
        }
        $mid2=auth()->user()->merchant_id;
        $products = Product::with('category')->where('status','active')->when($mid2, fn($q)=>$q->where('merchant_id',$mid2))->latest()->limit(24)->get();
        $isDemo2=(bool)auth()->user()->is_demo;
        $customers = Customer::when(session('branch_id'), fn($q,$id)=>$q->where('branch_id',$id))->when($mid2, fn($qq)=>$qq->where('merchant_id',$mid2))->where('is_demo',$isDemo2)->limit(5)->get();
        $laundryTypes = LaundryItemType::active()->when($mid2, fn($q)=>$q->where('merchant_id',$mid2))->orderBy('sort_order')->orderBy('name')->get();
        $order->load(['items.product','customer','branch']);
        return view('pos.index', compact('products','customers','laundryTypes','order'));
    }
    public function store(Request $r, OrderService $svc){
        $r->validate([
            'customer_id'=>'required|exists:customers,id',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required|numeric|min:0.001',
            'paid_amount'=>'nullable|numeric|min:0',
            'discount'=>'nullable|numeric|min:0',
            'tax'=>'nullable|numeric|min:0',
            'order_status'=>'nullable|string|in:received,ready,picked_up,complete',
        ]);
        $order = $svc->create($r->all(), auth()->user());
        if($r->wantsJson()) return response()->json($order);
        return redirect()->route('orders.show', $order->id)->with('success','Order berhasil: '.$order->order_number);
    }
    public function update(Request $r, Order $order, OrderService $svc){
        $isDemo=(bool)auth()->user()->is_demo;
        if((bool)$order->is_demo !== $isDemo) abort(403, 'Tidak punya akses order ini');
        $mid = auth()->user()->merchant_id;
        if($mid && (int)$order->merchant_id !== (int)$mid) abort(403,'Bukan order merchant Anda');
        $r->validate([
            'customer_id'=>'nullable|exists:customers,id',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required|numeric|min:0.001',
            'items.*.price'=>'nullable|numeric|min:0',
            'items.*.discount'=>'nullable|numeric|min:0',
            'discount'=>'nullable|numeric|min:0',
            'discount_type'=>'nullable|in:fixed,percent',
            'tax'=>'nullable|numeric|min:0',
            'order_status'=>'nullable|string|in:received,ready,picked_up,complete',
            'laundry_details'=>'nullable|array',
            'notes'=>'nullable|string',
        ]);
        $order = $svc->updateFromPos($order, $r->all(), auth()->user());
        if($r->wantsJson()) return response()->json($order);
        return redirect()->route('orders.show', $order->id)->with('success','Order diperbarui: '.$order->order_number);
    }
}

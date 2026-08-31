<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\CashTransaction;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller {
    public function index(){ return view('reports.index'); }
    public function sales(Request $r){
        $branchId=session('branch_id'); $isDemo=(bool)auth()->user()->is_demo; $mid=auth()->user()->merchant_id;
        $from=$r->from ?? now()->subDays(7)->toDateString();
        $to=$r->to ?? now()->toDateString();
        $q=Order::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo',$isDemo)->whereBetween('order_date',[$from,$to])->where('order_status','!=','cancelled');
        $summary=['total'=>$q->count(),'gross'=>$q->sum('subtotal'),'discount'=>$q->sum('discount'),'net'=>$q->sum('total')];
        $perDay=(clone $q)->selectRaw("order_date, sum(total) as total, count(*) as cnt")->groupBy('order_date')->orderBy('order_date')->get();
        return view('reports.sales', compact('summary','perDay','from','to'));
    }
    public function payments(Request $r){
        $branchId=session('branch_id'); $mid=auth()->user()->merchant_id;
        $from=$r->from ?? now()->subDays(30)->toDateString();
        $to=$r->to ?? now()->toDateString();
        $isDemo2=(bool)auth()->user()->is_demo; $data=\App\Models\Payment::whereHas('order', fn($qq)=>$qq->when($branchId, fn($q)=>$q->where('branch_id',$branchId))->when($mid, fn($q)=>$q->where('merchant_id',$mid))->where('is_demo',$isDemo2)->whereBetween('order_date',[$from,$to]))->selectRaw("payment_method, sum(amount) as total")->groupBy('payment_method')->get();
        return view('reports.payments', compact('data','from','to'));
    }
    public function cash(Request $r){
        $branchId=session('branch_id'); $mid=auth()->user()->merchant_id;
        $from=$r->from ?? now()->startOfMonth()->toDateString();
        $to=$r->to ?? now()->toDateString();
        $q=CashTransaction::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->whereBetween('transaction_date',[$from,$to]);
        // demo/prod already isolated via branch_id (DEMO branch), no extra is_demo column on cash
        $summary=['income'=>(clone $q)->where('type','income')->sum('amount'),'expense'=>(clone $q)->where('type','expense')->sum('amount')];
        $summary['net']=$summary['income']-$summary['expense'];
        $perCat=(clone $q)->selectRaw("category, type, sum(amount) as total")->groupBy('category','type')->get();
        return view('reports.cash', compact('summary','perCat','from','to'));
    }
    public function products(Request $r){
        $branchId=session('branch_id'); $mid=auth()->user()->merchant_id;
        $from=$r->from ?? now()->subDays(30)->toDateString();
        $isDemo2=(bool)auth()->user()->is_demo; $data=DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->join('products','products.id','=','order_items.product_id')
            ->when($branchId, fn($q)=>$q->where('orders.branch_id',$branchId))->when($mid, fn($q)=>$q->where('orders.merchant_id',$mid))->where('orders.is_demo',$isDemo2)
            ->whereBetween('orders.order_date',[$from, $r->to ?? now()->toDateString()])
            ->where('orders.order_status','!=','cancelled')
            ->selectRaw("products.name, sum(order_items.quantity) as qty, sum(order_items.subtotal) as revenue")
            ->groupBy('products.name')->orderByDesc('revenue')->limit(20)->get();
        return view('reports.products', compact('data','from'));
    }
    public function customers(Request $r){
        $branchId=session('branch_id'); $isDemo=(bool)auth()->user()->is_demo; $mid=auth()->user()->merchant_id;
        $data=Customer::when($branchId, fn($q)=>$q->where('branch_id',$branchId))->when($mid, fn($q)=>$q->where('merchant_id',$mid))->where('is_demo',$isDemo)->withCount(['orders'=>fn($q)=>$q->where('is_demo',$isDemo)])->withSum(['orders'=>fn($q)=>$q->where('is_demo',$isDemo)],'total')->orderByDesc('orders_sum_total')->limit(20)->get();
        return view('reports.customers', compact('data'));
    }
    public function laundry(Request $r){
        $branchId=session('branch_id'); $isDemo=(bool)auth()->user()->is_demo; $mid=auth()->user()->merchant_id;
        $q=Order::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo',$isDemo);
        $summary=[
            'total'=>(clone $q)->count(),
            'pending'=>(clone $q)->whereIn('order_status',['received','washing','drying','ironing'])->count(),
            'ready'=>(clone $q)->where('order_status','ready')->count(),
            'picked'=>(clone $q)->where('order_status','picked_up')->count(),
            'cancelled'=>(clone $q)->where('order_status','cancelled')->count(),
            'weight'=> DB::table('order_items')->join('products','products.id','=','order_items.product_id')->join('orders','orders.id','=','order_items.order_id')
                ->when($branchId, fn($qq)=>$qq->where('orders.branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('orders.merchant_id',$mid))->where('orders.is_demo',$isDemo)->where('products.type','service')->sum('order_items.quantity'),
            'revenue'=>(clone $q)->where('order_status','!=','cancelled')->sum('total'),
        ];
        $byStatus=(clone $q)->selectRaw("order_status, count(*) as cnt")->groupBy('order_status')->pluck('cnt','order_status');
        return view('reports.laundry', compact('summary','byStatus'));
    }
}

<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Customer;
use App\Models\CashTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
class DashboardController extends Controller {
    public function index(Request $request){
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $isDemo=(bool)auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id;
        $today = now()->toDateString();
        $q = Order::query()->where('is_demo',$isDemo)->when($mid, fn($qq)=>$qq->where('merchant_id',$mid));
        if($branchId) $q->where('branch_id',$branchId);
        $todayQuery = (clone $q)->whereDate('order_date',$today);
        $stats = [
            'today_sales' => (clone $todayQuery)->sum('total'),
            'today_orders' => (clone $todayQuery)->count(),
            'today_customers' => Customer::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo',$isDemo)->whereDate('created_at',$today)->count(),
            'cash_income' => CashTransaction::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('type','income')->whereDate('transaction_date',$today)->sum('amount'),
            'cash_expense' => CashTransaction::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('type','expense')->whereDate('transaction_date',$today)->sum('amount'),
            'pending_laundry' => (clone $q)->whereIn('order_status',['received'])->count(),
            'ready_laundry' => (clone $q)->where('order_status','ready')->count(),
            'outstanding' => (clone $q)->where('payment_status','!=','paid')->where('order_status','!=','cancelled')->sum(\Illuminate\Support\Facades\DB::raw('total - paid_amount')),
        ];
        $sales7 = Order::where('is_demo',$isDemo)->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))
            ->where('order_date','>=', now()->subDays(6)->toDateString())
            ->selectRaw("order_date, sum(total) as total, count(*) as cnt")
            ->groupBy('order_date')->orderBy('order_date')->get();
        $byStatus = Order::where('is_demo',$isDemo)->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->selectRaw("order_status, count(*) as cnt")->groupBy('order_status')->pluck('cnt','order_status');
        $recent = Order::where('is_demo',$isDemo)->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->with(['customer','cashier'])->latest()->limit(5)->get();
        $queueList = Order::where('is_demo',$isDemo)
            ->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))
            ->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))
            ->with(['customer'])
            ->where('order_status', 'received')
            ->orderBy('order_date', 'asc')
            ->limit(5)
            ->get();
        return view('dashboard', compact('stats','sales7','byStatus','recent','queueList'));
    }
}

<?php
namespace App\Http\Controllers;
use App\Models\CashTransaction;
use App\Models\CashCategory;
use Illuminate\Http\Request;
class CashController extends Controller {
    public function index(Request $r){
        $branchId=session('branch_id'); $mid=auth()->user()->merchant_id;
        $q=CashTransaction::with(['user','branch'])->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->latest();
        if($r->type) $q->where('type',$r->type);
        if($r->from) $q->whereDate('transaction_date','>=',$r->from);
        if($r->to) $q->whereDate('transaction_date','<=',$r->to);
        $transactions=$q->paginate(15);
        return view('cash.index', compact('transactions'));
    }
    public function create(){ $categories=CashCategory::all(); return view('cash.create', compact('categories')); }
    public function store(Request $r){
        $r->validate(['type'=>'required|in:income,expense','amount'=>'required|numeric|min:1','category'=>'nullable|string','description'=>'nullable|string']);
        $branchId=session('branch_id')??auth()->user()->branch_id;
        $branch=\App\Models\Branch::find($branchId);
        $mid=auth()->user()->merchant_id ?? $branch?->merchant_id;
        CashTransaction::create(['branch_id'=>$branchId,'merchant_id'=>$mid,'user_id'=>auth()->id(),'type'=>$r->type,'category'=>$r->category,'amount'=>$r->amount,'description'=>$r->description,'transaction_date'=>$r->transaction_date ?? now()->toDateString()]);
        return redirect()->route('cash.index')->with('success','Transaksi kas dibuat');
    }
}

<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Support\NumberGenerator;
use Illuminate\Http\Request;
class CustomerController extends Controller {
    public function index(Request $r){
        $branchId = session('branch_id');
        $isDemo = (bool)auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id;
        $q = Customer::query()->when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo', $isDemo);
        if($s=$r->search) $q->where(fn($qq)=>$qq->where('name','like',"%$s%")->orWhere('phone','like',"%$s%")->orWhere('code','like',"%$s%"));
        $customers = $q->latest()->paginate(10);
        return view('customers.index',compact('customers'));
    }
    public function create(){ return view('customers.create'); }
    public function store(Request $r){
        $r->validate(['name'=>'required','phone'=>'nullable','email'=>'nullable|email']);
        $branchId = session('branch_id') ?? auth()->user()->branch_id ?? \App\Models\Branch::first()->id;
        $branch = \App\Models\Branch::find($branchId);
        $isDemo = (bool)auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id ?? $branch?->merchant_id ?? \App\Models\Branch::first()?->merchant_id;
        Customer::create(['code'=>NumberGenerator::customerCode(),'name'=>$r->name,'phone'=>$r->phone,'email'=>$r->email,'address'=>$r->address,'notes'=>$r->notes,'status'=>$r->status??'active','branch_id'=>$branchId,'merchant_id'=>$mid,'is_demo'=>$isDemo]);
        return redirect()->route('customers.index')->with('success','Customer dibuat');
    }
    public function edit(Customer $customer){
        $isDemo=(bool)auth()->user()->is_demo; if((bool)$customer->is_demo!==$isDemo) abort(403); return view('customers.edit',compact('customer')); }
    public function update(Request $r, Customer $customer){
        $isDemo=(bool)auth()->user()->is_demo; if((bool)$customer->is_demo!==$isDemo) abort(403);
        $r->validate(['name'=>'required']);
        $customer->update($r->only(['name','phone','email','address','notes','status']));
        return redirect()->route('customers.index')->with('success','Customer diupdate');
    }
    public function destroy(Customer $customer){ $isDemo=(bool)auth()->user()->is_demo; if((bool)$customer->is_demo!==$isDemo) abort(403); $customer->delete(); return back()->with('success','Dihapus'); }
    // API for POS search
    public function search(Request $r){
        $branchId = session('branch_id');
        $isDemo = (bool)auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id;
        $s=$r->q;
        $q=Customer::when($branchId, fn($qq)=>$qq->where('branch_id',$branchId))->when($mid, fn($qq)=>$qq->where('merchant_id',$mid))->where('is_demo',$isDemo);
        if($s) $q->where(fn($qq)=>$qq->where('name','like',"%$s%")->orWhere('phone','like',"%$s%")->orWhere('code','like',"%$s%"));
        return response()->json($q->limit(10)->get(['id','code','name','phone']));
    }
    public function show(Customer $customer){
        $isDemo = (bool)auth()->user()->is_demo;
        if((bool)$customer->is_demo !== $isDemo) abort(403, 'Tidak punya akses customer ini');
        $customer->load(['orders'=>fn($q)=>$q->where('is_demo',$isDemo)->latest()->limit(10)]); return view('customers.show',compact('customer')); }
}

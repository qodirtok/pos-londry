<?php
namespace App\Http\Controllers;
use App\Models\Branch;
use Illuminate\Http\Request;
class BranchController extends Controller {
    private function scopeQuery(){
        $user = auth()->user();
        $q = Branch::query();
        if($user->merchant_id){
            $q->where('merchant_id', $user->merchant_id);
        }
        // super admin (merchant_id null) lihat semua
        return $q;
    }
    private function authorizeBranch(Branch $b){
        $user = auth()->user();
        if($user->merchant_id && (int)$b->merchant_id !== (int)$user->merchant_id) abort(403,'Bukan cabang merchant Anda');
    }
    public function index(){ $branches=$this->scopeQuery()->latest()->paginate(10); return view('branches.index',compact('branches')); }
    public function create(){ return view('branches.create'); }
    public function store(Request $r){
        $r->validate(['code'=>'required|unique:branches,code','name'=>'required','status'=>'required|in:active,inactive']);
        Branch::create(array_merge($r->only(['code','name','phone','email','address','city','province','postal_code','status']), ['merchant_id'=>auth()->user()->merchant_id ?? Branch::first()?->merchant_id, 'is_demo'=>(bool)auth()->user()->is_demo]));
        return redirect()->route('branches.index')->with('success','Cabang dibuat');
    }
    public function show(Branch $branch){ $this->authorizeBranch($branch); return redirect()->route('branches.edit',$branch); }
    public function edit(Branch $branch){ $this->authorizeBranch($branch); return view('branches.edit',compact('branch')); }
    public function update(Request $r, Branch $branch){
        $r->validate(['code'=>'required|unique:branches,code,'.$branch->id,'name'=>'required','status'=>'required|in:active,inactive']);
        $this->authorizeBranch($branch); $branch->update($r->only(['code','name','phone','email','address','city','province','postal_code','status']));
        return redirect()->route('branches.index')->with('success','Cabang diupdate');
    }
    public function destroy(Branch $branch){
        $this->authorizeBranch($branch);
        if(\App\Models\Order::where('branch_id',$branch->id)->exists()) return back()->with('error','Cabang masih punya order, tidak bisa dihapus');
        if(\App\Models\Customer::where('branch_id',$branch->id)->exists()) return back()->with('error','Cabang masih punya customer');
        if(\App\Models\User::where('branch_id',$branch->id)->exists()) return back()->with('error','Masih ada user terikat cabang ini');
        $branch->delete(); return back()->with('success','Dihapus');
    }
}

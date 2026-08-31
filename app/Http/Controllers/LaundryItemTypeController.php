<?php
namespace App\Http\Controllers;
use App\Models\LaundryItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class LaundryItemTypeController extends Controller {
    public function index(Request $r){
        $mid=auth()->user()->merchant_id; $types = LaundryItemType::when($mid, fn($q)=>$q->where('merchant_id',$mid))->orderBy('sort_order')->orderBy('name')->get();
        if($r->wantsJson() || $r->is('api/*')) return response()->json($types);
        return view('laundry-types.index', compact('types'));
    }
    public function apiIndex(){
        $mid=auth()->user()->merchant_id; return response()->json(LaundryItemType::active()->when($mid, fn($q)=>$q->where('merchant_id',$mid))->orderBy('sort_order')->orderBy('name')->get());
    }
    public function store(Request $r){
        $r->validate(['name'=>'required|string|max:30','icon'=>'nullable|string|max:10']);
        $name = trim($r->name);
        $code = Str::slug($name, '_');
        $code = preg_replace('/[^a-z0-9_]/','', $code);
        if(strlen($code) < 2) $code = 'item_'.time();
        $mid=auth()->user()->merchant_id ?? \App\Models\Merchant::first()?->id;
        if(LaundryItemType::where('code',$code)->where('merchant_id',$mid)->exists()){
            if($r->wantsJson()) return response()->json(['message'=>'Jenis sudah ada: '.$code], 422);
            return back()->withErrors(['name'=>'Jenis sudah ada']);
        }
        $max = LaundryItemType::max('sort_order') ?? 0;
        $t = LaundryItemType::create([
            'code'=>$code,
            'name'=>$name,
            'icon'=>$r->icon ?: '📦',
            'sort_order'=>$max+1,
            'status'=>'active',
            'branch_id'=> session('branch_id') ?? auth()->user()->branch_id,
            'merchant_id'=>$mid,
        ]);
        if($r->wantsJson()) return response()->json($t, 201);
        return back()->with('success','Jenis ditambahkan: '.$name);
    }
    public function destroy(LaundryItemType $laundryItemType){
        $mid=auth()->user()->merchant_id; if($mid && (int)$laundryItemType->merchant_id !== (int)$mid) abort(403);
        $laundryItemType->delete();
        if(request()->wantsJson()) return response()->json(['ok'=>true]);
        return back()->with('success','Jenis dihapus');
    }
    // API delete by id
    public function apiDestroy($id){
        $t = LaundryItemType::findOrFail($id);
        $mid=auth()->user()->merchant_id; if($mid && (int)$t->merchant_id !== (int)$mid) abort(403);
        $t->delete();
        return response()->json(['ok'=>true]);
    }
}

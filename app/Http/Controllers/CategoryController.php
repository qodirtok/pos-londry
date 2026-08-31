<?php
namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
class CategoryController extends Controller {
    public function index(){ $mid=auth()->user()->merchant_id; $categories=Category::when($mid, fn($q)=>$q->where('merchant_id',$mid))->latest()->paginate(10); return view('categories.index',compact('categories')); }
    public function create(){ return view('categories.create'); }
    public function store(Request $r){
        $mid=auth()->user()->merchant_id ?? \App\Models\Merchant::first()?->id;
        $r->validate(['name'=>'required','code'=>'required|unique:categories,code']);
        // code unique per merchant? jaga global unique dulu, tapi cek merchant duplicate
        if(Category::where('code',$r->code)->where('merchant_id',$mid)->exists()) return back()->withErrors(['code'=>'Kode kategori sudah ada di toko ini']);
        Category::create($r->only(['name','code','description','status'])+['status'=>$r->status??'active','merchant_id'=>$mid]); return redirect()->route('categories.index')->with('success','Kategori dibuat');
    }
    public function edit(Category $category){ $mid=auth()->user()->merchant_id; if($mid && (int)$category->merchant_id !== (int)$mid) abort(403); return view('categories.edit',compact('category')); }
    public function update(Request $r, Category $category){ $mid=auth()->user()->merchant_id; if($mid && (int)$category->merchant_id !== (int)$mid) abort(403); $r->validate(['name'=>'required','code'=>'required|unique:categories,code,'.$category->id]); $category->update($r->only(['name','code','description','status'])); return redirect()->route('categories.index')->with('success','Diupdate'); }
    public function destroy(Category $category){ $mid=auth()->user()->merchant_id; if($mid && (int)$category->merchant_id !== (int)$mid) abort(403); if(\App\Models\Product::where('category_id',$category->id)->exists()) return back()->with('error','Kategori masih dipakai produk, hapus/pindahkan produk dulu atau nonaktifkan kategori'); $category->delete(); return back()->with('success','Dihapus'); }
}

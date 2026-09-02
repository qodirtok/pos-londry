<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
use App\Support\NumberGenerator;
use Illuminate\Http\Request;
class ProductController extends Controller {
    public function index(Request $r){
        $mid=auth()->user()->merchant_id;
        $q=Product::with('category')->when($mid, fn($qq)=>$qq->where('merchant_id',$mid));
        if($s=$r->search) $q->where(fn($qq)=>$qq->where('name','like',"%$s%")->orWhere('sku','like',"%$s%")->orWhere('barcode','like',"%$s%"));
        if($r->type) $q->where('type',$r->type);
        if($r->category_id) $q->where('category_id',$r->category_id);
        if($r->status) $q->where('status',$r->status);
        else $q->where('status','active'); // default: hanya tampilkan produk aktif
        $products=$q->latest()->paginate(12);
        return view('products.index',compact('products'));
    }
    public function create(){ $mid=auth()->user()->merchant_id; $categories=Category::when($mid, fn($q)=>$q->where('merchant_id',$mid))->get(); return view('products.create',compact('categories')); }
    public function store(Request $r){
        $r->validate(['name'=>'required','category_id'=>'required|exists:categories,id','type'=>'required|in:product,service','price'=>'required|numeric|min:0','unit'=>'required','sku'=>'nullable|unique:products,sku']);
        $mid=auth()->user()->merchant_id ?? \App\Models\Merchant::first()?->id;
        $data=$r->only(['sku','barcode','name','category_id','type','price','cost','unit','status','description']);
        $data['merchant_id']=$mid;
        // validate category belongs to merchant
        $cat=Category::find($r->category_id); if($cat && $mid && (int)$cat->merchant_id !== (int)$mid) abort(403,'Kategori bukan milik toko Anda');
        if(empty($data['sku'])) $data['sku']=NumberGenerator::sku($data['type']);
        $data['status']=$data['status']??'active'; $data['cost']=$data['cost']??0;
        Product::create($data);
        // stocks for product type
        $prod=Product::where('sku',$data['sku'])->first();
        if($prod->type==='product'){
            $bid=session('branch_id')??auth()->user()->branch_id;
            if($bid) \App\Models\ProductStock::firstOrCreate(['product_id'=>$prod->id,'branch_id'=>$bid],[ 'quantity'=>0,'minimum_stock'=>0]);
        }
        return redirect()->route('products.index')->with('success','Produk dibuat');
    }
    public function edit(Product $product){ $mid=auth()->user()->merchant_id; if($mid && (int)$product->merchant_id !== (int)$mid) abort(403); $categories=Category::when($mid, fn($q)=>$q->where('merchant_id',$mid))->get(); return view('products.edit',compact('product','categories')); }
    public function update(Request $r, Product $product){
        $mid=auth()->user()->merchant_id; if($mid && (int)$product->merchant_id !== (int)$mid) abort(403);
        $r->validate(['name'=>'required','category_id'=>'required|exists:categories,id','type'=>'required|in:product,service','price'=>'required|numeric|min:0','unit'=>'required','sku'=>'required|unique:products,sku,'.$product->id]);
        $cat=Category::find($r->category_id); if($cat && $mid && (int)$cat->merchant_id !== (int)$mid) abort(403,'Kategori bukan milik toko Anda');
        $product->update($r->only(['sku','barcode','name','category_id','type','price','cost','unit','status','description']));
        return redirect()->route('products.index')->with('success','Diupdate');
    }
    public function destroy(Product $product){
        $mid=auth()->user()->merchant_id; if($mid && (int)$product->merchant_id !== (int)$mid) abort(403);
        if($product->type==='product' && \App\Models\OrderItem::where('product_id',$product->id)->exists()){
            $product->update(['status'=>'inactive']); return back()->with('success','Produk sudah pernah dipakai, dinonaktifkan saja');
        }
        $product->delete(); return back()->with('success','Dihapus');
    }
    public function show(Product $product){ $mid=auth()->user()->merchant_id; if($mid && (int)$product->merchant_id !== (int)$mid) abort(403); return redirect()->route('products.edit',$product); }
    public function search(Request $r){
        $s=$r->q;
        $mid=auth()->user()->merchant_id;
        $q=Product::with('category')->where('status','active')->when($mid, fn($qq)=>$qq->where('merchant_id',$mid));
        if($s) $q->where(fn($qq)=>$qq->where('name','like',"%$s%")->orWhere('sku','like',"%$s%")->orWhere('barcode','like',"%$s%"));
        return response()->json($q->limit(12)->get());
    }
}

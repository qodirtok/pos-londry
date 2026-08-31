<?php
namespace App\Http\Controllers;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class MerchantController extends Controller {
    private function scopeQuery(){
        $user = auth()->user();
        $q = Merchant::query();
        // super admin (Admin tanpa merchant_id) lihat semua; yang punya merchant_id hanya miliknya
        if($user->merchant_id && !$user->isAdmin() || ($user->merchant_id && !auth()->user()->isDemo() && $user->merchant_id)){
            // non-super-admin: hanya merchant sendiri
            // super admin = isAdmin() && merchant_id == null -> lihat semua
            $isSuper = $user->isAdmin() && $user->merchant_id === null;
            if(!$isSuper) $q->where('id', $user->merchant_id);
        }
        // demo juga terisolasi via is_demo? merchants belum punya is_demo, tapi demo user merchant= LONDRY-001 jadi ikut terfilter
        return $q;
    }
    public function index(){
        $merchants = $this->scopeQuery()->latest()->paginate(10);
        return view('merchants.index', compact('merchants'));
    }
    public function create(){ 
        $user = auth()->user();
        $isSuper = $user->isAdmin() && $user->merchant_id === null;
        if(!$isSuper && $user->merchant_id) abort(403,'Anda sudah terikat merchant, tidak bisa buat merchant baru');
        return view('merchants.create'); 
    }
    public function store(Request $r){
        $user = auth()->user();
        $isSuper = $user->isAdmin() && $user->merchant_id === null;
        if(!$isSuper && $user->merchant_id) abort(403);
        $r->validate(['name'=>'required','code'=>'required|unique:merchants,code','status'=>'required|in:active,inactive']);
        $m = Merchant::create([
            'code'=>$r->code,'name'=>$r->name,'slug'=>Str::slug($r->name).'-'.strtolower($r->code),
            'phone'=>$r->phone,'email'=>$r->email,'address'=>$r->address,'city'=>$r->city,'status'=>$r->status,
            'owner_user_id'=>$user->id,
        ]);
        // seed katalog & setting default untuk merchant baru
        $mid = $m->id;
        $cats = [
            ['name'=>'Laundry','code'=>'LNDRY','description'=>'Semua layanan laundry'],
            ['name'=>'Cuci Kering','code'=>'CKERING','description'=>'Cuci kering'],
            ['name'=>'Cuci Setrika','code'=>'CSET','description'=>'Cuci + setrika'],
            ['name'=>'Setrika','code'=>'SETRIKA','description'=>'Setrika saja'],
            ['name'=>'Express','code'=>'EXPRESS','description'=>'Layanan express'],
            ['name'=>'Minuman','code'=>'MINUMAN','description'=>'Produk minuman'],
            ['name'=>'Parfum','code'=>'PARFUM','description'=>'Parfum laundry'],
            ['name'=>'Packaging','code'=>'PACK','description'=>'Packaging'],
        ];
        foreach($cats as $c) \App\Models\Category::firstOrCreate(['code'=>$c['code'],'merchant_id'=>$mid], $c+['status'=>'active','merchant_id'=>$mid]);
        $catLaundry = \App\Models\Category::where('code','LNDRY')->where('merchant_id',$mid)->first();
        $catCK = \App\Models\Category::where('code','CKERING')->where('merchant_id',$mid)->first();
        $catCS = \App\Models\Category::where('code','CSET')->where('merchant_id',$mid)->first();
        $catSet = \App\Models\Category::where('code','SETRIKA')->where('merchant_id',$mid)->first();
        $catExp = \App\Models\Category::where('code','EXPRESS')->where('merchant_id',$mid)->first();
        $catParfum = \App\Models\Category::where('code','PARFUM')->where('merchant_id',$mid)->first();
        $catPack = \App\Models\Category::where('code','PACK')->where('merchant_id',$mid)->first();
        $products = [
            ['sku'=>'SRV-'.$mid.'-000001','name'=>'Cuci Kering Reguler','category_id'=>$catCK->id,'type'=>'service','price'=>7000,'cost'=>3000,'unit'=>'kg'],
            ['sku'=>'SRV-'.$mid.'-000002','name'=>'Cuci Setrika Reguler','category_id'=>$catCS->id,'type'=>'service','price'=>8000,'cost'=>3500,'unit'=>'kg'],
            ['sku'=>'SRV-'.$mid.'-000003','name'=>'Setrika Saja','category_id'=>$catSet->id,'type'=>'service','price'=>6000,'cost'=>2500,'unit'=>'kg'],
            ['sku'=>'SRV-'.$mid.'-000004','name'=>'Cuci Kering Express','category_id'=>$catExp->id,'type'=>'service','price'=>12000,'cost'=>5000,'unit'=>'kg'],
            ['sku'=>'SRV-'.$mid.'-000005','name'=>'Cuci Satuan Kemeja','category_id'=>$catLaundry->id,'type'=>'service','price'=>8000,'cost'=>3000,'unit'=>'pcs'],
            ['sku'=>'PRD-'.$mid.'-000001','name'=>'Parfum Laundry 100ml','category_id'=>$catParfum->id,'type'=>'product','price'=>25000,'cost'=>15000,'unit'=>'pcs','barcode'=>'899'.$mid.'00000001'],
            ['sku'=>'PRD-'.$mid.'-000002','name'=>'Parfum Laundry 250ml','category_id'=>$catParfum->id,'type'=>'product','price'=>50000,'cost'=>30000,'unit'=>'pcs','barcode'=>'899'.$mid.'00000002'],
            ['sku'=>'PRD-'.$mid.'-000003','name'=>'Plastik Packaging','category_id'=>$catPack->id,'type'=>'product','price'=>5000,'cost'=>2000,'unit'=>'pcs','barcode'=>'899'.$mid.'00000003'],
            ['sku'=>'PRD-'.$mid.'-000004','name'=>'Detergent Premium 1L','category_id'=>$catPack->id,'type'=>'product','price'=>35000,'cost'=>20000,'unit'=>'pcs','barcode'=>'899'.$mid.'00000004'],
        ];
        foreach($products as $pr){ \App\Models\Product::firstOrCreate(['sku'=>$pr['sku']], $pr+['status'=>'active','merchant_id'=>$mid]); }
        foreach(\App\Models\LaundryItemType::defaults() as $d) \App\Models\LaundryItemType::firstOrCreate(['code'=>$d['code'],'merchant_id'=>$mid], $d+['merchant_id'=>$mid]);
        $branch = \App\Models\Branch::where('merchant_id',$mid)->first();
        foreach([
            ['code'=>'cash','name'=>'Cash','status'=>'active'],
            ['code'=>'transfer','name'=>'Transfer','status'=>'active'],
            ['code'=>'qris','name'=>'QRIS','status'=>'active'],
            ['code'=>'debit','name'=>'Debit','status'=>'active'],
            ['code'=>'credit_card','name'=>'Credit Card','status'=>'active'],
            ['code'=>'e_wallet','name'=>'E-Wallet','status'=>'active'],
        ] as $pm) \App\Models\PaymentMethod::firstOrCreate(['code'=>$pm['code']], $pm);
        foreach([
            ['key'=>'app_name','value'=>$m->name.' POS','type'=>'string'],
            ['key'=>'company_name','value'=>$m->name,'type'=>'string'],
            ['key'=>'currency','value'=>'IDR','type'=>'string'],
            ['key'=>'timezone','value'=>'Asia/Jakarta','type'=>'string'],
            ['key'=>'receipt_header','value'=>'Terima kasih telah menggunakan layanan kami','type'=>'string'],
            ['key'=>'receipt_footer','value'=>'Barang yang sudah dicuci tidak dapat dikembalikan','type'=>'string'],
        ] as $st) \App\Models\Setting::firstOrCreate(['branch_id'=>null,'merchant_id'=>$mid,'key'=>$st['key']], $st+['merchant_id'=>$mid]);
        if($branch) \App\Models\Setting::firstOrCreate(['branch_id'=>$branch->id,'key'=>'branch_receipt_header'], ['value'=>$m->name.' - '.$branch->name,'type'=>'string','merchant_id'=>$mid]);
        // auto buat cabang WALKAWY jika belum ada (merchant baru tanpa cabang) - tidak, biarkan user buat via Branch
        return redirect()->route('merchants.index')->with('success','Merchant dibuat + katalog default terisi');
    }
    public function show(Merchant $merchant){
        $this->authorizeMerchant($merchant);
        return redirect()->route('merchants.edit',$merchant);
    }
    public function edit(Merchant $merchant){
        $this->authorizeMerchant($merchant);
        return view('merchants.edit', compact('merchant'));
    }
    public function update(Request $r, Merchant $merchant){
        $this->authorizeMerchant($merchant);
        $r->validate(['name'=>'required','code'=>'required|unique:merchants,code,'.$merchant->id,'status'=>'required|in:active,inactive']);
        $merchant->update($r->only(['name','code','phone','email','address','city','status']));
        if($r->filled('name') || $r->filled('code')) $merchant->update(['slug'=>Str::slug($merchant->name).'-'.strtolower($merchant->code)]);
        return redirect()->route('merchants.index')->with('success','Merchant diupdate');
    }
    public function destroy(Merchant $merchant){
        $this->authorizeMerchant($merchant);
        if($merchant->branches()->exists()) return back()->with('error','Masih ada cabang, hapus cabang dulu');
        $merchant->delete();
        return back()->with('success','Dihapus');
    }
    private function authorizeMerchant(Merchant $m){
        $user = auth()->user();
        $isSuper = $user->isAdmin() && $user->merchant_id === null;
        if($isSuper) return;
        if((int)$m->id !== (int)$user->merchant_id) abort(403,'Bukan merchant Anda');
    }
}

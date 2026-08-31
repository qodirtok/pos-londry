<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Branch;
class CatalogSeeder extends Seeder {
    public function run(): void {
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
        $mid = \App\Models\Merchant::first()?->id;
        foreach($cats as $c) Category::firstOrCreate(['code'=>$c['code'],'merchant_id'=>$mid], $c+['status'=>'active','merchant_id'=>$mid]);
        $catLaundry = Category::where('code','LNDRY')->first();
        $catCK = Category::where('code','CKERING')->first();
        $catCS = Category::where('code','CSET')->first();
        $catSet = Category::where('code','SETRIKA')->first();
        $catExp = Category::where('code','EXPRESS')->first();
        $catParfum = Category::where('code','PARFUM')->first();
        $catPack = Category::where('code','PACK')->first();
        $products = [
            ['sku'=>'SRV-000001','name'=>'Cuci Kering Reguler','category_id'=>$catCK->id,'type'=>'service','price'=>7000,'cost'=>3000,'unit'=>'kg'],
            ['sku'=>'SRV-000002','name'=>'Cuci Setrika Reguler','category_id'=>$catCS->id,'type'=>'service','price'=>8000,'cost'=>3500,'unit'=>'kg'],
            ['sku'=>'SRV-000003','name'=>'Setrika Saja','category_id'=>$catSet->id,'type'=>'service','price'=>6000,'cost'=>2500,'unit'=>'kg'],
            ['sku'=>'SRV-000004','name'=>'Cuci Kering Express','category_id'=>$catExp->id,'type'=>'service','price'=>12000,'cost'=>5000,'unit'=>'kg'],
            ['sku'=>'SRV-000005','name'=>'Cuci Satuan Kemeja','category_id'=>$catLaundry->id,'type'=>'service','price'=>8000,'cost'=>3000,'unit'=>'pcs'],
            ['sku'=>'PRD-000001','name'=>'Parfum Laundry 100ml','category_id'=>$catParfum->id,'type'=>'product','price'=>25000,'cost'=>15000,'unit'=>'pcs','barcode'=>'899000000001'],
            ['sku'=>'PRD-000002','name'=>'Parfum Laundry 250ml','category_id'=>$catParfum->id,'type'=>'product','price'=>50000,'cost'=>30000,'unit'=>'pcs','barcode'=>'899000000002'],
            ['sku'=>'PRD-000003','name'=>'Plastik Packaging','category_id'=>$catPack->id,'type'=>'product','price'=>5000,'cost'=>2000,'unit'=>'pcs','barcode'=>'899000000003'],
            ['sku'=>'PRD-000004','name'=>'Detergent Premium 1L','category_id'=>$catPack->id,'type'=>'product','price'=>35000,'cost'=>20000,'unit'=>'pcs','barcode'=>'899000000004'],
        ];
        foreach($products as $p){
            $prod = Product::firstOrCreate(['sku'=>$p['sku']], $p+['status'=>'active','description'=>null,'merchant_id'=>$mid]);
            if($p['type']=='product'){
                foreach(Branch::all() as $br){
                    ProductStock::firstOrCreate(['product_id'=>$prod->id,'branch_id'=>$br->id], ['quantity'=> $br->code==='SBY'?50:100,'minimum_stock'=>10]);
                }
            }
        }
    }
}

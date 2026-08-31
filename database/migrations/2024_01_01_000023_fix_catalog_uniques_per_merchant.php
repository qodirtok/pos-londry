<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        // SQLite: dropUnique needs index name; Laravel sqlite driver will recreate table.
        // categories: code unique -> (code, merchant_id) unique
        try { Schema::table('categories', function(Blueprint $t){ $t->dropUnique('categories_code_unique'); }); } catch(Throwable $e){}
        try { Schema::table('categories', function(Blueprint $t){ $t->unique(['code','merchant_id'], 'categories_code_merchant_unique'); }); } catch(Throwable $e){}

        // laundry_item_types: code unique -> (code, merchant_id) unique
        try { Schema::table('laundry_item_types', function(Blueprint $t){ $t->dropUnique('laundry_item_types_code_unique'); }); } catch(Throwable $e){}
        try { Schema::table('laundry_item_types', function(Blueprint $t){ $t->unique(['code','merchant_id'], 'laundry_types_code_merchant_unique'); }); } catch(Throwable $e){}

        // settings: (branch_id, key) unique -> (branch_id, merchant_id, key) unique
        try { Schema::table('settings', function(Blueprint $t){ $t->dropUnique('settings_branch_id_key_unique'); }); } catch(Throwable $e){}
        // existing sqlite index may be named differently; try dropping by columns
        try {
            $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='settings'");
            foreach($indexes as $idx){
                if(str_contains($idx->name, 'branch_id') && str_contains($idx->name, 'key')) {
                    try { Schema::table('settings', function(Blueprint $t) use ($idx){ $t->dropUnique($idx->name); }); } catch(Throwable $e){}
                }
            }
        } catch(Throwable $e){}
        try { Schema::table('settings', function(Blueprint $t){ $t->unique(['branch_id','merchant_id','key'], 'settings_branch_merchant_key_unique'); }); } catch(Throwable $e){}

        // backfill merchant 2 catalog if empty (after unique fix)
        $m2 = \App\Models\Merchant::where('code','TOKO-002')->first();
        if($m2 && \App\Models\Category::where('merchant_id',$m2->id)->count()===0){
            $cats = [
                ['name'=>'Laundry','code'=>'LNDRY'],['name'=>'Cuci Kering','code'=>'CKERING'],['name'=>'Cuci Setrika','code'=>'CSET'],
                ['name'=>'Setrika','code'=>'SETRIKA'],['name'=>'Express','code'=>'EXPRESS'],['name'=>'Minuman','code'=>'MINUMAN'],
                ['name'=>'Parfum','code'=>'PARFUM'],['name'=>'Packaging','code'=>'PACK'],
            ];
            foreach($cats as $c) \App\Models\Category::create($c+['status'=>'active','merchant_id'=>$m2->id,'description'=>null]);
            foreach(\App\Models\LaundryItemType::defaults() as $d) \App\Models\LaundryItemType::create($d+['merchant_id'=>$m2->id,'branch_id'=>null]);
        }
        if($m2 && \App\Models\Product::where('merchant_id',$m2->id)->count()===0){
            $catCK = \App\Models\Category::where('code','CKERING')->where('merchant_id',$m2->id)->first();
            $catCS = \App\Models\Category::where('code','CSET')->where('merchant_id',$m2->id)->first();
            $catSet = \App\Models\Category::where('code','SETRIKA')->where('merchant_id',$m2->id)->first();
            $catExp = \App\Models\Category::where('code','EXPRESS')->where('merchant_id',$m2->id)->first();
            $catLaundry = \App\Models\Category::where('code','LNDRY')->where('merchant_id',$m2->id)->first();
            $catParfum = \App\Models\Category::where('code','PARFUM')->where('merchant_id',$m2->id)->first();
            $catPack = \App\Models\Category::where('code','PACK')->where('merchant_id',$m2->id)->first();
            $list=[
                ['sku'=>'SRV-2-000001','name'=>'Cuci Kering Reguler','category_id'=>$catCK->id,'type'=>'service','price'=>7000],
                ['sku'=>'SRV-2-000002','name'=>'Cuci Setrika Reguler','category_id'=>$catCS->id,'type'=>'service','price'=>8000],
                ['sku'=>'SRV-2-000003','name'=>'Setrika Saja','category_id'=>$catSet->id,'type'=>'service','price'=>6000],
                ['sku'=>'SRV-2-000004','name'=>'Cuci Kering Express','category_id'=>$catExp->id,'type'=>'service','price'=>12000],
                ['sku'=>'SRV-2-000005','name'=>'Cuci Satuan Kemeja','category_id'=>$catLaundry->id,'type'=>'service','price'=>8000],
                ['sku'=>'PRD-2-000001','name'=>'Parfum Laundry 100ml','category_id'=>$catParfum->id,'type'=>'product','price'=>25000],
                ['sku'=>'PRD-2-000002','name'=>'Parfum Laundry 250ml','category_id'=>$catParfum->id,'type'=>'product','price'=>50000],
                ['sku'=>'PRD-2-000003','name'=>'Plastik Packaging','category_id'=>$catPack->id,'type'=>'product','price'=>5000],
                ['sku'=>'PRD-2-000004','name'=>'Detergent Premium 1L','category_id'=>$catPack->id,'type'=>'product','price'=>35000],
            ];
            foreach($list as $pr) \App\Models\Product::create($pr+['cost'=>3000,'unit'=>'kg','status'=>'active','merchant_id'=>$m2->id]);
        }
    }
    public function down(): void {
        try { Schema::table('categories', function(Blueprint $t){ $t->dropUnique('categories_code_merchant_unique'); }); } catch(Throwable $e){}
        try { Schema::table('categories', function(Blueprint $t){ $t->unique('code', 'categories_code_unique'); }); } catch(Throwable $e){}
        try { Schema::table('laundry_item_types', function(Blueprint $t){ $t->dropUnique('laundry_types_code_merchant_unique'); }); } catch(Throwable $e){}
        try { Schema::table('laundry_item_types', function(Blueprint $t){ $t->unique('code', 'laundry_item_types_code_unique'); }); } catch(Throwable $e){}
        try { Schema::table('settings', function(Blueprint $t){ $t->dropUnique('settings_branch_merchant_key_unique'); }); } catch(Throwable $e){}
        try { Schema::table('settings', function(Blueprint $t){ $t->unique(['branch_id','key'], 'settings_branch_id_key_unique'); }); } catch(Throwable $e){}
    }
};

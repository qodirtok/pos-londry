<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        foreach(['categories','products','laundry_item_types','settings'] as $tbl){
            Schema::table($tbl, function(Blueprint $t) use ($tbl){
                if(!Schema::hasColumn($tbl,'merchant_id')){
                    $t->foreignId('merchant_id')->nullable()->after('id')->constrained('merchants')->nullOnDelete();
                }
            });
        }
        // backfill from first merchant or branch merchant
        $defaultMid = \App\Models\Merchant::first()?->id;
        foreach(\App\Models\Category::whereNull('merchant_id')->get() as $c){ $c->update(['merchant_id'=>$defaultMid]); }
        foreach(\App\Models\Product::whereNull('merchant_id')->get() as $p){ $p->update(['merchant_id'=>$defaultMid]); }
        foreach(\App\Models\LaundryItemType::whereNull('merchant_id')->get() as $l){ 
            $mid = $l->branch?->merchant_id ?? $defaultMid;
            $l->update(['merchant_id'=>$mid]);
        }
        foreach(\App\Models\Setting::whereNull('merchant_id')->whereNull('branch_id')->get() as $s){
            $s->update(['merchant_id'=>$defaultMid]);
        }
        foreach(\App\Models\Setting::whereNull('merchant_id')->whereNotNull('branch_id')->get() as $s){
            $mid = $s->branch?->merchant_id ?? $defaultMid;
            $s->update(['merchant_id'=>$mid]);
        }
    }
    public function down(): void {
        foreach(['settings','laundry_item_types','products','categories'] as $tbl){
            if(Schema::hasColumn($tbl,'merchant_id')){
                Schema::table($tbl, function(Blueprint $t){ $t->dropConstrainedForeignId('merchant_id'); });
            }
        }
    }
};

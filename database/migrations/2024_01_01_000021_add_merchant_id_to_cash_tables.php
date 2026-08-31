<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        foreach(['cash_transactions','cashier_shifts'] as $tbl){
            Schema::table($tbl, function(Blueprint $t) use ($tbl){
                if(!Schema::hasColumn($tbl,'merchant_id')){
                    $t->foreignId('merchant_id')->nullable()->after('branch_id')->constrained('merchants')->nullOnDelete();
                }
            });
        }
        // backfill from branch.merchant_id
        foreach(\App\Models\CashTransaction::whereNull('merchant_id')->with('branch')->get() as $ct){
            $mid = $ct->branch?->merchant_id ?? \App\Models\Merchant::first()?->id;
            if($mid) $ct->update(['merchant_id'=>$mid]);
        }
        foreach(\App\Models\CashierShift::whereNull('merchant_id')->with('branch')->get() as $sh){
            $mid = $sh->branch?->merchant_id ?? \App\Models\Merchant::first()?->id;
            if($mid) $sh->update(['merchant_id'=>$mid]);
        }
    }
    public function down(): void {
        foreach(['cashier_shifts','cash_transactions'] as $tbl){
            if(Schema::hasColumn($tbl,'merchant_id')){
                Schema::table($tbl, function(Blueprint $t){ $t->dropConstrainedForeignId('merchant_id'); });
            }
        }
    }
};

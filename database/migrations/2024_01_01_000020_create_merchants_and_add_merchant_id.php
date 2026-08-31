<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('merchants', function(Blueprint $t){
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('slug')->unique()->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('city')->nullable();
            $t->string('status')->default('active');
            $t->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        foreach(['branches','users','customers','orders'] as $tbl){
            Schema::table($tbl, function(Blueprint $t) use ($tbl){
                if(!Schema::hasColumn($tbl,'merchant_id')){
                    $t->foreignId('merchant_id')->nullable()->after('id')->constrained('merchants')->nullOnDelete();
                }
            });
        }
    }
    public function down(): void {
        foreach(['orders','customers','users','branches'] as $tbl){
            if(Schema::hasColumn($tbl,'merchant_id')){
                Schema::table($tbl, function(Blueprint $t){ $t->dropConstrainedForeignId('merchant_id'); });
            }
        }
        Schema::dropIfExists('merchants');
    }
};

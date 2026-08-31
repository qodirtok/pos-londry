<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function(Blueprint $t){
            if(!Schema::hasColumn('users','is_demo')) $t->boolean('is_demo')->default(false)->after('status');
        });
        Schema::table('customers', function(Blueprint $t){
            if(!Schema::hasColumn('customers','is_demo')) $t->boolean('is_demo')->default(false)->after('branch_id');
        });
        Schema::table('orders', function(Blueprint $t){
            if(!Schema::hasColumn('orders','is_demo')) $t->boolean('is_demo')->default(false)->after('branch_id');
        });
        Schema::table('branches', function(Blueprint $t){
            if(!Schema::hasColumn('branches','is_demo')) $t->boolean('is_demo')->default(false)->after('status');
        });
    }
    public function down(): void {
        Schema::table('users', function(Blueprint $t){ if(Schema::hasColumn('users','is_demo')) $t->dropColumn('is_demo'); });
        Schema::table('customers', function(Blueprint $t){ if(Schema::hasColumn('customers','is_demo')) $t->dropColumn('is_demo'); });
        Schema::table('orders', function(Blueprint $t){ if(Schema::hasColumn('orders','is_demo')) $t->dropColumn('is_demo'); });
        Schema::table('branches', function(Blueprint $t){ if(Schema::hasColumn('branches','is_demo')) $t->dropColumn('is_demo'); });
    }
};

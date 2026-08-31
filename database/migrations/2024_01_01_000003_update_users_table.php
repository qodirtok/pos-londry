<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('status')->default('active')->after('phone');
            $table->foreignId('branch_id')->nullable()->after('status')->constrained('branches')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('branch_id');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['username','phone','status','branch_id','last_login_at']);
        });
    }
};

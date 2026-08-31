<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->decimal('quantity',12,3)->default(0);
            $table->decimal('old_quantity',12,3)->default(0);
            $table->decimal('new_quantity',12,3)->default(0);
            $table->decimal('difference',12,3)->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stock_movements'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->date('order_date');
            $table->date('pickup_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('subtotal',15,2)->default(0);
            $table->decimal('discount',15,2)->default(0);
            $table->string('discount_type')->nullable();
            $table->decimal('tax',15,2)->default(0);
            $table->decimal('total',15,2)->default(0);
            $table->decimal('paid_amount',15,2)->default(0);
            $table->decimal('change_amount',15,2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('received');
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->index(['branch_id','order_date']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->decimal('quantity',12,3)->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('price',15,2)->default(0);
            $table->decimal('discount',15,2)->default(0);
            $table->decimal('subtotal',15,2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->string('payment_method')->default('cash');
            $table->decimal('amount',15,2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('phone');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('whatsapp_logs'); }
};

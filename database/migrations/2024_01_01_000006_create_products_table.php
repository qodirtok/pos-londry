<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('type')->default('product');
            $table->decimal('price',15,2)->default(0);
            $table->decimal('cost',15,2)->default(0);
            $table->string('unit')->default('pcs');
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};

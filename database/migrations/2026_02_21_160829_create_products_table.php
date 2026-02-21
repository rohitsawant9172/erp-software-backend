<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('shops')->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->decimal('price', 15, 2);
            $table->integer('stock_level')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

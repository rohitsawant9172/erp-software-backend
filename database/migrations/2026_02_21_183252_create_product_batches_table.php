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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_number');
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('mrp', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2);
            $table->integer('stock_level')->default(0);
            $table->timestamps();
            
            // A shop cannot have two batches of the same product with the same batch number
            $table->unique(['tenant_id', 'product_id', 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};

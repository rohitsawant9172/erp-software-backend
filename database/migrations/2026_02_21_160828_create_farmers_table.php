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
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('shops')->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('land_size', 10, 2)->nullable(); // in acres or similar
            $table->string('crops_grown')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('current_usage', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};

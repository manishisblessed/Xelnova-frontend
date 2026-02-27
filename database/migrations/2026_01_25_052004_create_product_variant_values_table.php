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
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_option_id')->constrained()->cascadeOnDelete();
            
            // Unique constraint - each option can only be assigned once per variant
            $table->unique(['product_variant_id', 'product_variant_option_id'], 'pvv_variant_option_unique');
            
            // Indexes
            $table->index('product_variant_id');
            $table->index('product_variant_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
    }
};

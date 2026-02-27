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
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_type_id')->constrained()->cascadeOnDelete();
            $table->string('value', 100); // e.g., "red", "8gb", "xl"
            $table->string('display_value', 100); // e.g., "Midnight Red", "8 GB RAM", "Extra Large"
            $table->string('color_code', 10)->nullable(); // Hex code for color types, e.g., "#FF5733"
            $table->string('image_path', 255)->nullable(); // Optional image for this option
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Unique constraint - each value can only appear once per product per variant type
            $table->unique(['product_id', 'variant_type_id', 'value'], 'pvo_product_type_value_unique');
            
            // Indexes
            $table->index('product_id');
            $table->index('variant_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_options');
    }
};

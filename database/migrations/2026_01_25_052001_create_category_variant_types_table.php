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
        Schema::create('category_variant_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('affects_price')->default(false);
            $table->boolean('is_required')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Unique constraint - each variant type can only be assigned once per category
            $table->unique(['category_id', 'variant_type_id']);
            
            // Indexes
            $table->index('category_id');
            $table->index('variant_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_variant_types');
    }
};

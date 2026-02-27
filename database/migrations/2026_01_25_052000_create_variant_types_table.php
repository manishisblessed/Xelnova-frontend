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
        Schema::create('variant_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // e.g., "Color", "Size", "RAM", "Storage"
            $table->string('slug', 100)->unique();
            $table->enum('input_type', ['color', 'size', 'text', 'select'])->default('select');
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_types');
    }
};

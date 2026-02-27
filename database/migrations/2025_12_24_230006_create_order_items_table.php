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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            
            // Product snapshot (in case product details change)
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->json('product_options')->nullable(); // Variant info, color, size, etc.
            
            // Pricing
            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2); // Unit price
            $table->decimal('total', 10, 2); // quantity * price
            
            // Item-level status (for multi-vendor fulfillment)
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'packed',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled',
                'returned',
                'refunded'
            ])->default('pending');
            
            // Tracking
            $table->string('tracking_number')->nullable();
            $table->string('courier')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'seller_id']);
            $table->index(['seller_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

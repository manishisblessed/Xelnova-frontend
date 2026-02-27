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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Shipping address (stored as JSON snapshot)
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            
            // Pricing
            $table->decimal('subtotal', 10, 2); // Items total before discount
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2); // Final amount
            
            // Payment
            $table->string('payment_method')->nullable(); // razorpay, cod, etc.
            $table->string('payment_id')->nullable(); // Razorpay payment ID
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Order status
            $table->enum('order_status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled',
                'returned'
            ])->default('pending');
            
            // Coupon info
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            
            // Additional
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'order_status']);
            $table->index(['order_number']);
            $table->index(['payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

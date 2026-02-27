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
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sub_order_number', 25)->unique(); // e.g., XN2512250001WIAK-01
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            
            // Pricing for this seller's items
            $table->decimal('subtotal', 10, 2); // Items total for this seller
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2); // Total for this seller
            
            // Status (independent of parent order)
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
                'refund_requested',
                'refunded'
            ])->default('pending');
            
            // Shipping/Tracking
            $table->string('tracking_number')->nullable();
            $table->string('courier')->nullable();
            $table->string('shipping_label_url')->nullable();
            
            // Timestamps for status changes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Refund details
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            
            // Notes
            $table->text('seller_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'seller_id']);
            $table->index(['seller_id', 'status']);
            $table->index(['status']);
        });

        // Add sub_order_id to order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('sub_order_id')->nullable()->after('order_id')->constrained('sub_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['sub_order_id']);
            $table->dropColumn('sub_order_id');
        });
        
        Schema::dropIfExists('sub_orders');
    }
};

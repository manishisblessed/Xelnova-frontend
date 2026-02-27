<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seller_payout_requests')) {
            Schema::create('seller_payout_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number', 30)->unique();
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->decimal('requested_amount', 12, 2);
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('payment_reference')->nullable();
                $table->string('payment_method')->nullable();
                $table->text('admin_notes')->nullable();
                $table->text('seller_notes')->nullable();
                $table->timestamps();

                $table->index(['seller_id', 'status']);
                $table->index(['status', 'requested_at']);
            });
        }

        if (!Schema::hasTable('seller_payout_request_items')) {
            Schema::create('seller_payout_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payout_request_id')->constrained('seller_payout_requests')->onDelete('cascade');
                $table->foreignId('sub_order_id')->constrained('sub_orders')->onDelete('cascade');
                $table->decimal('gross_amount', 12, 2);
                $table->decimal('commission_rate', 5, 2);
                $table->decimal('commission_amount', 12, 2);
                $table->decimal('net_amount', 12, 2);
                $table->timestamps();

                $table->unique('sub_order_id');
                $table->index(['payout_request_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_payout_request_items');
    }
};

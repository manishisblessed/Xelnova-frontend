<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seller_payout_requests')) {
            return;
        }

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

    public function down(): void
    {
        if (Schema::hasTable('seller_payout_request_items')) {
            return;
        }

        Schema::dropIfExists('seller_payout_requests');
    }
};

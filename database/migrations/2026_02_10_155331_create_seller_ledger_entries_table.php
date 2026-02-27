<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sub_order_id')->nullable()->constrained('sub_orders')->nullOnDelete();
            $table->unsignedBigInteger('payout_request_id')->nullable();
            $table->enum('entry_type', [
                'sale_credit',
                'commission_debit',
                'refund_debit',
                'refund_commission_credit',
                'payout_debit',
                'manual_credit',
                'manual_debit',
            ]);
            $table->enum('direction', ['credit', 'debit']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('idempotency_key', 191)->unique();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'created_at']);
            $table->index(['entry_type', 'created_at']);
            $table->index('payout_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_ledger_entries');
    }
};

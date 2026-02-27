<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sub_order_id')->constrained('sub_orders')->onDelete('cascade');
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('base_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->enum('entry_type', ['earned', 'reversed']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['sub_order_id', 'entry_type']);
            $table->index(['seller_id', 'created_at']);
            $table->index(['entry_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_commission_entries');
    }
};

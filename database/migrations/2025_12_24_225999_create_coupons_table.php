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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('value', 10, 2); // Amount or percentage
            $table->decimal('max_discount', 10, 2)->nullable(); // Max discount for percentage coupons
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->unsignedInteger('max_uses')->nullable(); // Total uses allowed
            $table->unsignedInteger('uses_count')->default(0); // Current uses
            $table->unsignedInteger('per_user_limit')->default(1); // Uses per user
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_number')->unique();
            
            // Relationships
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('sub_order_id')->nullable()->constrained('sub_orders')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Dispute details
            $table->enum('type', [
                'product_issue',
                'delivery_issue', 
                'payment_issue',
                'seller_issue',
                'other'
            ])->default('other');
            $table->string('subject');
            $table->text('description');
            
            // Status and priority
            $table->enum('status', [
                'open',
                'under_review',
                'resolved',
                'rejected',
                'closed'
            ])->default('open');
            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'urgent'
            ])->default('medium');
            
            // Resolution
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('status');
            $table->index('priority');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

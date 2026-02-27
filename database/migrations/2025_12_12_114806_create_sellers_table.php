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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Business Information
            $table->string('business_name');
            $table->enum('business_type', ['individual', 'company', 'partnership'])->default('individual');
            $table->string('business_registration_number')->nullable();
            $table->text('business_address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('India');
            $table->string('phone');
            $table->string('email')->unique();
            
            // Tax Information
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            
            // Status & Verification
            $table->enum('status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
            $table->enum('verification_status', ['unverified', 'verified', 'rejected'])->default('unverified');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Commission
            $table->decimal('commission_rate', 5, 2)->default(10.00); // percentage
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('verification_status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};

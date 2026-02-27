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
        Schema::create('seller_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->string('brand_name');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable(); // Brand logo
            $table->string('proof_document_path')->nullable(); // Trademark/registration certificate
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('seller_id');
            $table->index('approval_status');
            $table->unique(['seller_id', 'brand_name']); // Prevent duplicate brand names per seller
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_brands');
    }
};

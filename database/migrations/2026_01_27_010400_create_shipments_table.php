<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->constrained('sub_orders')->cascadeOnDelete();
            $table->string('provider'); // e.g., 'delhivery'
            $table->string('service_type')->nullable(); // e.g., 'Surface-10kg'
            $table->string('awb_code')->nullable();
            $table->string('provider_order_id')->nullable();
            $table->string('status')->default('pending'); 
            $table->string('courier_name')->nullable();
            $table->string('label_url')->nullable();
            $table->string('manifest_url')->nullable();
            $table->json('tracking_history')->nullable();
            $table->json('meta_data')->nullable(); // Store raw provider response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

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
        Schema::create('pincode_locations', function (Blueprint $table) {
            $table->id();
            $table->string('pincode')->unique()->index();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('IN');
            $table->timestamps();
            
            // Spatial index could be added here if using spatial types, 
            // but standard indexing is fine for simple lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pincode_locations');
    }
};

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
        Schema::table('products', function (Blueprint $table) {
            // Change gst_rate from ENUM to DECIMAL(5,2)
            // Note: We might need to drop the enum constraint depending on DB, 
            // but standard 'change' usually works if types are compatible-ish or forced.
            // Since enum values are '0','5', etc, they should cast to decimal fine.
            $table->decimal('gst_rate', 5, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Reverting to ENUM is risky if data doesn't match, but we define the reverse
            $table->enum('gst_rate', ['0', '5', '12', '18', '28'])->default('18')->change();
        });
    }
};

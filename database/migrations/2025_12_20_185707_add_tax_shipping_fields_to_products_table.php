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
            // Tax Fields (Indian GST Compliance)
            $table->string('hsn_code', 20)->nullable()->after('barcode');
            $table->enum('gst_rate', ['0', '5', '12', '18', '28'])->default('18')->after('hsn_code');
            $table->boolean('is_inclusive_tax')->default(false)->after('gst_rate');
            
            // Enhanced Shipping Fields
            $table->boolean('is_fragile')->default(false)->after('height');
            $table->string('shipping_class', 50)->nullable()->after('is_fragile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'hsn_code',
                'gst_rate',
                'is_inclusive_tax',
                'is_fragile',
                'shipping_class',
            ]);
        });
    }
};

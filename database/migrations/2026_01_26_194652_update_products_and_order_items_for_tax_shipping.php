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
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('price');
            $table->boolean('is_free_shipping')->default(false)->after('shipping_cost');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('total');
            $table->decimal('tax_rate', 5, 2)->default(0.00)->after('tax_amount');
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('tax_rate');
            $table->boolean('is_free_shipping')->default(false)->after('shipping_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'is_free_shipping']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'tax_rate', 'shipping_cost', 'is_free_shipping']);
        });
    }
};

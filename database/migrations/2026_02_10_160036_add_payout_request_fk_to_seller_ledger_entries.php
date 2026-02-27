<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ledger_entries', function (Blueprint $table) {
            $table->foreign('payout_request_id')
                ->references('id')
                ->on('seller_payout_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seller_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['payout_request_id']);
        });
    }
};

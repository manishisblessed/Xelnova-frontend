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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('phone_verified_at');
            $table->enum('user_type', ['admin', 'seller', 'customer'])->default('customer')->after('avatar');
            
            $table->index(['phone']);
            $table->index(['user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['user_type']);
            $table->dropColumn(['phone', 'phone_verified_at', 'avatar', 'user_type']);
        });
    }
};

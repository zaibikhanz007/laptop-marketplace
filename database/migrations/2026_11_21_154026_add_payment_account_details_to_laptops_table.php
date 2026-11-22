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
        Schema::table('laptops', function (Blueprint $table) {
            $table->string('payment_account_title', 100)->nullable();
            $table->string('payment_account_number', 50)->nullable();
            $table->string('payment_bank_name', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->dropColumn([
                'payment_account_title',
                'payment_account_number',
                'payment_bank_name',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('laptops', 'laptops_owner_created_idx')) {
            Schema::table('laptops', fn (Blueprint $table) => $table->index(['user_id', 'created_at'], 'laptops_owner_created_idx'));
        }
        if (! Schema::hasIndex('laptops', 'laptops_created_idx')) {
            Schema::table('laptops', fn (Blueprint $table) => $table->index('created_at', 'laptops_created_idx'));
        }
        if (! Schema::hasIndex('orders', 'orders_duplicate_request_idx')) {
            Schema::table('orders', fn (Blueprint $table) => $table->index(['laptop_id', 'buyer_id', 'status'], 'orders_duplicate_request_idx'));
        }
        if (! Schema::hasIndex('orders', 'orders_created_idx')) {
            Schema::table('orders', fn (Blueprint $table) => $table->index('created_at', 'orders_created_idx'));
        }
        if (! Schema::hasIndex('complaints', 'complaints_open_limit_idx')) {
            Schema::table('complaints', fn (Blueprint $table) => $table->index(['complainant_id', 'against_user_id', 'status'], 'complaints_open_limit_idx'));
        }
        if (! Schema::hasIndex('complaints', 'complaints_created_idx')) {
            Schema::table('complaints', fn (Blueprint $table) => $table->index('created_at', 'complaints_created_idx'));
        }
        if (! Schema::hasIndex('users', 'users_created_idx')) {
            Schema::table('users', fn (Blueprint $table) => $table->index('created_at', 'users_created_idx'));
        }
    }

    public function down(): void
    {
        foreach ([
            ['laptops', 'laptops_owner_created_idx'],
            ['laptops', 'laptops_created_idx'],
            ['orders', 'orders_duplicate_request_idx'],
            ['orders', 'orders_created_idx'],
            ['complaints', 'complaints_open_limit_idx'],
            ['complaints', 'complaints_created_idx'],
            ['users', 'users_created_idx'],
        ] as [$tableName, $indexName]) {
            if (Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropIndex($indexName));
            }
        }
    }
};

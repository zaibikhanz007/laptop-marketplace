<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'users_status_created_idx');
        });

        Schema::table('laptops', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'laptops_status_created_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['buyer_id', 'created_at'], 'orders_buyer_created_idx');
            $table->index(['seller_id', 'created_at'], 'orders_seller_created_idx');
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['seller_id', 'created_at'], 'reviews_seller_created_idx');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->index(['complainant_id', 'created_at'], 'complaints_owner_created_idx');
            $table->index(['against_user_id', 'status'], 'complaints_target_status_idx');
            $table->index(['status', 'created_at'], 'complaints_status_created_idx');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'wishlists_user_created_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_owner_read_idx');
            $table->index(['notifiable_type', 'notifiable_id', 'created_at'], 'notifications_owner_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropIndex('users_status_created_idx'));
        Schema::table('laptops', function (Blueprint $table) {
            $table->dropIndex('laptops_status_created_idx');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_buyer_created_idx');
            $table->dropIndex('orders_seller_created_idx');
            $table->dropIndex('orders_status_created_idx');
        });
        Schema::table('reviews', fn (Blueprint $table) => $table->dropIndex('reviews_seller_created_idx'));
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('complaints_owner_created_idx');
            $table->dropIndex('complaints_target_status_idx');
            $table->dropIndex('complaints_status_created_idx');
        });
        Schema::table('wishlists', fn (Blueprint $table) => $table->dropIndex('wishlists_user_created_idx'));
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_owner_read_idx');
            $table->dropIndex('notifications_owner_created_idx');
        });
    }
};

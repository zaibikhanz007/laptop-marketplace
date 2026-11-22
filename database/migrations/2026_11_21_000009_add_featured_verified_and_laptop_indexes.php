<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false);
            $table->date('featured_until')->nullable();
            $table->index(['status', 'brand', 'price', 'location'], 'laptops_marketplace_search_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->dropIndex('laptops_marketplace_search_idx');
            $table->dropColumn(['is_featured', 'featured_until']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }
};

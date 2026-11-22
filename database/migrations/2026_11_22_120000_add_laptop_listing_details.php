<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            if (! Schema::hasColumn('laptops', 'title')) {
                $table->string('title')->nullable();
            }
            if (! Schema::hasColumn('laptops', 'generation')) {
                $table->string('generation')->nullable();
            }
            if (! Schema::hasColumn('laptops', 'storage_type')) {
                $table->string('storage_type', 20)->nullable();
            }
            if (! Schema::hasColumn('laptops', 'storage_capacity')) {
                $table->string('storage_capacity', 50)->nullable();
            }
            if (! Schema::hasColumn('laptops', 'graphics_card')) {
                $table->string('graphics_card')->nullable();
            }
            if (! Schema::hasColumn('laptops', 'screen_size')) {
                $table->string('screen_size', 20)->nullable();
            }
            if (! Schema::hasColumn('laptops', 'contact_number')) {
                $table->string('contact_number', 30)->nullable();
            }
        });

        // The original enum only supported the older condition labels. A string
        // keeps existing values valid while allowing the new marketplace labels.
        if (Schema::hasColumn('laptops', 'condition')) {
            Schema::table('laptops', function (Blueprint $table) {
                $table->string('condition')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            foreach (['title', 'generation', 'storage_type', 'storage_capacity', 'graphics_card', 'screen_size', 'contact_number'] as $column) {
                if (Schema::hasColumn('laptops', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

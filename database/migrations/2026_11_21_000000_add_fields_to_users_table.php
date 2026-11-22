<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->enum('status', ['pending', 'active', 'blocked'])->default('pending');
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'phone', 'city', 'address']);
        });
    }
};

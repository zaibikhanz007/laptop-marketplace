<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complainant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('against_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'warned', 'blocked', 'dismissed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('complaints'); }
};

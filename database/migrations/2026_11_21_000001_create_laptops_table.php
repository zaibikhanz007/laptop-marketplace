<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laptops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('model');
            $table->string('processor')->nullable();
            $table->string('ram')->nullable();
            $table->string('storage')->nullable();
            $table->enum('condition', ['like_new', 'good', 'fair', 'needs_repair']);
            $table->unsignedInteger('price');
            $table->string('location');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'available', 'sold', 'rejected', 'blocked'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('laptops'); }
};

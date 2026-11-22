<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laptop_images', function (Blueprint $table) {
            $table->boolean('is_cover')->default(false);
        });

        DB::table('laptop_images')
            ->select('laptop_id')
            ->distinct()
            ->get()
            ->each(function ($row) {
                $imageId = DB::table('laptop_images')
                    ->where('laptop_id', $row->laptop_id)
                    ->orderBy('id')
                    ->value('id');
                if ($imageId) {
                    DB::table('laptop_images')->where('id', $imageId)->update(['is_cover' => true]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('laptop_images', function (Blueprint $table) {
            $table->dropColumn('is_cover');
        });
    }
};

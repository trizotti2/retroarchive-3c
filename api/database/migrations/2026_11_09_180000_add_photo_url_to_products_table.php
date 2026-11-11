<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('photo_url', 2048)->nullable();
        });

        DB::table('products')
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->select('id', 'image_path')
            ->chunkById(500, function ($products): void {
                foreach ($products as $product) {
                    $path = $product->image_path;
                    $url = preg_match('~^https?://~i', $path) || str_starts_with($path, '/storage/')
                        ? $path
                        : '/storage/'.ltrim($path, '/');

                    DB::table('products')->where('id', $product->id)->update(['photo_url' => $url]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });
    }
};

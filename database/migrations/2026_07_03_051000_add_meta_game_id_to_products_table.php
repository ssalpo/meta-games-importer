<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table
                ->foreignId('meta_game_id')
                ->nullable()
                ->after('account_id')
                ->constrained('meta_games')
                ->nullOnDelete();
        });

        DB::table('meta_games')
            ->whereNotNull('product_id')
            ->orderBy('id')
            ->eachById(function (object $metaGame): void {
                DB::table('products')
                    ->where('id', $metaGame->product_id)
                    ->whereNull('meta_game_id')
                    ->update(['meta_game_id' => $metaGame->id]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('meta_game_id');
        });
    }
};

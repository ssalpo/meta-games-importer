<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table
                ->unsignedBigInteger('ggsel_offer_id')
                ->nullable()
                ->after('external_reference')
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['ggsel_offer_id']);
            $table->dropColumn('ggsel_offer_id');
        });
    }
};

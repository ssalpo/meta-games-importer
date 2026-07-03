<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_games', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id')->unique();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('title');
            $table->string('parent_title')->nullable();
            $table->string('full_title')->index();
            $table->boolean('is_addon')->default(false)->index();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_discount', 12, 2)->nullable();
            $table->boolean('discount_ended')->default(false);
            $table->decimal('rating', 4, 2)->nullable();
            $table->string('image_landscape')->nullable();
            $table->string('image_square')->nullable();
            $table->boolean('is_files_downloaded')->default(false);
            $table->timestamp('source_created_at')->nullable();
            $table->timestamp('source_updated_at')->nullable()->index();
            $table->json('raw_payload');
            $table->timestamp('last_imported_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_games');
    }
};

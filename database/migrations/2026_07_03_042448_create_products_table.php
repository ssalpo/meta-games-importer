<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('placement_category');
            $table->string('external_reference')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('title_ru');
            $table->string('title_en');
            $table->text('description_ru')->nullable();
            $table->text('description_en')->nullable();
            $table->text('instruction_ru')->nullable();
            $table->text('instruction_en')->nullable();
            $table->text('additional_info_ru')->nullable();
            $table->text('additional_info_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

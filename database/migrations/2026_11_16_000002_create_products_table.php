<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedSmallInteger('weight_grams')->default(250);
            // Perkiraan jumlah porsi per kemasan — dasar hitungan Smart Bundle.
            $table->unsignedTinyInteger('servings')->default(2);
            $table->string('cook_method', 60)->nullable();
            // Kebutuhan yang cocok: kos, keluarga, bekal, mealprep, acara.
            $table->json('tags')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

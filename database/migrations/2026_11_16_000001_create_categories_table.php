<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 80)->unique();
            $table->string('description', 160)->nullable();
            // Warna & ilustrasi kemasan yang dipakai di kartu produk.
            $table->string('tone', 20)->default('blue');
            $table->string('art', 20)->default('box');
            // Pendamping/camilan (bukan lauk utama) — porsinya dihitung setengah oleh Smart Bundle.
            $table->boolean('is_side')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name', 100);
            $table->string('phone', 20);
            $table->text('address');
            $table->string('payment_method', 20);
            $table->string('notes', 255)->nullable();
            $table->unsignedInteger('total');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Nama & harga disalin supaya riwayat pesanan tidak berubah saat produk diedit.
            $table->string('product_name', 120);
            $table->string('bundle_name', 80)->nullable();
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

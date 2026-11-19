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
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');            // integer IDR
            $table->string('unit')->default('kg');          // e.g. 'kg', 'pack'
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('image_url')->nullable();
            $table->string('emoji')->nullable();            // placeholder visual e.g. 🦀
            $table->string('gradient')->nullable();         // card background gradient
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

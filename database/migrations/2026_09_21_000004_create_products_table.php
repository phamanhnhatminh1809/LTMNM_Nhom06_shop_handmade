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
            $table->string('code', 50)->unique()->comment('e.g., SP001');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onUpdate('cascade')->onDelete('set null');
            $table->string('name', 255);
            $table->string('slug', 255)->nullable()->index();
            $table->text('description')->nullable();
            $table->text('instruction_preserve')->nullable()->comment('Hướng dẫn bảo quản');
            $table->string('image', 255)->nullable();
            $table->json('gallery')->nullable();
            $table->decimal('price', 15, 2)->default(0.00);
            $table->decimal('old_price', 15, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('sold')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

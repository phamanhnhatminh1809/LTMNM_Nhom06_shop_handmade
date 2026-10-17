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
        // 1. Bảng phân quyền (roles)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->timestamps();
        });

        // 2. Bảng người dùng (users) - đã gộp cột address
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onUpdate('cascade')->onDelete('restrict');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('full_name', 100);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->comment('1: Active, 0: Locked');
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Bảng mã OTP khôi phục mật khẩu (password_reset_otps)
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // 4. Bảng danh mục sản phẩm (categories)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 150)->unique();
            $table->string('icon', 50)->nullable()->comment('Emoji or icon string');
            $table->timestamps();
        });

        // 5. Bảng sản phẩm (products)
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

        // 6. Bảng đơn hàng (orders)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('e.g., ORD-1002');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->string('shipping_name', 100);
            $table->string('shipping_phone', 20);
            $table->text('shipping_address');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->enum('status', ['pending', 'shipping', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        // 7. Bảng chi tiết đơn hàng (order_items)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onUpdate('cascade')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        // 8. Bảng đánh giá sản phẩm (reviews)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('1 to 5 stars');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique('order_item_id');
        });

        // 9. Bảng giỏ hàng (carts)
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->enum('status', ['active', 'converted'])->default('active');
            $table->unsignedInteger('total_quantity')->default(0);
            $table->timestamps();
        });

        // 10. Bảng chi tiết giỏ hàng (cart_details)
        Schema::create('cart_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_code', 50);
            $table->string('name', 255);
            $table->string('image_url')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_details');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('password_reset_otps');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung chỉ mục (index) cho các cột thường xuyên lọc, sắp xếp và tổng hợp.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_at_idx');
            $table->index('created_at', 'orders_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_featured', 'sold'], 'products_featured_sold_idx');
            $table->index(['stock_quantity', 'sold'], 'products_stock_sold_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role_id', 'is_active'], 'users_role_active_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['product_id', 'rating'], 'reviews_product_rating_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'carts_user_status_idx');
            $table->index(['session_id', 'status'], 'carts_session_status_idx');
        });
    }

    /**
     * Gỡ bỏ các chỉ mục khi rollback.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_at_idx');
            $table->dropIndex('orders_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_featured_sold_idx');
            $table->dropIndex('products_stock_sold_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_active_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_product_rating_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_user_status_idx');
            $table->dropIndex('carts_session_status_idx');
        });
    }
};

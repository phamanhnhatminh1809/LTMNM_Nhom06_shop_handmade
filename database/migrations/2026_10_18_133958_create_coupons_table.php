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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã giảm giá (VD: SALE10)
            $table->enum('type', ['fixed', 'percent']); // 'fixed' = giảm tiền mặt, 'percent' = giảm %
            $table->decimal('value', 15, 2); // Số tiền hoặc số %
            $table->decimal('min_order_amount', 15, 2)->default(0); // Đơn tối thiểu
            $table->integer('max_uses')->default(100); // Số lượt dùng tối đa
            $table->integer('used_count')->default(0); // Đã dùng
            $table->dateTime('expires_at')->nullable(); // Ngày hết hạn
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

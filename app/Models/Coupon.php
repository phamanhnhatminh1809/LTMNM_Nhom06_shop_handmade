<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 
        'max_uses', 'used_count', 'expires_at', 'is_active'
    ];

    // Hàm kiểm tra mã giảm giá có hợp lệ không
    public function isValid($orderTotal)
    {
        if (!$this->is_active) return 'Mã giảm giá đã bị vô hiệu hóa.';
        if ($this->used_count >= $this->max_uses) return 'Mã giảm giá đã hết lượt sử dụng.';
        if ($this->expires_at && now()->gt($this->expires_at)) return 'Mã giảm giá đã hết hạn.';
        if ($orderTotal < $this->min_order_amount) {
            return 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($this->min_order_amount, 0, ',', '.') . 'đ.';
        }
        return true; // Hợp lệ
    }

    // Tính số tiền được giảm
    public function calculateDiscount($orderTotal)
    {
        if ($this->type === 'percent') {
            return ($orderTotal * $this->value) / 100;
        }
        return $this->value; // Giảm tiền mặt
    }
}
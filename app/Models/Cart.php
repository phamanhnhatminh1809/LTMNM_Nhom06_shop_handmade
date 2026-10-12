<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'total_quantity',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(CartDetail::class);
    }

    /**
     * Tính tổng tiền tất cả sản phẩm trong giỏ hàng.
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->details->sum(function (CartDetail $detail) {
            return $detail->quantity * $detail->unit_price;
        });
    }

    /**
     * Cập nhật lại tổng số lượng mặt hàng trong giỏ.
     */
    public function recalculateTotals(): void
    {
        $this->total_quantity = (int) $this->details()->sum('quantity');
        $this->save();
    }

    /**
     * Lấy hoặc tạo mới giỏ hàng hoạt động (active) cho người dùng hiện tại hoặc phiên làm việc.
     */
    public static function getActiveCart(): self
    {
        $sessionId = Session::getId();

        if (Auth::check()) {
            $userId = Auth::id();

            // Tìm giỏ hàng active của user
            $cart = self::where('user_id', $userId)
                ->where('status', 'active')
                ->latest()
                ->first();

            // Nếu user có giỏ hàng vãng lai từ session trước khi đăng nhập, gộp hoặc liên kết
            $sessionCart = self::where('session_id', $sessionId)
                ->where('status', 'active')
                ->whereNull('user_id')
                ->first();

            if ($sessionCart) {
                if (!$cart) {
                    $sessionCart->user_id = $userId;
                    $sessionCart->save();
                    return $sessionCart;
                } else {
                    // Gộp sản phẩm từ sessionCart vào user cart
                    foreach ($sessionCart->details as $item) {
                        $existing = $cart->details()->where('product_id', $item->product_id)->first();
                        if ($existing) {
                            $existing->quantity += $item->quantity;
                            $existing->save();
                        } else {
                            $item->cart_id = $cart->id;
                            $item->save();
                        }
                    }
                    $sessionCart->delete();
                    $cart->recalculateTotals();
                    return $cart;
                }
            }

            if (!$cart) {
                $cart = self::create([
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'status' => 'active',
                    'total_quantity' => 0,
                ]);
            }

            return $cart;
        }

        // Người dùng chưa đăng nhập (khách vãng lai qua session_id)
        $cart = self::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            $cart = self::create([
                'session_id' => $sessionId,
                'status' => 'active',
                'total_quantity' => 0,
            ]);
        }

        return $cart;
    }
}

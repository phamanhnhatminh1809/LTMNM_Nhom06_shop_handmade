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
     * Gộp giỏ hàng khách vãng lai (theo session_id cũ) vào giỏ hàng active của user sau đăng nhập/đăng ký.
     */
    public static function mergeSessionCart(string $sessionId, int $userId): ?self
    {
        if ($sessionId === '') {
            return self::where('user_id', $userId)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        $cart = self::where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->first();

        $sessionCart = self::with('details.product')
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->whereNull('user_id')
            ->first();

        if (!$sessionCart) {
            return $cart;
        }

        if (!$cart) {
            $sessionCart->user_id = $userId;
            $sessionCart->session_id = Session::getId() ?: $sessionId;
            $sessionCart->save();
            return $sessionCart;
        }

        foreach ($sessionCart->details as $item) {
            $stock = $item->product?->stock_quantity;
            if ($stock !== null && $stock <= 0) {
                $item->delete();
                continue;
            }

            $existing = $cart->details()->where('product_id', $item->product_id)->first();
            if ($existing) {
                $newQty = $existing->quantity + $item->quantity;
                if ($stock !== null) {
                    $newQty = min($newQty, $stock);
                }
                $existing->quantity = max(1, $newQty);
                $existing->save();
                $item->delete();
            } else {
                if ($stock !== null && $item->quantity > $stock) {
                    $item->quantity = $stock;
                }
                $item->cart_id = $cart->id;
                $item->save();
            }
        }

        $sessionCart->delete();
        $cart->recalculateTotals();

        return $cart;
    }

    /**
     * Lấy hoặc tạo mới giỏ hàng hoạt động (active) cho người dùng hiện tại hoặc phiên làm việc.
     */
    public static function getActiveCart(bool $createIfNotExist = true): ?self
    {
        $sessionId = Session::getId();

        if (Auth::check()) {
            $userId = Auth::id();
            $cart = self::mergeSessionCart($sessionId, $userId);

            if (!$cart && $createIfNotExist) {
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

        if (!$cart && $createIfNotExist) {
            $cart = self::create([
                'session_id' => $sessionId,
                'status' => 'active',
                'total_quantity' => 0,
            ]);
        }

        return $cart;
    }
}

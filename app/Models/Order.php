<?php

namespace App\Models;

use App\Services\AdminNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'code',
        'user_id',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'total_amount',
        'shipping_fee',
        'discount_amount',
        'payment_method',
        'payment_status',
        'payment_gateway',
        'payment_reference',
        'payment_transaction_id',
        'checkout_url',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'      => 'float',
            'shipping_fee'      => 'float',
            'discount_amount'   => 'float',
            'payment_reference' => 'integer',
            'paid_at'           => 'datetime',
        ];
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Nhãn hiển thị phương thức thanh toán thân thiện cho khách hàng & admin.
     */
    public function paymentMethodLabel(): string
    {
        return match (true) {
            $this->payment_method === 'qr' && $this->payment_gateway === 'payos' => 'Thanh toán QR (PayOS)',
            $this->payment_method === 'qr' => 'Chuyển khoản ngân hàng (QR)',
            default => 'Thanh toán khi nhận hàng (COD)',
        };
    }

    /**
     * Nhãn hiển thị trạng thái thanh toán rõ ràng cho khách hàng & admin.
     */
    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_PAID      => 'Đã thanh toán',
            self::PAYMENT_STATUS_CANCELLED => 'Đã hủy thanh toán',
            self::PAYMENT_STATUS_FAILED    => 'Thanh toán thất bại',
            default                        => $this->payment_method === 'qr'
                ? 'Chờ thanh toán QR'
                : 'Thanh toán khi nhận hàng',
        };
    }

    /**
     * Hủy đơn đang chờ xử lý, hoàn trả tồn kho và đồng bộ cảnh báo kho (an toàn đồng thời & idempotent).
     */
    public function cancelAndRestoreInventory(?string $paymentStatus = null): bool
    {
        $cancelled = DB::transaction(function () use ($paymentStatus): bool {
            $lockedOrder = self::lockForUpdate()->with('items')->find($this->id);
            if (!$lockedOrder || $lockedOrder->status !== 'pending') {
                return false;
            }

            $updates = ['status' => 'cancelled'];
            if ($paymentStatus !== null && !$lockedOrder->isPaid()) {
                $updates['payment_status'] = $paymentStatus;
            }

            $lockedOrder->update($updates);

            foreach ($lockedOrder->items as $item) {
                if (!$item->product_id) {
                    continue;
                }
                $product = Product::lockForUpdate()->find($item->product_id);
                if (!$product) {
                    continue;
                }
                $product->increment('stock_quantity', $item->quantity);
                if ($product->sold >= $item->quantity) {
                    $product->decrement('sold', $item->quantity);
                }
            }

            return true;
        });

        if (!$cancelled) {
            return false;
        }

        $this->refresh();
        $this->loadMissing('items.product');

        $notificationService = app(AdminNotificationService::class);
        foreach ($this->items as $item) {
            if ($item->product) {
                $notificationService->syncProductStockAlert($item->product->fresh());
            }
        }

        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        $clearDashboardCache = static fn () => Cache::forget('admin_dashboard_stats');

        static::saved($clearDashboardCache);
        static::deleted($clearDashboardCache);
    }
}

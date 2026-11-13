<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\AdminNotificationRead;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;

class AdminNotificationService
{
    public const DEFAULT_MIN_STOCK_THRESHOLD = 5;

    /**
     * Danh sách loại thông báo được phép xem theo vai trò.
     */
    public function allowedTypesForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $roleName = $user->resolveRoleName();

        return match ($roleName) {
            Role::ADMIN => [
                AdminNotification::TYPE_NEW_ORDER,
                AdminNotification::TYPE_LOW_STOCK,
            ],
            Role::SALES_STAFF => [
                AdminNotification::TYPE_NEW_ORDER,
            ],
            Role::WAREHOUSE_STAFF => [
                AdminNotification::TYPE_LOW_STOCK,
            ],
            default => [],
        };
    }

    public function canUserViewNotification(?User $user, AdminNotification $notification): bool
    {
        return in_array($notification->type, $this->allowedTypesForUser($user), true);
    }

    /**
     * Phát thông báo khi khách hàng đặt đơn hàng mới.
     */
    public function notifyNewOrder(Order $order): AdminNotification
    {
        $customerName = trim((string) ($order->shipping_name ?: ($order->user?->full_name ?? 'Khách hàng')));
        $formattedTotal = number_format((float) $order->total_amount, 0, ',', '.') . 'đ';
        $paymentLabel = $order->payment_method === 'qr' ? 'Chuyển khoản QR' : 'COD';

        return AdminNotification::create([
            'type'           => AdminNotification::TYPE_NEW_ORDER,
            'severity'       => AdminNotification::SEVERITY_INFO,
            'title'          => 'Đơn hàng mới #' . $order->code,
            'message'        => "{$customerName} vừa đặt đơn trị giá {$formattedTotal} ({$paymentLabel}).",
            'action_url'     => route('admin.orders.show', $order->id, false),
            'reference_type' => 'order',
            'reference_id'   => $order->id,
        ]);
    }

    /**
     * Đồng bộ cảnh báo tồn kho cho sản phẩm dựa trên ngưỡng min_stock_threshold riêng.
     */
    public function syncProductStockAlert(Product $product): ?AdminNotification
    {
        $threshold = $product->effectiveMinStockThreshold();
        $stock = (int) $product->stock_quantity;

        $openAlertQuery = AdminNotification::query()
            ->where('type', AdminNotification::TYPE_LOW_STOCK)
            ->where('reference_type', 'product')
            ->where('reference_id', $product->id)
            ->whereNull('resolved_at');

        if ($stock > $threshold) {
            $openAlertQuery->update(['resolved_at' => now()]);
            return null;
        }

        $severity = $stock <= 0
            ? AdminNotification::SEVERITY_DANGER
            : AdminNotification::SEVERITY_WARNING;

        $title = $stock <= 0
            ? 'Hết hàng: ' . $product->name
            : 'Sắp hết hàng: ' . $product->name;

        $message = $stock <= 0
            ? "Mã {$product->code} đã hết tồn kho (0 / ngưỡng tối thiểu {$threshold})."
            : "Mã {$product->code} chỉ còn {$stock} sản phẩm (ngưỡng tối thiểu {$threshold}).";

        $actionUrl = route('admin.products.edit', $product->id, false);
        $existingOpen = (clone $openAlertQuery)->latest('id')->first();

        if ($existingOpen) {
            if ($existingOpen->severity === AdminNotification::SEVERITY_WARNING && $severity === AdminNotification::SEVERITY_DANGER) {
                $openAlertQuery->update(['resolved_at' => now()]);
            } else {
                $existingOpen->update([
                    'severity'   => $severity,
                    'title'      => $title,
                    'message'    => $message,
                    'action_url' => $actionUrl,
                ]);

                return $existingOpen;
            }
        }

        return AdminNotification::create([
            'type'           => AdminNotification::TYPE_LOW_STOCK,
            'severity'       => $severity,
            'title'          => $title,
            'message'        => $message,
            'action_url'     => $actionUrl,
            'reference_type' => 'product',
            'reference_id'   => $product->id,
        ]);
    }

    /**
     * Đóng cảnh báo tồn kho khi sản phẩm bị xóa.
     */
    public function resolveAlertsForDeletedProduct(int $productId): void
    {
        AdminNotification::query()
            ->where('type', AdminNotification::TYPE_LOW_STOCK)
            ->where('reference_type', 'product')
            ->where('reference_id', $productId)
            ->whereNull('resolved_at')
            ->update(['resolved_at' => now()]);
    }

    public function unreadCountForUser(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        $allowedTypes = $this->allowedTypesForUser($user);
        if (empty($allowedTypes)) {
            return 0;
        }

        return AdminNotification::query()
            ->forTypes($allowedTypes)
            ->unreadForUser($user->id)
            ->count();
    }

    public function summaryForUser(User $user, int $limit = 15, string $filter = 'all'): array
    {
        $allowedTypes = $this->allowedTypesForUser($user);
        if (empty($allowedTypes)) {
            return [
                'unread_count' => 0,
                'items'        => [],
            ];
        }

        $baseQuery = AdminNotification::query()->forTypes($allowedTypes);

        $unreadCount = (clone $baseQuery)->unreadForUser($user->id)->count();

        $listQuery = (clone $baseQuery)
            ->withExists(['reads as is_read' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderByDesc('id')
            ->limit(max(1, min($limit, 50)));

        if ($filter === 'unread') {
            $listQuery->unreadForUser($user->id);
        }

        $items = $listQuery->get()->map(function (AdminNotification $item) {
            return [
                'id'          => $item->id,
                'type'        => $item->type,
                'severity'    => $item->severity,
                'title'       => $item->title,
                'message'     => $item->message,
                'action_url'  => $item->action_url,
                'is_read'     => (bool) $item->is_read,
                'is_resolved' => $item->resolved_at !== null,
                'time_ago'    => $item->created_at ? $item->created_at->diffForHumans() : '',
            ];
        })->values()->all();

        return [
            'unread_count' => $unreadCount,
            'items'        => $items,
        ];
    }

    public function markAsRead(User $user, AdminNotification $notification): int
    {
        AdminNotificationRead::firstOrCreate(
            [
                'notification_id' => $notification->id,
                'user_id'         => $user->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        return $this->unreadCountForUser($user);
    }

    public function markAllAsRead(User $user): int
    {
        $allowedTypes = $this->allowedTypesForUser($user);
        if (empty($allowedTypes)) {
            return 0;
        }

        $unreadIds = AdminNotification::query()
            ->forTypes($allowedTypes)
            ->unreadForUser($user->id)
            ->pluck('id');

        if ($unreadIds->isEmpty()) {
            return 0;
        }

        $now = now();
        $rows = $unreadIds->map(fn ($id) => [
            'notification_id' => $id,
            'user_id'         => $user->id,
            'read_at'         => $now,
        ])->all();

        AdminNotificationRead::upsert($rows, ['notification_id', 'user_id'], ['read_at']);

        return 0;
    }
}

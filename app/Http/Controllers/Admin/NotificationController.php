<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private readonly AdminNotificationService $notificationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $filter = $request->input('filter') === 'unread' ? 'unread' : 'all';
        $limit = max(1, min((int) $request->input('limit', 15), 50));

        $summary = $this->notificationService->summaryForUser($user, $limit, $filter);

        return response()->json([
            'unread_count' => $summary['unread_count'],
            'items'        => $summary['items'],
            'allowed_types' => $this->notificationService->allowedTypesForUser($user),
        ]);
    }

    public function markAsRead(AdminNotification $notification): JsonResponse
    {
        $user = Auth::user();

        if (!$this->notificationService->canUserViewNotification($user, $notification)) {
            return response()->json([
                'message' => 'Bạn không có quyền thao tác trên thông báo này.',
            ], 403);
        }

        $unreadCount = $this->notificationService->markAsRead($user, $notification);

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
        ]);
    }
}

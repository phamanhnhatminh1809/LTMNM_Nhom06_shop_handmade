<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Kiểm tra người dùng đã đăng nhập, đang hoạt động và có quyền phù hợp.
     *
     * Cách dùng:
     * - middleware('admin')                          -> Cho phép mọi tài khoản quản trị/nhân viên (admin, warehouse_staff, sales_staff)
     * - middleware('admin:admin')                    -> Chỉ cho phép Admin toàn quyền
     * - middleware('admin:admin,warehouse_staff')    -> Chỉ cho phép Admin & Nhân viên kho (Sản phẩm, Loại sản phẩm)
     * - middleware('admin:admin,sales_staff')        -> Chỉ cho phép Admin & Nhân viên bán hàng (Đơn hàng, Đánh giá)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Nếu tài khoản bị khóa trong lúc đang đăng nhập -> đăng xuất ngay
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'account' => 'Tài khoản của bạn đã bị khóa.',
            ]);
        }

        // Phải là Admin hoặc Nhân viên mới được vào khu vực /admin
        if (!$user->isStaffOrAdmin()) {
            abort(403, 'Bạn không có quyền truy cập vào khu vực quản trị.');
        }

        // Nếu route yêu cầu danh sách role cụ thể
        if (!empty($roles) && !$user->hasAnyRole($roles)) {
            abort(403, 'Tài khoản của bạn (' . ($user->role->label ?? $user->role->name) . ') không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}

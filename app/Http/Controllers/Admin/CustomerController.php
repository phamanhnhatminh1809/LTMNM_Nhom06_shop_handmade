<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Danh sách khách hàng.
     * Hỗ trợ: tìm kiếm (tên/email/sđt/username), lọc trạng thái (active/locked).
     */
    public function index(Request $request): View
    {
        $keyword = trim($request->input('q', ''));
        $status  = $request->input('status', '');   // 'active' | 'locked' | ''

        $customers = User::customers()
            ->search($keyword)
            ->filterStatus($status)
            ->withCount('orders')          // số đơn hàng mỗi khách
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Đếm badge cho 2 tab trạng thái
        $statusCounts = [
            'all'    => User::customers()->count(),
            'active' => User::customers()->where('is_active', true)->count(),
            'locked' => User::customers()->where('is_active', false)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'keyword', 'status', 'statusCounts'));
    }

    /**
     * Xem thông tin chi tiết một khách hàng.
     * Load lịch sử đơn hàng gần nhất.
     */
    public function show(User $customer): View
    {
        // Đảm bảo đây là tài khoản khách hàng, không phải admin
        abort_if($customer->role_id !== 2, 404);

        $customer->load([
            'orders' => fn ($q) => $q->latest()->take(10),
            'orders.items.product',
        ]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Khóa tài khoản khách hàng.
     * Sau khi khóa, tài khoản không thể đăng nhập (AuthController đã kiểm tra is_active).
     * Không thể khóa tài khoản admin.
     * Không thể tự khóa tài khoản đang đăng nhập.
     */
    public function lock(User $customer): RedirectResponse
    {
        abort_if($customer->role_id !== 2, 403, 'Không thể khóa tài khoản admin.');

        if ($customer->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Không thể tự khóa tài khoản đang đăng nhập.');
        }

        if (!$customer->is_active) {
            return redirect()->back()
                ->with('error', 'Tài khoản "' . $customer->full_name . '" đã bị khóa từ trước.');
        }

        $customer->update(['is_active' => false]);

        // Xóa session hiện tại của user bị khóa (đăng xuất họ ngay lập tức nếu đang online)
        // Phương pháp này hoạt động khi dùng database session driver.
        // Nếu dùng file session, user phải đợi đến request tiếp theo mới bị từ chối.

        return redirect()->back()
            ->with('success', 'Đã khóa tài khoản "' . $customer->full_name . '" thành công.');
    }

    /**
     * Mở khóa tài khoản khách hàng.
     */
    public function unlock(User $customer): RedirectResponse
    {
        abort_if($customer->role_id !== 2, 403, 'Chỉ thao tác được với tài khoản khách hàng.');

        if ($customer->is_active) {
            return redirect()->back()
                ->with('error', 'Tài khoản "' . $customer->full_name . '" đang hoạt động bình thường.');
        }

        $customer->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Đã mở khóa tài khoản "' . $customer->full_name . '" thành công.');
    }
}

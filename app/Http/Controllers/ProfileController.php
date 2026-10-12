<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang quản lý thông tin cá nhân và đổi mật khẩu.
     */
    public function show(): View
    {
        $user = Auth::user();

        // Lấy 5 đơn hàng gần nhất của người dùng để hiển thị tổng quan
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->latest()
            ->take(5)
            ->get();

        return view('profile.index', compact('user', 'recentOrders'));
    }

    /**
     * Cập nhật Họ tên, Email, Số điện thoại, Địa chỉ.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email'     => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-]{8,20}$/'],
            'address'   => 'nullable|string|max:500',
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.max'      => 'Họ và tên tối đa 100 ký tự.',
            'email.required'     => 'Địa chỉ email không được để trống.',
            'email.email'        => 'Địa chỉ email không đúng định dạng.',
            'email.unique'       => 'Địa chỉ email này đã được sử dụng bởi tài khoản khác.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng.',
            'phone.max'          => 'Số điện thoại tối đa 20 ký tự.',
            'address.max'        => 'Địa chỉ tối đa 500 ký tự.',
        ]);

        $user->update([
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'address'   => $validated['address'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin cá nhân thành công!',
                'user'    => $user->fresh(),
            ]);
        }

        return redirect()->route('profile.show')->with('profile_success', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Đổi mật khẩu bằng cách nhập mật khẩu hiện tại và mật khẩu mới.
     */
    public function changePassword(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required'         => 'Vui lòng nhập mật khẩu mới.',
            'password.min'              => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'password.confirmed'        => 'Xác nhận mật khẩu mới không khớp.',
            'password.different'        => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.',
        ]);

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->input('current_password'), $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => ['current_password' => ['Mật khẩu hiện tại không chính xác!']],
                ], 422);
            }

            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không chính xác!',
            ]);
        }

        // Lưu mật khẩu mới
        $user->password = Hash::make($request->input('password'));
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công!',
            ]);
        }

        return redirect()->route('profile.show')->with('password_success', 'Đổi mật khẩu thành công!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang hồ sơ cá nhân của Quản trị viên / Nhân viên.
     */
    public function edit(): View
    {
        $user = Auth::user();

        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin cơ bản: Họ tên, Email, Số điện thoại.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email'     => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-]{8,20}$/'],
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.max'      => 'Họ và tên tối đa 100 ký tự.',
            'email.required'     => 'Địa chỉ email không được để trống.',
            'email.email'        => 'Địa chỉ email không đúng định dạng.',
            'email.unique'       => 'Địa chỉ email này đã được sử dụng bởi tài khoản khác.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng.',
            'phone.max'          => 'Số điện thoại tối đa 20 ký tự.',
        ]);

        $user->update([
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
        ]);

        return redirect()->route('admin.profile')->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Đổi mật khẩu tài khoản nội bộ.
     */
    public function changePassword(Request $request): RedirectResponse
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

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không chính xác!',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('admin.profile')->with('password_success', 'Đổi mật khẩu thành công!');
    }
}

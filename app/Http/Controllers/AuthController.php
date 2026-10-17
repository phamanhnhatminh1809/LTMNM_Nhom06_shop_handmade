<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByUserRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = $request->input('account');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $fieldType = filter_var($account, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $account, 'password' => $password, 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isStaffOrAdmin()) {
                return redirect()->intended($this->getDefaultAdminRouteForUser($user));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'account' => 'Tài khoản hoặc mật khẩu không chính xác hoặc tài khoản đã bị khóa.',
        ])->onlyInput('account');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.unique' => 'Tên tài khoản này đã được sử dụng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 8 ký tự.',
        ]);

        $user = User::create([
            'role_id' => 2, // Customer
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Điều hướng người dùng về trang mặc định theo quyền.
     */
    private function redirectByUserRole(User $user): RedirectResponse
    {
        if ($user->isStaffOrAdmin()) {
            return redirect($this->getDefaultAdminRouteForUser($user));
        }

        return redirect()->route('home');
    }

    /**
     * Lấy URL trang quản trị mặc định phù hợp với quyền của nhân viên/admin.
     */
    private function getDefaultAdminRouteForUser(User $user): string
    {
        if ($user->isWarehouseStaff()) {
            return route('admin.products.index');
        }

        if ($user->isSalesStaff()) {
            return route('admin.orders.index');
        }

        return route('admin.dashboard');
    }
}

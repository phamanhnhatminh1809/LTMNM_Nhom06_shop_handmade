<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * @method \App\Models\User|null user()
 */
class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            return $user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ], [
            'account.required' => 'Vui lòng nhập email hoặc tên tài khoản của bạn.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $account = $request->input('account');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $fieldType = filter_var($account, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $account, 'password' => $password, 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[^a-zA-Z0-9]/',
            ],
            'terms' => 'accepted',
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'username.required' => 'Vui lòng nhập tên tài khoản.',
            'username.unique' => 'Tên tài khoản này đã được sử dụng.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 8 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (ví dụ: @, #, $, %, !...).',
            'terms.accepted' => 'Bạn vui lòng tích chọn đồng ý với Điều khoản sử dụng và Chính sách bảo mật.',
        ]);

        User::create([
            'role_id' => 2, // Customer
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'is_active' => true,
        ]);

        return redirect()->route('login')->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập để tiếp tục.');
    }

    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->isAdmin();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isAdmin) {
            return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi tài khoản quản trị viên.');
        }

        return redirect()->route('home')->with('success', 'Đăng xuất tài khoản thành công.');
    }
}


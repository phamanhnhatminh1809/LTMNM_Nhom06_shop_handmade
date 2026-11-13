<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetOtp;
use App\Mail\ResetPasswordOtpMail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

    /**
     * Gửi OTP xác thực email đăng ký
     */
    public function sendRegisterOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $email = $request->email;
        if (User::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email này đã được sử dụng!'], 422);
        }

        // Tạo mã OTP 6 số
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Lưu tạm vào bảng password_reset_otps
        PasswordResetOtp::where('email', $email)->delete();
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Gửi mail
        try {
            Mail::to($email)->send(new ResetPasswordOtpMail($otp, 'Khách hàng mới'));
        } catch (\Throwable $e) {
            try {
                Log::error('Lỗi gửi mail OTP đăng ký: ' . $e->getMessage());
            } catch (\Throwable $logEx) {
                // Không để lỗi ghi log làm gián đoạn luồng trả mã OTP
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã OTP đã được gửi.',
            'dev_otp' => app()->environment('local') ? $otp : null,
        ]);
    }

    /**
     * Xử lý đăng ký (có kèm kiểm tra OTP)
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'otp' => 'required|string|size:6',
        ], [
            'username.unique' => 'Tên tài khoản này đã được sử dụng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 8 ký tự.',
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.size' => 'Mã OTP phải là 6 chữ số.',
        ]);

        // Kiểm tra OTP có khớp không
        $record = PasswordResetOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record || $record->isExpired()) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác hoặc đã hết hạn.'])->withInput();
        }

        // Tạo user mới
        $user = User::create([
            'role_id' => 2, // Customer
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'is_active' => true,
        ]);

        // Xóa OTP sau khi dùng
        PasswordResetOtp::where('email', $request->email)->delete();

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

    private function redirectByUserRole(User $user): RedirectResponse
    {
        if ($user->isStaffOrAdmin()) {
            return redirect($this->getDefaultAdminRouteForUser($user));
        }

        return redirect()->route('home');
    }

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
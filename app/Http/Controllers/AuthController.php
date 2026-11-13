<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOtpMail;
use App\Models\Cart;
use App\Models\PasswordResetOtp;
use App\Models\User;
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
        $guestSessionId = $request->session()->getId();

        if (Auth::attempt([$fieldType => $account, 'password' => $password, 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isStaffOrAdmin()) {
                return redirect()->intended($this->getDefaultAdminRouteForUser($user));
            }

            Cart::mergeSessionCart($guestSessionId, $user->id);

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
        $request->validate([
            'email'     => 'required|email',
            'full_name' => 'nullable|string|max:100',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email không đúng định dạng.',
        ]);

        $email = trim((string) $request->email);
        if (User::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Email này đã được sử dụng!'], 422);
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::where('email', $email)->delete();
        PasswordResetOtp::create([
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            $recipientName = trim((string) $request->input('full_name', '')) ?: 'Quý khách';
            Mail::to($email)->send(new ResetPasswordOtpMail($otp, $recipientName, 'register'));
        } catch (\Throwable $e) {
            Log::error('Lỗi gửi mail OTP đăng ký: ' . $e->getMessage(), ['email' => $email]);
            PasswordResetOtp::where('email', $email)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi email OTP qua SMTP lúc này. Vui lòng kiểm tra cấu hình MAIL_* trong .env hoặc thử lại sau.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (kể cả mục Thư rác/Spam).',
        ]);
    }

    /**
     * Xử lý đăng ký (có kèm kiểm tra OTP)
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'required|string|email|max:100|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'password'  => ['required', 'string', 'min:8', 'confirmed', 'regex:/[^a-zA-Z0-9]/'],
            'otp'       => 'required|string|size:6',
            'terms'     => 'accepted',
        ], [
            'username.unique'    => 'Tên tài khoản này đã được sử dụng.',
            'email.unique'       => 'Địa chỉ email này đã được sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min'       => 'Mật khẩu phải chứa ít nhất 8 ký tự.',
            'password.regex'     => 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (ví dụ: @, #, $, %, !...).',
            'otp.required'       => 'Vui lòng nhập mã OTP.',
            'otp.size'           => 'Mã OTP phải là 6 chữ số.',
            'terms.accepted'     => 'Bạn cần đồng ý với điều khoản sử dụng và chính sách bảo mật.',
        ]);

        // Kiểm tra OTP có khớp không
        $record = PasswordResetOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record || $record->isExpired()) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác hoặc đã hết hạn.'])->withInput();
        }

        $guestSessionId = $request->session()->getId();

        // Tạo user mới
        $user = User::create([
            'role_id'   => 2, // Customer
            'full_name' => $request->input('full_name'),
            'username'  => $request->input('username'),
            'email'     => $request->input('email'),
            'phone'     => $request->input('phone'),
            'password'  => Hash::make($request->input('password')),
            'is_active' => true,
        ]);

        // Xóa OTP sau khi dùng
        PasswordResetOtp::where('email', $request->email)->delete();

        Auth::login($user);
        $request->session()->regenerate();
        Cart::mergeSessionCart($guestSessionId, $user->id);

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
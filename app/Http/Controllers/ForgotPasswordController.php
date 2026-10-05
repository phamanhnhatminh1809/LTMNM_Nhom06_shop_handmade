<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Bước 1: Tiếp nhận email và gửi mã OTP 6 số qua email.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $email = trim($request->input('email'));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email này chưa được đăng ký trong hệ thống!',
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản liên kết với email này hiện đang bị khóa.',
            ], 403);
        }

        // Sinh mã OTP 6 chữ số
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Xóa các mã OTP cũ của email này để đảm bảo chỉ có 1 mã hợp lệ gần nhất
        PasswordResetOtp::where('email', $email)->delete();

        // Lưu mã OTP mới với thời gian hết hạn là 10 phút
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Gửi email chứa mã OTP
        try {
            Mail::to($email)->send(new ResetPasswordOtpMail($otp, $user->full_name));
        } catch (Exception $e) {
            Log::error('Lỗi khi gửi email OTP: ' . $e->getMessage(), [
                'email' => $email,
                'otp' => $otp,
            ]);

            // Nếu chưa thiết lập thông số Mail Server, hệ thống vẫn lưu OTP và thông báo hướng dẫn
            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được tạo (Mail server đang ở chế độ log). Vui lòng kiểm tra email hoặc file log hệ thống.',
                'dev_otp' => app()->environment('local') ? $otp : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã xác thực OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư!',
            'dev_otp' => app()->environment('local') ? $otp : null,
        ]);
    }

    /**
     * Bước 2: Xác thực mã OTP 6 chữ số.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ], [
            'email.required' => 'Thiếu thông tin email.',
            'otp.required' => 'Vui lòng nhập đầy đủ mã OTP.',
            'otp.size' => 'Mã OTP gồm 6 chữ số.',
        ]);

        $email = trim($request->input('email'));
        $otp = trim($request->input('otp'));

        $record = PasswordResetOtp::where('email', $email)
            ->where('otp', $otp)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực OTP không chính xác. Vui lòng kiểm tra lại!',
            ], 422);
        }

        if ($record->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực OTP này đã hết hạn. Vui lòng yêu cầu gửi lại mã mới!',
            ], 422);
        }

        // Lưu trạng thái đã xác thực OTP vào session tạm thời
        $request->session()->put('otp_verified_email', $email);

        return response()->json([
            'success' => true,
            'message' => 'Xác thực mã OTP thành công! Mời bạn tạo mật khẩu mới.',
        ]);
    }

    /**
     * Bước 3: Đặt lại mật khẩu mới cho tài khoản.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Thiếu thông tin email.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $email = trim($request->input('email'));
        $otp = trim($request->input('otp'));

        // Kiểm tra tính hợp lệ của OTP một lần nữa
        $record = PasswordResetOtp::where('email', $email)
            ->where('otp', $otp)
            ->first();

        if (!$record || $record->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên xác thực đã hết hạn hoặc không hợp lệ. Vui lòng thử lại từ bước đầu!',
            ], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản người dùng!',
            ], 404);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Xóa mã OTP và session tạm sau khi đã đổi mật khẩu thành công
        PasswordResetOtp::where('email', $email)->delete();
        $request->session()->forget('otp_verified_email');

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công! Bạn có thể sử dụng mật khẩu mới để đăng nhập.',
        ]);
    }
}

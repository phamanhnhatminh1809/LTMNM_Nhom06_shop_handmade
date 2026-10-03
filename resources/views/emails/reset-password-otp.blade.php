<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP khôi phục mật khẩu - Shop Handmade</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f5f0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #2d3748;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f7f5f0; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #e76f51 0%, #f4a261 100%); padding: 32px 24px; text-align: center; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: 0.5px;">Shop Handmade</h1>
                            <p style="margin: 6px 0 0; font-size: 13px; opacity: 0.95;">Thế giới đồ thủ công đan len tinh tế</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; color: #1a202c; font-weight: 700;">
                                Xin chào, {{ $userName }}!
                            </h2>
                            <p style="margin: 0 0 20px; line-height: 1.6; color: #4a5568; font-size: 14px;">
                                Chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản liên kết với email này. Dưới đây là mã xác thực <strong>OTP</strong> của bạn:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #fff8f5; border: 2px dashed #f4a261; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0;">
                                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #e76f51; letter-spacing: 1px; margin-bottom: 6px;">
                                    MÃ XÁC THỰC CỦA BẠN
                                </div>
                                <div style="font-size: 34px; font-weight: 800; letter-spacing: 10px; color: #e76f51; font-family: 'Consolas', 'Courier New', monospace;">
                                    {{ $otp }}
                                </div>
                                <div style="font-size: 12px; color: #a0aec0; margin-top: 6px;">
                                    (Hiệu lực trong vòng 10 phút kể từ lúc gửi)
                                </div>
                            </div>

                            <p style="margin: 0 0 12px; line-height: 1.6; color: #718096; font-size: 13px;">
                                <strong>Lưu ý bảo mật:</strong> Tuyệt đối không chia sẻ mã này cho bất kỳ ai, kể cả nhân viên hỗ trợ của Shop. Nếu bạn không gửi yêu cầu này, vui lòng bỏ qua email hoặc đổi mật khẩu ngay để đảm bảo an toàn.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #fcfbf9; padding: 20px 32px; border-top: 1px solid #edf2f7; text-align: center; font-size: 12px; color: #a0aec0;">
                            <p style="margin: 0 0 4px;">Đây là email tự động, vui lòng không phản hồi thư này.</p>
                            <p style="margin: 0;">© {{ date('Y') }} <strong>Shop Handmade Nhóm 06</strong> - Môn LTMNM. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

# Hướng Dẫn Cấu Hình Email Gửi Mã OTP (Quên Mật Khẩu)

Tài liệu này hướng dẫn chi tiết cách thiết lập dịch vụ gửi email và cấu hình file môi trường `.env` để chức năng **Quên mật khẩu bằng mã xác thực OTP 6 chữ số** hoạt động chính xác.

---

## 1. Cơ Chế Hoạt Động Của Chức Năng OTP

Quy trình khôi phục mật khẩu gồm 3 bước:
1. **Bước 1 - Yêu cầu gửi mã (`POST /forgot-password/send-otp`)**:
   - Người dùng nhập email đã đăng ký tài khoản.
   - Hệ thống kiểm tra tính tồn tại và trạng thái của tài khoản.
   - Sinh mã ngẫu nhiên gồm 6 chữ số và lưu vào bảng `password_reset_otps` với thời hạn hiệu lực **10 phút**.
   - Gửi email giao diện HTML (qua Mailable `ResetPasswordOtpMail`) tới hòm thư của người dùng.
2. **Bước 2 - Xác thực mã (`POST /forgot-password/verify-otp`)**:
   - Người dùng nhập mã 6 số nhận được trong email.
   - Giao diện có bộ đếm lùi **60 giây** cho tính năng gửi lại mã OTP.
   - Hệ thống xác thực mã còn trong thời hạn và khớp với email.
3. **Bước 3 - Đặt lại mật khẩu mới (`POST /forgot-password/reset`)**:
   - Người dùng nhập mật khẩu mới và xác nhận mật khẩu (tối thiểu 8 ký tự).
   - Hệ thống cập nhật mật khẩu đã được băm (`Hash::make()`) cho tài khoản người dùng và xóa mã OTP đã sử dụng.

---

## 2. Các Biến Cấu Hình Trong File `.env`

Mở file `.env` tại thư mục gốc của dự án và cập nhật các dòng cấu hình gửi mail như sau:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=dia_chi_gmail_cua_ban@gmail.com
MAIL_PASSWORD="mat_khau_ung_dung_16_ky_tu"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="dia_chi_gmail_cua_ban@gmail.com"
MAIL_FROM_NAME="Shop Handmade"
```

### Ý nghĩa chi tiết của từng biến:

| Biến | Giá trị khuyến nghị | Giải thích |
| :--- | :--- | :--- |
| `MAIL_MAILER` | `smtp` | Phương thức gửi thư qua giao thức SMTP. |
| `MAIL_HOST` | `smtp.gmail.com` | Địa chỉ máy chủ SMTP của Google. |
| `MAIL_PORT` | `587` | Cổng kết nối chuẩn hỗ trợ mã hóa TLS của Gmail. |
| `MAIL_USERNAME` | *Email Gmail của bạn* | Địa chỉ tài khoản Gmail dùng để xác thực và gửi thư. |
| `MAIL_PASSWORD` | *"16 ký tự app password"* | Mật khẩu ứng dụng (App Password) do Google cấp (không phải mật khẩu đăng nhập tài khoản thông thường). |
| `MAIL_ENCRYPTION` | `tls` | Giao thức mã hóa đường truyền bảo mật (bắt buộc với port 587). |
| `MAIL_FROM_ADDRESS` | *Email Gmail của bạn* | Địa chỉ người gửi hiển thị trong hộp thư người nhận (nên trùng với `MAIL_USERNAME`). |
| `MAIL_FROM_NAME` | `"Shop Handmade"` | Tên thương hiệu hiển thị cho người nhận email. |

---

## 3. Hướng Dẫn Từng Bước Lấy Mật Khẩu Ứng Dụng Gmail (App Password)

Google không cho phép ứng dụng bên ngoài đăng nhập trực tiếp bằng mật khẩu tài khoản chính. Bạn cần tạo **Mật khẩu ứng dụng (App Password)** gồm 16 chữ cái theo các bước sau:

1. **Bước 1**: Đăng nhập tài khoản Gmail của bạn trên trình duyệt và truy cập trang quản lý bảo mật:
   Link: [https://myaccount.google.com/security](https://myaccount.google.com/security)

2. **Bước 2**: Đảm bảo tài khoản đã bật **Xác minh 2 bước (2-Step Verification)**:
   - Nếu chưa bật, hãy chọn mục **Xác minh 2 bước** và làm theo hướng dẫn bằng số điện thoại để kích hoạt.

3. **Bước 3**: Tìm mục **Mật khẩu ứng dụng**:
   - Trên thanh tìm kiếm ở đầu trang cài đặt Google, gõ từ khóa: **"Mật khẩu ứng dụng"** (hoặc tiếng Anh: **"App passwords"**).
   - Bấm vào kết quả tìm kiếm để vào trang quản lý mật khẩu ứng dụng.

4. **Bước 4**: Tạo mật khẩu ứng dụng mới:
   - Nhập tên ứng dụng gợi nhớ vào ô tên, ví dụ: `ShopHandmade`.
   - Bấm nút **Tạo (Create)**.

5. **Bước 5**: Sao chép mật khẩu được cấp:
   - Google sẽ hiển thị một cửa sổ chứa mã gồm 16 chữ cái dạng: `abcd efgh ijkl mnop`.
   - Sao chép mã này (xóa bỏ khoảng trắng nếu cần) và dán vào dòng `MAIL_PASSWORD` trong file `.env`:
     ```env
     MAIL_PASSWORD="abcdefghijklmnop"
     ```

---

## 4. Áp Dụng Cấu Hình Sau Khi Sửa File `.env`

Sau khi chỉnh sửa xong file `.env`, hệ thống Laravel cần xóa bộ nhớ đệm (cache) cấu hình để các thông số mới có hiệu lực:

- **Nếu đang chạy môi trường Docker**:
  ```powershell
  docker compose exec app php artisan config:clear
  ```

- **Nếu đang chạy môi trường PHP Local (Laragon / XAMPP)**:
  ```powershell
  php artisan config:clear
  ```

---

## 5. Chế Độ Thử Nghiệm Không Cần Gửi Mail Thật (Chế Độ Log)

Trong trường hợp bạn đang lập trình hoặc kiểm thử tính năng mà chưa muốn thiết lập tài khoản Gmail, bạn có thể chuyển tạm sang chế độ ghi file log:

```env
MAIL_MAILER=log
```

Khi sử dụng chế độ `log`:
- Hệ thống sẽ không kết nối ra Internet và không gửi email thật.
- Toàn bộ nội dung thư kèm mã xác thực OTP sẽ được ghi trực tiếp vào file log: `storage/logs/laravel.log`
- Ngoài ra, ở môi trường phát triển (`APP_ENV=local`), mã OTP cũng được gửi kèm trong phản hồi API và hiển thị tại màn hình Console F12 của trình duyệt để hỗ trợ kiểm thử nhanh chóng.

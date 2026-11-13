# Hướng dẫn cấu hình `.env` & Các dịch vụ bên thứ 3

Tài liệu này tổng hợp đầy đủ các biến môi trường mới trong `.env`, trạng thái tích hợp các dịch vụ bên thứ 3 và hướng dẫn từng bước để bạn điền **thông tin thật** (hệ thống tuyệt đối **không** dùng số tài khoản ngân hàng giả hay OTP giả lập).

---

## 1. Các biến cấu hình mới đã thêm vào `.env`

Trong `.env` và `.env.example` đã bổ sung nhóm biến `SHOP_*` đọc qua `config/shop.php`:

```env
# Phí vận chuyển (đồng nhất toàn hệ thống)
SHOP_FREE_SHIPPING_THRESHOLD=300000
SHOP_DEFAULT_SHIPPING_FEE=30000

# Thanh toán QR tự động qua PayOS (điền 3 key thật của bạn vào đây)
PAYOS_CLIENT_ID=
PAYOS_API_KEY=
PAYOS_CHECKSUM_KEY=
PAYOS_BASE_URL=https://api-merchant.payos.vn

# Thông tin liên hệ cửa hàng
SHOP_CONTACT_PHONE="0969 376 447"
SHOP_CONTACT_EMAIL="phamanhnhatminh1809@gmail.com"
SHOP_CONTACT_ADDRESS="Lê Trọng Tấn, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh"
SHOP_CONTACT_HOURS="Thứ 2 – Chủ nhật: 08:30 – 21:30"
```

---

## 2. Dịch vụ thứ 3 đang thiếu thông tin & Hướng dẫn bổ sung

### A. Cổng thanh toán QR PayOS (`PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY`) — **CẦN BẠN DÁN 3 KEY THẬT VÀO `.env`**

- **Tình trạng hiện tại**: Hệ thống đã chuyển sang tích hợp **PayOS API v2** cho phương thức **Thanh toán qua mã QR (PayOS VietQR)**. Hiện 3 biến `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY` trong `.env` đang để khung trống để bảo mật khóa thật của bạn.
- **Lý do không dùng các biến `VNPAY_*`**: Hệ thống chỉ cần dùng bộ 3 key `PAYOS_*` là đủ chạy hoàn chỉnh thanh toán QR trên `http://localhost:8080` (`returnUrl` và `cancelUrl` được Laravel truyền động, không bị gắn cứng `VNPAY_RETURN_URL` sang domain cũ).
- **Hành vi hệ thống khi chưa cấu hình**:
  - Khách vẫn đặt **Thanh toán khi nhận hàng (COD)** bình thường.
  - Nếu khách chọn **Thanh toán QR (PayOS)** ở `/checkout`, hệ thống sẽ hiển thị hướng dẫn cấu hình 3 biến `PAYOS_*` trong `.env` và chặn tạo đơn QR cho tới khi bạn điền đủ key thật.
- **Cách kích hoạt**:
  1. Mở file `.env` ở thư mục gốc dự án.
  2. Dán đúng 3 giá trị thật từ hệ thống/kênh thanh toán PayOS của bạn:
     - `PAYOS_CLIENT_ID=...`
     - `PAYOS_API_KEY=...`
     - `PAYOS_CHECKSUM_KEY=...`
     - `PAYOS_BASE_URL=https://api-merchant.payos.vn`
  3. Chạy lệnh xóa cache cấu hình ở mục 3 bên dưới.
  4. *(Tùy chọn khi deploy lên domain HTTPS thật)*: Có thể khai báo Webhook URL trên `my.payos.vn` trỏ tới `https://<domain-cua-ban>/checkout/payos/webhook`. Khi chạy ở `localhost:8080`, hệ thống vẫn tự xác thực thanh toán `PAID` qua `returnUrl` (`GET /checkout/payos/return`) và nút kiểm tra trạng thái bằng API PayOS.

---

### B. Dịch vụ gửi Email OTP qua Gmail SMTP (`smtp.gmail.com`) — **ĐÃ CÓ CẤU HÌNH TRONG `.env`, LƯU Ý KHI ĐỔI TÀI KHOẢN HOẶC MÔI TRƯỜNG**

- **Tình trạng hiện tại**: `.env` đang cấu hình sẵn:
  - `MAIL_MAILER=smtp`
  - `MAIL_HOST=smtp.gmail.com`
  - `MAIL_PORT=587`
  - `MAIL_ENCRYPTION=tls`
  - `MAIL_USERNAME` & `MAIL_PASSWORD` (Google App Password 16 ký tự) đã có sẵn trong `.env`.
- **Thay đổi trong code**:
  - Đã xóa hoàn toàn `dev_otp` giả lập trả về JSON và `alert()` trên trình duyệt.
  - Nếu gửi SMTP thành công, người dùng sẽ nhận email OTP thật (phân biệt rõ email **Đăng ký tài khoản** và **Khôi phục mật khẩu**).
  - Nếu Google từ chối xác thực SMTP (hết hạn App Password, sai mật khẩu, bị chặn mạng), server sẽ ghi log chi tiết tại `storage/logs/laravel.log`, xóa OTP vừa tạo và trả mã lỗi HTTP `503` thay vì báo gửi thành công giả.
- **Hướng dẫn nếu muốn đổi tài khoản Gmail hoặc tạo mới App Password**:
  1. Bật **Xác minh 2 bước (2-Step Verification)** tại [https://myaccount.google.com/security](https://myaccount.google.com/security).
  2. Truy cập mục **Mật khẩu ứng dụng (App passwords)** tại [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords).
  3. Tạo ứng dụng mới (đặt tên ví dụ `Shop Handmade`), sao chép chuỗi 16 ký tự (bỏ dấu cách) gán vào `MAIL_PASSWORD="..."`, cập nhật `MAIL_USERNAME` và `MAIL_FROM_ADDRESS` khớp với Gmail đó.

---

### C. Dịch vụ tra cứu địa giới hành chính Việt Nam (`provinces.open-api.vn`) — **DỊCH VỤ CÔNG KHAI KHÔNG CẦN KEY**

- **Tình trạng hiện tại**: Trang Thanh toán (`/checkout`) và Hồ sơ (`/profile`) gọi API công khai `https://provinces.open-api.vn/api/v2` từ trình duyệt để tải danh sách Tỉnh/Thành và Phường/Xã (không cần API key).
- **Cơ chế dự phòng đã bổ sung**: Nếu máy chủ API bên thứ 3 bị gián đoạn hoặc trình duyệt không có kết nối Internet ngoài, giao diện sẽ tự động thông báo và chuyển sang ô nhập địa chỉ thủ công thay vì làm kẹt nút đặt hàng.

---

## 3. Lệnh cần chạy sau khi bạn cập nhật `.env`

Mỗi khi chỉnh sửa `.env`, hãy chạy lệnh sau để Laravel trong container Docker nạp lại cấu hình mới:

```bash
docker exec ltmnm_shop_handmade_web php artisan config:clear
docker exec ltmnm_shop_handmade_web php artisan optimize:clear
```

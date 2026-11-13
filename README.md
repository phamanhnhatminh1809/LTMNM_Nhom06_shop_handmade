# LTMNM Nhóm 06 - Shop Handmade

Website thương mại điện tử bán đồ thủ công handmade, được xây dựng trên nền tảng Laravel 12 theo kiến trúc MVC chuẩn.

================================================================================
1. HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY DỰ ÁN
================================================================================

## LARAGON

Bước 1: Mở Laragon và nhấn "Start All" (chạy Apache và MySQL).
Bước 2: Tạo cơ sở dữ liệu có tên: ltmnm_shop_handmade
Bước 3: Mở cửa sổ dòng lệnh (Terminal) tại thư mục dự án và chạy:

  # 1. Cài đặt các thư viện (nếu chưa có thư mục vendor)
  composer install

  # 2. Tạo bảng CSDL và nạp dữ liệu mẫu
  php artisan migrate:fresh --seed

  # 3. Khởi chạy máy chủ phát triển
  php artisan serve

Bước 4: Mở trình duyệt và truy cập: http://localhost:8000


## DOCKER

Bước 1: Mở Docker Desktop và đảm bảo Docker đang chạy.
Bước 2: Khởi động hệ thống container:
  docker compose up -d --build

Bước 3: Khởi tạo dữ liệu bên trong container app:
  docker compose exec app php artisan migrate:fresh --seed

Bước 4: Truy cập website:
  - Máy chủ Apache (khuyên dùng): http://localhost:8080
  - Máy chủ Laravel Artisan:     http://localhost:8000

* Lưu ý: Nếu muốn vào terminal trực tiếp trong container app:
  docker compose exec -it app bash


================================================================================
2. THÔNG TIN CƠ SỞ DỮ LIỆU (MYSQL)
================================================================================

- Tên cơ sở dữ liệu: ltmnm_shop_handmade
- Chạy Local (Laragon): Host: 127.0.0.1 | Port: 3306 | User: root | Password: (để trống)
- Chạy qua Docker:      Host: 127.0.0.1 | Port: 3307 | User: root | Password: root


================================================================================
3. TÀI KHOẢN DÙNG THỬ (TEST ACCOUNTS)
================================================================================

* MẬT KHẨU DÙNG CHUNG CHO TẤT CẢ TÀI KHOẢN: password123

1. Quản trị viên (Toàn quyền hệ thống):
   - Email: admin@shophandmade.com
   - Tên đăng nhập: shop_admin
   - Quyền hạn: Quản lý toàn bộ kho hàng, đơn hàng, khách hàng, nhân viên, phân quyền và thống kê doanh thu.

2. Nhân viên kho:
   - Email: warehouse@shophandmade.com
   - Tên đăng nhập: staff_warehouse
   - Quyền hạn: Thêm, sửa, xóa sản phẩm, cập nhật tồn kho và quản lý loại sản phẩm. Không xem được đơn hàng và doanh thu.

3. Nhân viên bán hàng:
   - Email: sales@shophandmade.com
   - Tên đăng nhập: staff_sales
   - Quyền hạn: Xem và duyệt đơn hàng, cập nhật trạng thái đơn (chuyển hàng, hoàn tất), xem và duyệt đánh giá của khách hàng.

4. Khách hàng:
   - Khách 1: test_customer@gmail.com (Tên đăng nhập: test_customer)
   - Khách 2: tranbinh@mail.com       (Tên đăng nhập: tranbinh)
   - Khách 3: levanminh@gmail.com     (Tên đăng nhập: levanminh)
   - Quyền hạn: Xem sản phẩm, đặt hàng COD, quản lý giỏ hàng, theo dõi đơn hàng cá nhân, viết đánh giá sản phẩm.


================================================================================
4. CÁC ĐƯỜNG DẪN TRANG CHÍNH TRONG HỆ THỐNG
================================================================================

* Dành cho Khách hàng:
  - Trang chủ:              http://localhost:8000/
  - Danh sách sản phẩm:     http://localhost:8000/products
  - Chi tiết sản phẩm:      http://localhost:8000/products/{id}
  - Giỏ hàng:               http://localhost:8000/cart
  - Đặt hàng & Thanh toán:  http://localhost:8000/checkout
  - Đơn hàng của tôi:       http://localhost:8000/orders
  - Trang cá nhân:          http://localhost:8000/profile
  - Đăng nhập:              http://localhost:8000/login
  - Đăng ký tài khoản:      http://localhost:8000/register
  - Giới thiệu / Liên hệ:   http://localhost:8000/about  hoặc  /contact

* Dành cho Ban Quản trị & Nhân viên (Khu vực Admin):
  - Tổng quan (Dashboard):  http://localhost:8000/admin/dashboard
  - Quản lý Sản phẩm:       http://localhost:8000/admin/products
  - Quản lý Loại sản phẩm:  http://localhost:8000/admin/categories
  - Quản lý Đơn hàng:       http://localhost:8000/admin/orders
  - Quản lý Đánh giá:       http://localhost:8000/admin/reviews
  - Quản lý Khách hàng:     http://localhost:8000/admin/customers
  - Phân quyền & Nhân viên: http://localhost:8000/admin/staff


================================================================================
5. CẤU HÌNH GỬI EMAIL OTP (QUÊN MẬT KHẨU)
================================================================================

Để kích hoạt tính năng gửi mã OTP về Gmail khi người dùng quên mật khẩu, xem hướng dẫn chi tiết tại file:
docs/HUONG_DAN_CAU_HINH_EMAIL_OTP.md

# 🧶 LTMNM Nhóm 06 - Shop Handmade 

Dự án website thương mại điện tử đồ thủ công **Shop Handmade**, được xây dựng trên nền tảng **Laravel 12** theo kiến trúc chuẩn **MVC (Model - View - Controller)**.

---

## Kiến Trúc Hệ Thống 

- **M (Model)**: `app/Models/` (`Category`, `Product`, `User`, `Role`, `Order`, `OrderItem`) sử dụng Eloquent ORM kết nối MySQL.
- **V (View)**: `resources/views/` sử dụng Blade Template Engine kế thừa layout (`layouts/app.blade.php`, `layouts/admin.blade.php`, `layouts/auth.blade.php`).
- **C (Controller)**: `app/Http/Controllers/` (`HomeController`, `ProductController`, `AuthController`, `Admin\DashboardController`, `Admin\ProductController`).
- **R (Routes)**: `routes/web.php` với cấu trúc URL RESTful.
- **Tài nguyên tĩnh (Assets)**: `public/css/`, `public/js/`, `public/images/`.

---

## Hướng dẫn chạy dự án

### Cách 1: Chạy bằng Laragon / PHP Local (Nhanh nhất)
1. Mở **Laragon** -> Nhấn **Start All** (Apache & MySQL).
2. Tạo database `ltmnm_shop_handmade` trong MySQL (nếu chưa có).
3. Mở Terminal tại thư mục dự án và chạy:
   ```bash
   # Cài đặt dependencies (nếu chưa có vendor)
   composer install

   # Chạy Migration và nạp dữ liệu mẫu (sản phẩm ngẫu nhiên 6-13 SP/danh mục, tài khoản, đơn hàng, đánh giá)
   php artisan migrate:fresh --seed

   # Khởi chạy server phát triển
   php artisan serve
   ```
4. Truy cập website: [http://localhost:8000](http://localhost:8000)

---

### Cách 2: Chạy bằng Docker Compose
1. Đảm bảo **Docker Desktop** đang chạy.
2. Khởi động hệ thống container:
   ```bash
   docker compose up -d --build
   ```
3. Chạy migration và seed dữ liệu bên trong container `app`:
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```
   docker compose exec app php artisan serve
4. Truy cập website:
   - **Web Apache Server (Khuyến nghị - Siêu mượt, không giật lag)**: [http://localhost:8080](http://localhost:8080)
   - **Laravel App (Artisan)**: [http://localhost:8000](http://localhost:8000)
   
5. Để thực hiện các lệnh php/composer trực tiếp trong docker
   docker compose exec -it app bash
---

## Các đường dẫn chính

| Trang | URL | Chức năng |
| :--- | :--- | :--- |
| **Trang chủ** | `/` | Banner slider, danh mục, sản phẩm nổi bật & mới nhất |
| **Danh sách sản phẩm** | `/products` | Lọc theo danh mục, khoảng giá, sắp xếp, tìm kiếm, phân trang |
| **Chi tiết sản phẩm** | `/products/{id}` | Gallery ảnh, giá, tồn kho, mô tả, sản phẩm liên quan |
| **Đăng nhập** | `/login` | Đăng nhập tài khoản, modal khôi phục mật khẩu OTP |
| **Đăng ký** | `/register` | Đăng ký tài khoản thành viên mới |
| **Giới thiệu** | `/about` | Giới thiệu cửa hàng đồ handmade |
| **Liên hệ** | `/contact` | Thông tin liên hệ, hotline, địa chỉ |
| **Giỏ hàng** | `/cart` | Quản lý giỏ hàng, cập nhật số lượng, xóa món |
| **Thanh toán COD** | `/checkout` | Đặt hàng thanh toán khi nhận hàng, kiểm tra giỏ hàng |
| **Đơn hàng của tôi** | `/orders` | Lịch sử mua hàng, chi tiết đơn, đánh giá sản phẩm |
| **Hồ sơ cá nhân** | `/profile` | Quản lý thông tin tài khoản, đổi mật khẩu |
| **Tổng quan Admin** | `/admin/dashboard` | 5 thẻ chỉ số, thống kê bán chạy, biểu đồ Chart.js doanh thu & danh mục |
| **Quản lý Sản phẩm** | `/admin/products` | Danh sách, lọc danh mục, thêm/sửa/xóa sản phẩm, cập nhật tồn kho |
| **Quản lý Loại sản phẩm** | `/admin/categories` | Thêm, sửa, xóa loại sản phẩm handmade, chống xóa khi có sản phẩm |
| **Quản lý Đơn hàng** | `/admin/orders` | Danh sách đơn, lọc trạng thái, duyệt đơn (Chờ xác nhận ➔ Đang giao ➔ Hoàn thành) |
| **Quản lý Đánh giá** | `/admin/reviews` | Lọc đánh giá theo sản phẩm/số sao, tìm kiếm, kiểm duyệt xóa vi phạm |
| **Quản lý Khách hàng** | `/admin/customers` | Danh sách tài khoản khách, xem lịch sử đơn, khóa/mở khóa tài khoản |
| **Nhân viên & Phân quyền** | `/admin/staff` | Quản lý tài khoản nội bộ, cấp quyền Admin / Nhân viên kho / Nhân viên bán hàng |

---

## 🔑 Tài khoản thử nghiệm

Tất cả tài khoản mẫu đều dùng chung mật khẩu: `password123`

| Vai trò | Họ tên | Email / Tên đăng nhập | SĐT | Quyền hạn chi tiết |
| :--- | :--- | :--- | :--- | :--- |
| 👑 **Quản trị viên (Admin)** | Quản trị viên | `admin@shophandmade.com`<br>`shop_admin` | `0901234567` | **Toàn quyền hệ thống**: Quản lý kho hàng, đơn hàng, khách hàng, nhân viên, phân quyền RBAC và thống kê doanh thu. |
| 📦 **Nhân viên kho** | Phạm Hoàng Kho | `warehouse@shophandmade.com`<br>`staff_warehouse` | `0945678901` | **Quản lý kho**: Toàn quyền thêm, sửa, xóa sản phẩm, cập nhật số lượng tồn kho và quản lý loại sản phẩm. Không xem được đơn hàng/doanh thu. |
| 💼 **Nhân viên bán hàng** | Đỗ Thu Bán Hàng | `sales@shophandmade.com`<br>`staff_sales` | `0956789012` | **Quản lý bán hàng**: Xem và duyệt đơn hàng, cập nhật trạng thái đơn (chuyển hàng, hoàn tất), xem và kiểm duyệt đánh giá khách hàng. |
| 👤 **Khách hàng 1** | Nguyễn Văn An | `test_customer@gmail.com`<br>`test_customer` | `0912345678` | Đặt hàng COD, xem lịch sử đơn, quản lý giỏ hàng, viết đánh giá sản phẩm đã mua, cập nhật thông tin cá nhân. |
| 👤 **Khách hàng 2** | Trần Thị Bình | `tranbinh@mail.com`<br>`tranbinh` | `0923456789` | Tài khoản khách hàng mẫu (đã có đơn hàng mẫu đang giao và đã hoàn thành). |
| 👤 **Khách hàng 3** | Lê Văn Minh | `levanminh@gmail.com`<br>`levanminh` | `0934567890` | Tài khoản khách hàng mẫu (đã có đơn hàng mẫu giá trị cao đã hoàn thành). |

---

## Kết nối cơ sở dữ liệu mysql

- **Chạy Local (Laragon)**: Port `3306` | User `root` | Password: ` ` (trống)
- **Chạy Docker**: Port `3307` (Host) -> `3306` (Container) | User `root` | Password `root`
- **Database**: `ltmnm_shop_handmade`

---

## Cấu hình dịch vụ gửi Email (OTP Quên mật khẩu)

Chi tiết các bước cấu hình Google App Password và thiết lập file `.env` xem tại tài liệu:
[docs/HUONG_DAN_CAU_HINH_EMAIL_OTP.md](docs/HUONG_DAN_CAU_HINH_EMAIL_OTP.md)

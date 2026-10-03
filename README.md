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

   # Chạy Migration và nạp 50 sản phẩm mẫu từ Seeder
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
4. Truy cập website:
   - **Laravel App (Artisan)**: [http://localhost:8000](http://localhost:8000)
   - **Web Apache Server**: [http://localhost:8080](http://localhost:8080)
   
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
| **Tổng quan Admin** | `/admin/dashboard` | 5 thẻ chỉ số, biểu đồ Chart.js doanh thu & danh mục, đơn gần đây |
| **Quản lý SP Admin** | `/admin/products` | Bảng quản lý sản phẩm, tồn kho, modal thêm sản phẩm |

---

## 🔑 Tài khoản thử nghiệm

| Vai trò | Email / Username | Mật khẩu | Quyền hạn |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin@shophandmade.com` / `shop_admin` | `password123` | Quản trị toàn bộ hệ thống, kho hàng, đơn hàng |
| **Khách hàng mẫu** | `test_customer@gmail.com` / `test_customer` | `password123` | Mua sắm, theo dõi đơn hàng |

---

## 🗄️ Kết nối cơ sở dữ liệu mysql

- **Chạy Local (Laragon)**: Port `3306` | User `root` | Password: ` ` (trống)
- **Chạy Docker**: Port `3307` (Host) -> `3306` (Container) | User `root` | Password `root`
- **Database**: `ltmnm_shop_handmade`

---

## Cấu hình dịch vụ gửi Email (OTP Quên mật khẩu)

Chi tiết các bước cấu hình Google App Password và thiết lập file `.env` xem tại tài liệu:
[docs/HUONG_DAN_CAU_HINH_EMAIL_OTP.md](docs/HUONG_DAN_CAU_HINH_EMAIL_OTP.md)

# LTMNM Nhóm 06 - Shop Handmade

## Kiến trúc các Container

| Service | Tên Container | Vai trò & Công nghệ | Địa chỉ / Port |
| :--- | :--- | :--- | :--- |
| **`web`** | `ltmnm_shop_handmade_web` | Apache Web Server + PHP 8.2 (chạy ứng dụng Frontend) | `http://localhost:8080` |
| **`app`** | `ltmnm_shop_handmade_app` | Laravel API Server + PHP 8.2 CLI + Composer | `http://localhost:8000` (API) \| Thư mục: `/var/www/html` |
| **`db`** | `ltmnm_shop_handmade_mysql_db` | MySQL 8.0 Database | Cổng `3307` (Host) -> `3306` (Container) |

---

## Hướng dẫn khởi chạy Docker

### Khởi chạy lần đầu tiên (hoặc khi có thay đổi Dockerfile/docker-compose)
```bash
docker compose up -d --build
```

### Khởi động lại docker (dùng khi pull code mới về)
```bash
docker compose restart
```

### Dừng hệ thống container
```bash
docker compose down
```

### 4. Khởi động lại kèm RESET toàn bộ cơ sở dữ liệu (Re-seed DB)
> **Cảnh báo:** Lệnh này sẽ **XÓA SẠCH** toàn bộ dữ liệu trong volume database và khởi tạo lại từ đầu theo 2 file schema & seeder!
```bash
docker compose down -v ; docker compose up -d
```

---

## Hướng dẫn sử dụng Container `app` (Laravel API Backend, PHP CLI & Composer)

Container `app` được cấu hình để ánh xạ toàn bộ mã nguồn dự án vào `/var/www/html` trong container (vừa chạy Laravel API Server tại cổng `8000`, vừa phục vụ chạy lệnh `artisan`, `composer` và hỗ trợ toàn bộ cấu trúc `backend/src/features/`).

### Kiểm tra môi trường PHP & Composer
```bash
docker compose exec app php -v
docker compose exec app composer -v
```

### Quản lý thư viện PHP với Composer
- **Cài đặt toàn bộ dependencies** (khi có `composer.json`):
  ```bash
  docker compose exec app composer install
  ```
- **Cài đặt thêm thư viện mới**:
  ```bash
  docker compose exec app composer require <tên-package>
  ```
- **Tối ưu hoặc cập nhật lại autoload**:
  ```bash
  docker compose exec app composer dump-autoload

  # Hoặc tối ưu hóa hiệu năng (tạo classmap tĩnh):
  docker compose exec app composer dump-autoload -o
  ```
  > **Khi nào cần chạy lệnh này?**
  > - **Khi sửa cấu hình `autoload` / `autoload-dev` trong `composer.json`**: Ví dụ khi bổ sung namespace mới (như ánh xạ `"App\\Features\\": "backend/src/features/"`), thêm đường dẫn `files` (helper functions) hoặc `classmap`.
  > - **Khi gặp lỗi `Class ... not found` hoặc `Target class [...] does not exist`**: Mặc dù PSR-4 tự động tải file theo quy ước đặt tên, nhưng khi mới tạo class, helper, seeder hoặc migration mới mà hệ thống chưa nhận diện được, chạy lệnh này để Composer quét và cập nhật lại danh sách autoload.
  > - **Sau khi kéo code mới về (`git pull`) hoặc chuyển nhánh (`git checkout`)**: Nếu nhánh mới có thêm class mới hoặc cập nhật cấu trúc namespace từ các thành viên khác.
  > - **Khi muốn tối ưu tốc độ tải trang / hiệu năng (Production)**: Sử dụng cờ `-o` (hoặc `--optimize`) để gom tất cả quy tắc PSR-4 thành classmap tĩnh, giúp PHP nạp class nhanh hơn đáng kể.

### Chạy trực tiếp terminal bên trong container `app`
Nếu  muốn vào thẳng môi trường dòng lệnh bên trong container để thực hiện nhiều lệnh liên tục:
```bash
docker compose exec app bash
```
*(Gõ `exit` để quay trở về terminal máy chủ)*

---

## Truy cập hệ thống

- **Trang chủ Website (Frontend)**: [http://localhost:8080](http://localhost:8080)
- **API Backend (Laravel)**: [http://localhost:8000](http://localhost:8000) (ví dụ health check: `http://localhost:8000/up`)

---

## Kết nối cơ sở dữ liệu MySQL (DBeaver, DataGrip, Navicat, v.v.)

- **Host**: `localhost` (hoặc `127.0.0.1`)
- **Port**: `3307`
- **Database**: `ltmnm_shop_handmade`
- **Username**: `root`
- **Password**: `root`
- **Lưu ý cấu hình driver (DBeaver)**: Đặt thuộc tính `allowPublicKeyRetrieval: True`, kiểu kết nối `Connect by: Host`.

---

## Không gian lập trình quy trình làm việc

### Không gian lập trình
- **Team FE**: Lập trình riêng ở một nhánh và làm việc trong thư mục `frontend` (chỉ merge vào nhánh `main` khi có yêu cầu).
- **Team BE**: Lập trình riêng ở một nhánh và làm việc trong thư mục `backend`, merge vào nhánh `main` ngay khi hoàn thành chức năng.

### Quy trình làm việc
- **Team FE**: **LUÔN LUÔN** `git pull` và `git rebase` code từ nhánh `main` về nhánh hiện tại trước khi bắt đầu code.
- **Team BE**: Khi hoàn thành một chức năng ở nhánh tính năng, thực hiện merge ngay vào nhánh `main`.

# 🧶 Shop Handmade - Frontend

## Yêu cầu
- PHP >= 8.0 (khuyên dùng Laragon)
- Git

## Cách chạy

```bash
# 1. Clone
git clone https://github.com/phamanhnhatminh1809/LTMNM_Nhom06_shop_handmade.git
cd LTMNM_Nhom06_shop_handmade

# 2. Checkout nhánh FE
git checkout fe/customer-ui-core

# 3. Chạy server
cd frontend/src
php -S localhost:8000
```

## Mở trình duyệt
```
http://localhost:8000
```

## Các URL test
- `/` - Trang chủ
- `/?route=products` - Danh sách SP
- `/?route=product-detail&id=1` - Chi tiết SP
- `/?route=products&q=túi` - Tìm kiếm
- `/?route=products&category=moc-khoa` - Lọc danh mục

## Nếu lỗi `php: command not found`
Cài **Laragon** (https://laragon.org) → mở Laragon Terminal → chạy lệnh trên.

## Tắt server
`Ctrl + C`
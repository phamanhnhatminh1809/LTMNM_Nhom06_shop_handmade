USE ltmnm_shop_handmade;

-- XÓA DỮ LIỆU CŨ VÀ RESET AUTO_INCREMENT
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;
TRUNCATE TABLE roles;

SET FOREIGN_KEY_CHECKS = 1;


-- KHỞI TẠO DỮ LIỆU MẪU (SEEDING)


-- Bảng roles
INSERT INTO roles (id, name) VALUES 
(1, 'admin'),
(2, 'customer');

-- Bảng users (Mật khẩu mẫu ở đây là chuỗi đã được hash bằng bcrypt cho chữ "password123")
INSERT INTO users (id, role_id, username, email, password, full_name, phone, is_active) VALUES 
(1, 1, 'shop_admin', 'admin@shophandmade.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', '0901234567', 1),
(2, 2, 'test_customer', 'test_customer@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Customer', '0912345678', 1),
(3, 2, 'khachhang02', 'khachhang02@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0923456789', 1),
(4, 2, 'khachhang03', 'khachhang03@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn C', '0934567890', 1),
(5, 2, 'khachhang_lock', 'locked@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị D (Bị khóa)', '0945678901', 0);

-- Bảng categories
INSERT INTO categories (id, name, slug) VALUES 
(1, 'Đồ len', 'do-len'),
(2, 'Trang sức handmade', 'trang-suc-handmade'),
(3, 'Gốm sứ', 'gom-su'),
(4, 'Đồ da thủ công', 'do-da-thu-cong'),
(5, 'Quà tặng lưu niệm', 'qua-tang-luu-niem');

-- Bảng products
INSERT INTO products (id, category_id, code, name, description, instruction_preserve, image, price, stock_quantity) VALUES 
(1, 1,'SP001', 'Áo len đan tay', 'Áo len đan thủ công từ sợi len cừu mềm mại, giữ ấm tốt.', 'Giặt tay bằng nước lạnh, không dùng máy sấy.', '/images/products/sp001.jpg', 350000.00, 50),
(2, 2,'SP002', 'Vòng tay Macrame', 'Vòng tay đan chéo phong cách boho, dây cotton bền chắc.', 'Tránh tiếp xúc với hóa chất tẩy rửa mạnh.', '/images/products/sp002.jpg', 45000.00, 100),
(3, 3,'SP003', 'Cốc gốm vẽ tay', 'Cốc gốm nung nhiệt độ cao, họa tiết hoa nhí nặn nổi.', 'Rửa bằng mút mềm, dùng được trong lò vi sóng.', '/images/products/sp003.jpg', 120000.00, 30),
(4, 4,'SP004', 'Ví da nam handmade', 'Ví da bò sáp thật khâu tay 100%, thiết kế tối giản.', 'Dùng xi đánh da chuyên dụng khi bị xước.', '/images/products/sp004.jpg', 550000.00, 20),
(5, 1,'SP005', 'Móc khóa len Amigurumi', 'Móc khóa hình gấu dâu đan bằng len cotton dệt.', 'Bọc trong túi lưới nếu giặt bằng máy.', '/images/products/sp005.jpg', 35000.00, 200),
(6, 5,'SP006', 'Dây chuyền bạc đính đá', 'Dây chuyền bạc 925 chế tác thủ công nguyên khối.', 'Bảo quản trong hộp kín khi không sử dụng.', '/images/products/sp006.jpg', 250000.00, 15),
(7, 5,'SP007', 'Túi xách sợi thô', 'Túi xách đi biển đan từ sợi thô dệt kim, dáng tote rộng rãi.', 'Chỉ giặt hấp hoặc lau bằng khăn ẩm.', '/images/products/sp007.jpg', 450000.00, 10),
(8, 5,'SP008', 'Nến thơm tinh dầu thiên nhiên', 'Nến đậu nành hương hoa hồng và gỗ đàn hương 200g.', 'Cắt bấc 0.5cm trước mỗi lần đốt để không bị khói đen.', '/images/products/sp008.jpg', 180000.00, 40),
(9, 4,'SP009', 'Sổ tay da bọc gáy', 'Sổ tay vintage giấy kraft dày dặn, bìa da bò khắc laser.', 'Tránh để ở nơi ẩm ướt, mốc.', '/images/products/sp009.jpg', 220000.00, 25),
(10,3,'SP010', 'Bình gốm cắm hoa men chảy', 'Bình gốm dáng cao, men chảy tự nhiên độc bản.', 'Cẩn thận khi cầm nắm, lau bằng khăn mềm.', '/images/products/sp010.jpg', 380000.00, 5);

-- Bảng orders
INSERT INTO orders (id, code, user_id, shipping_name, shipping_phone, shipping_address, total_amount, status) VALUES 
(1, 'ORD-1001', 2, 'Nguyễn Văn A', '0912345678', '123 Đường A, Quận 1, TP.HCM', 440000.00, 'completed'),
(2, 'ORD-1002', 3, 'Trần Thị B', '0923456789', '456 Đường B, Quận 2, TP.HCM', 360000.00, 'shipping'),
(3, 'ORD-1003', 2, 'Nguyễn Văn A', '0912345678', '123 Đường A, Quận 1, TP.HCM', 725000.00, 'pending'),
(4, 'ORD-1004', 4, 'Lê Văn C', '0934567890', '789 Đường C, Quận 3, TP.HCM', 500000.00, 'cancelled'),
(5, 'ORD-1005', 4, 'Nguyễn Thị E (Nhận thay)', '0988888888', '321 Đường D, Quận 4, TP.HCM', 450000.00, 'pending');

-- Bảng order_items (Dữ liệu thành tiền = quantity * unit_price tương ứng với orders)
INSERT INTO order_items (id, order_id, product_id, quantity, unit_price, total_price) VALUES 
-- Đơn hàng 1: Tổng 440,000 (1 Áo len + 2 Vòng tay)
(1, 1, 1, 1, 350000.00, 350000.00),
(2, 1, 2, 2, 45000.00, 90000.00),

-- Đơn hàng 2: Tổng 360,000 (3 Cốc gốm)
(3, 2, 3, 3, 120000.00, 360000.00),

-- Đơn hàng 3: Tổng 725,000 (1 Ví da + 5 Móc khóa len)
(4, 3, 4, 1, 550000.00, 550000.00),
(5, 3, 5, 5, 35000.00, 175000.00),

-- Đơn hàng 4: Tổng 500,000 (2 Dây chuyền bạc)
(6, 4, 6, 2, 250000.00, 500000.00),

-- Đơn hàng 5: Tổng 450,000 (1 Túi xách sợi thô)
(7, 5, 7, 1, 450000.00, 450000.00);
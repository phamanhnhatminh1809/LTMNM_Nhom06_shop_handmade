USE ltmnm_shop_handmade;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;
TRUNCATE TABLE roles;
SET FOREIGN_KEY_CHECKS = 1;

-- Bảng roles
INSERT INTO roles (id, name, created_at, updated_at) VALUES 
(1, 'admin', NOW(), NOW()),
(2, 'customer', NOW(), NOW());

-- Bảng users
INSERT INTO users (id, role_id, username, email, password, full_name, phone, is_active, created_at, updated_at) VALUES 
(1, 1, 'shop_admin', 'admin@shophandmade.com', '$2y$12$nsX2.Eq354TdiYd5ee7tf.PGGaoWsbZPlyBJl.7PXQfBPjZLKIboe', 'Quản trị viên', '0901234567', 1, NOW(), NOW()),
(2, 2, 'test_customer', 'test_customer@gmail.com', '$2y$12$ziTCpctJxdDXtaPOvvyESuv8s9nC8KsJOYhgZHXNU77WnrpHc5l7.', 'Nguyễn Văn An', '0912345678', 1, NOW(), NOW()),
(3, 2, 'tranbinh', 'tranbinh@mail.com', '$2y$12$r17kaMca/a8bXAp50euluuCPZFGAxGDZ1eZqbqy8ypCF0jQZcMBKa', 'Trần Thị Bình', '0923456789', 1, NOW(), NOW()),
(4, 2, 'levanminh', 'levanminh@gmail.com', '$2y$12$COqmdFBe5m/Q2GKGj411hOhpL9LXoRTMR2LHmJ8e/uErqaQBHnzEC', 'Lê Văn Minh', '0934567890', 1, NOW(), NOW());

-- Bảng categories
INSERT INTO categories (id, name, slug, icon, created_at, updated_at) VALUES 
(1, 'Túi xách', 'tui-xach', '👜', NOW(), NOW()),
(2, 'Móc khóa', 'moc-khoa', '🔑', NOW(), NOW()),
(3, 'Trang sức', 'trang-suc', '💍', NOW(), NOW()),
(4, 'Gấu bông', 'gau-bong', '🧸', NOW(), NOW()),
(5, 'Khăn len', 'khan-len', '🧣', NOW(), NOW());

-- Bảng products (50 sản phẩm)
INSERT INTO products (id, code, category_id, name, slug, description, instruction_preserve, image, gallery, price, old_price, stock_quantity, rating, sold, is_featured, created_at, updated_at) VALUES 
(1, 'SP001', 1, 'Túi len thủ công', 'tui-len-thu-cong', 'Túi len thủ công đan tỉ mỉ từng mũi, chất liệu cotton mềm mại, thân thiện môi trường.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-01.jpg', '[\"assets\\/images\\/products\\/tui-01.jpg\",\"assets\\/images\\/products\\/tui-02.jpg\",\"assets\\/images\\/products\\/tui-03.jpg\"]', 250000, 320000, 12, 4.8, 156, 1, NOW(), NOW()),
(2, 'SP002', 1, 'Túi tote len', 'tui-tote-len', 'Túi tote len đan tay, rộng rãi, đựng được laptop 13 inch.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-02.jpg', '[\"assets\\/images\\/products\\/tui-02.jpg\"]', 280000, NULL, 8, 4.9, 34, 1, NOW(), NOW()),
(3, 'SP003', 1, 'Túi đeo chéo mini', 'tui-deo-cheo-mini', 'Túi đeo chéo mini xinh xắn, đựng điện thoại và ví nhỏ.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-03.jpg', '[\"assets\\/images\\/products\\/tui-03.jpg\"]', 180000, 220000, 20, 4.6, 78, 0, NOW(), NOW()),
(4, 'SP004', 1, 'Túi cói đan móc', 'tui-coi-dan-moc', 'Túi cói đan móc tự nhiên, phong cách mùa hè.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-04.jpg', '[\"assets\\/images\\/products\\/tui-04.jpg\"]', 320000, 400000, 15, 4.7, 62, 0, NOW(), NOW()),
(5, 'SP005', 1, 'Túi bucket len', 'tui-bucket-len', 'Túi bucket len đan tay, kiểu dáng Hàn Quốc.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-05.jpg', '[\"assets\\/images\\/products\\/tui-05.jpg\"]', 260000, NULL, 18, 4.8, 91, 0, NOW(), NOW()),
(6, 'SP006', 1, 'Túi đan mây tre', 'tui-dan-may-tre', 'Túi đan mây tre thủ công, bền đẹp, thân thiện môi trường.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-06.jpg', '[\"assets\\/images\\/products\\/tui-06.jpg\"]', 350000, 420000, 10, 4.5, 45, 0, NOW(), NOW()),
(7, 'SP007', 1, 'Túi vải canvas', 'tui-vai-canvas', 'Túi vải canvas in hình, phong cách trẻ trung.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-07.jpg', '[\"assets\\/images\\/products\\/tui-07.jpg\"]', 150000, 200000, 25, 4.6, 120, 0, NOW(), NOW()),
(8, 'SP008', 1, 'Túi clutch dự tiệc', 'tui-clutch-du-tiec', 'Túi clutch dự tiệc sang trọng, đan len cao cấp.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-08.jpg', '[\"assets\\/images\\/products\\/tui-08.jpg\"]', 220000, NULL, 12, 4.9, 38, 0, NOW(), NOW()),
(9, 'SP009', 1, 'Túi đeo vai nữ', 'tui-deo-vai-nu', 'Túi đeo vai nữ tính, đan len mềm mại.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-09.jpg', '[\"assets\\/images\\/products\\/tui-09.jpg\"]', 240000, 300000, 20, 4.7, 85, 0, NOW(), NOW()),
(10, 'SP010', 1, 'Túi xách công sở', 'tui-xach-cong-so', 'Túi xách công sở thanh lịch, đựng được laptop.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/tui-10.jpg', '[\"assets\\/images\\/products\\/tui-10.jpg\"]', 400000, 500000, 10, 4.8, 42, 0, NOW(), NOW()),
(11, 'SP011', 2, 'Móc khóa gấu nhỏ', 'moc-khoa-gau-nho', 'Móc khóa hình gấu nhỏ xinh, làm từ len acrylic cao cấp.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-01.jpg', '[\"assets\\/images\\/products\\/moc-01.jpg\"]', 45000, 60000, 50, 4.9, 320, 1, NOW(), NOW()),
(12, 'SP012', 2, 'Móc khóa trái tim', 'moc-khoa-trai-tim', 'Móc khóa trái tim đôi, làm quà cặp đôi.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-02.jpg', '[\"assets\\/images\\/products\\/moc-02.jpg\"]', 35000, 50000, 100, 5, 210, 1, NOW(), NOW()),
(13, 'SP013', 2, 'Móc khóa hoa hồng', 'moc-khoa-hoa-hong', 'Móc khóa hoa hồng len, làm quà tặng bạn gái.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-03.jpg', '[\"assets\\/images\\/products\\/moc-03.jpg\"]', 40000, NULL, 60, 4.8, 156, 0, NOW(), NOW()),
(14, 'SP014', 2, 'Móc khóa chìa khóa', 'moc-khoa-chia-khoa', 'Móc khóa chìa khóa mini, tiện lợi.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-04.jpg', '[\"assets\\/images\\/products\\/moc-04.jpg\"]', 25000, 35000, 80, 4.6, 90, 0, NOW(), NOW()),
(15, 'SP015', 2, 'Móc khóa thỏ con', 'moc-khoa-tho-con', 'Móc khóa thỏ con đáng yêu.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-05.jpg', '[\"assets\\/images\\/products\\/moc-05.jpg\"]', 42000, NULL, 55, 4.9, 178, 0, NOW(), NOW()),
(16, 'SP016', 2, 'Móc khóa mèo con', 'moc-khoa-meo-con', 'Móc khóa mèo con ngộ nghĩnh.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-06.jpg', '[\"assets\\/images\\/products\\/moc-06.jpg\"]', 38000, 50000, 70, 4.7, 145, 0, NOW(), NOW()),
(17, 'SP017', 2, 'Móc khóa cầu vồng', 'moc-khoa-cau-vong', 'Móc khóa cầu vồng nhiều màu.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-07.jpg', '[\"assets\\/images\\/products\\/moc-07.jpg\"]', 30000, NULL, 90, 4.8, 220, 0, NOW(), NOW()),
(18, 'SP018', 2, 'Móc khóa ngôi sao', 'moc-khoa-ngoi-sao', 'Móc khóa ngôi sao lấp lánh.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-08.jpg', '[\"assets\\/images\\/products\\/moc-08.jpg\"]', 32000, 45000, 65, 4.6, 132, 0, NOW(), NOW()),
(19, 'SP019', 2, 'Móc khóa cún con', 'moc-khoa-cun-con', 'Móc khóa cún con dễ thương.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-09.jpg', '[\"assets\\/images\\/products\\/moc-09.jpg\"]', 40000, NULL, 60, 4.8, 165, 0, NOW(), NOW()),
(20, 'SP020', 2, 'Móc khóa hoa cúc', 'moc-khoa-hoa-cuc', 'Móc khóa hoa cúc trắng xinh.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/moc-10.jpg', '[\"assets\\/images\\/products\\/moc-10.jpg\"]', 36000, 48000, 75, 4.7, 148, 0, NOW(), NOW()),
(21, 'SP021', 3, 'Vòng tay hạt gỗ', 'vong-tay-hat-go', 'Vòng tay hạt gỗ tự nhiên, kết hợp chỉ đỏ may mắn.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-01.jpg', '[\"assets\\/images\\/products\\/ts-01.jpg\"]', 89000, NULL, 30, 4.7, 89, 1, NOW(), NOW()),
(22, 'SP022', 3, 'Dây chuyền hoa', 'day-chuyen-hoa', 'Dây chuyền mặt hoa len, nhẹ nhàng nữ tính.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-02.jpg', '[\"assets\\/images\\/products\\/ts-02.jpg\"]', 120000, 150000, 25, 4.5, 60, 1, NOW(), NOW()),
(23, 'SP023', 3, 'Khuyên tai ngọc trai', 'khuyen-tai-ngoc-trai', 'Khuyên tai ngọc trai thanh lịch.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-03.jpg', '[\"assets\\/images\\/products\\/ts-03.jpg\"]', 75000, 100000, 40, 4.8, 132, 0, NOW(), NOW()),
(24, 'SP024', 3, 'Nhẫn bạc nữ', 'nhan-bac-nu', 'Nhẫn bạc nữ đơn giản, thanh lịch.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-04.jpg', '[\"assets\\/images\\/products\\/ts-04.jpg\"]', 95000, NULL, 35, 4.6, 98, 0, NOW(), NOW()),
(25, 'SP025', 3, 'Lắc tay charm', 'lac-tay-charm', 'Lắc tay charm nhiều phụ kiện.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-05.jpg', '[\"assets\\/images\\/products\\/ts-05.jpg\"]', 110000, 140000, 28, 4.7, 76, 0, NOW(), NOW()),
(26, 'SP026', 3, 'Bông tai giọt nước', 'bong-tai-giot-nuoc', 'Bông tai giọt nước lấp lánh.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-06.jpg', '[\"assets\\/images\\/products\\/ts-06.jpg\"]', 65000, NULL, 50, 4.5, 112, 0, NOW(), NOW()),
(27, 'SP027', 3, 'Vòng cổ đá quý', 'vong-co-da-quy', 'Vòng cổ đá quý sang trọng.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-07.jpg', '[\"assets\\/images\\/products\\/ts-07.jpg\"]', 250000, 320000, 15, 4.9, 42, 0, NOW(), NOW()),
(28, 'SP028', 3, 'Vòng tay charm bạc', 'vong-tay-charm-bac', 'Vòng tay charm bạc cá tính.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-08.jpg', '[\"assets\\/images\\/products\\/ts-08.jpg\"]', 140000, NULL, 22, 4.6, 68, 0, NOW(), NOW()),
(29, 'SP029', 3, 'Nhẫn cặp đôi', 'nhan-cap-doi', 'Nhẫn cặp đôi ý nghĩa cho các cặp tình nhân.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-09.jpg', '[\"assets\\/images\\/products\\/ts-09.jpg\"]', 180000, 240000, 20, 4.8, 105, 0, NOW(), NOW()),
(30, 'SP030', 3, 'Trâm cài tóc ngọc', 'tram-cai-toc-ngoc', 'Trâm cài tóc ngọc trai cổ điển.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/ts-10.jpg', '[\"assets\\/images\\/products\\/ts-10.jpg\"]', 130000, NULL, 18, 4.7, 58, 0, NOW(), NOW()),
(31, 'SP031', 4, 'Gấu bông mini', 'gau-bong-mini', 'Gấu bông mini handmade, nhồi bông gòn trắng, mềm mịn.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-01.jpg', '[\"assets\\/images\\/products\\/gau-01.jpg\"]', 180000, 220000, 20, 4.6, 45, 1, NOW(), NOW()),
(32, 'SP032', 4, 'Gấu bông thỏ', 'gau-bong-tho', 'Gấu bông hình thỏ trắng, tai dài, mắt đen láy.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-02.jpg', '[\"assets\\/images\\/products\\/gau-02.jpg\"]', 200000, NULL, 18, 4.7, 52, 1, NOW(), NOW()),
(33, 'SP033', 4, 'Gấu bông teddy', 'gau-bong-teddy', 'Gấu bông teddy cổ điển, mềm mại.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-03.jpg', '[\"assets\\/images\\/products\\/gau-03.jpg\"]', 250000, 320000, 15, 4.9, 88, 0, NOW(), NOW()),
(34, 'SP034', 4, 'Gấu bông gấu trúc', 'gau-bong-gau-truc', 'Gấu bông gấu trúc đen trắng đáng yêu.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-04.jpg', '[\"assets\\/images\\/products\\/gau-04.jpg\"]', 220000, NULL, 22, 4.8, 76, 0, NOW(), NOW()),
(35, 'SP035', 4, 'Gấu bông cún con', 'gau-bong-cun-con', 'Gấu bông cún con lông xù.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-05.jpg', '[\"assets\\/images\\/products\\/gau-05.jpg\"]', 170000, 220000, 25, 4.6, 65, 0, NOW(), NOW()),
(36, 'SP036', 4, 'Gấu bông mèo con', 'gau-bong-meo-con', 'Gấu bông mèo con ngộ nghĩnh.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-06.jpg', '[\"assets\\/images\\/products\\/gau-06.jpg\"]', 190000, NULL, 20, 4.7, 58, 0, NOW(), NOW()),
(37, 'SP037', 4, 'Gấu bông khủng long', 'gau-bong-khung-long', 'Gấu bông khủng long cho bé trai.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-07.jpg', '[\"assets\\/images\\/products\\/gau-07.jpg\"]', 210000, 280000, 18, 4.8, 72, 0, NOW(), NOW()),
(38, 'SP038', 4, 'Gấu bông kỳ lân', 'gau-bong-ky-lan', 'Gấu bông kỳ lân nhiều màu.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-08.jpg', '[\"assets\\/images\\/products\\/gau-08.jpg\"]', 240000, NULL, 15, 4.9, 95, 0, NOW(), NOW()),
(39, 'SP039', 4, 'Gấu bông cá voi', 'gau-bong-ca-voi', 'Gấu bông cá voi xanh mềm mịn.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-09.jpg', '[\"assets\\/images\\/products\\/gau-09.jpg\"]', 160000, 200000, 28, 4.6, 84, 0, NOW(), NOW()),
(40, 'SP040', 4, 'Gấu bông đôi', 'gau-bong-doi', 'Cặp gấu bông đôi làm quà tặng.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/gau-10.jpg', '[\"assets\\/images\\/products\\/gau-10.jpg\"]', 320000, 400000, 12, 4.9, 48, 0, NOW(), NOW()),
(41, 'SP041', 5, 'Khăn len nữ tính', 'khan-len-nu-tinh', 'Khăn len dài 1m6, đan tay, giữ ấm tốt, nhiều màu để lựa chọn.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-01.jpg', '[\"assets\\/images\\/products\\/khan-01.jpg\"]', 150000, 200000, 15, 4.8, 78, 1, NOW(), NOW()),
(42, 'SP042', 5, 'Khăn choàng cổ', 'khan-choang-co', 'Khăn choàng cổ len mềm, đan kiểu sóng, phù hợp mùa đông.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-02.jpg', '[\"assets\\/images\\/products\\/khan-02.jpg\"]', 190000, 240000, 22, 4.8, 91, 1, NOW(), NOW()),
(43, 'SP043', 5, 'Khăn quàng cổ dài', 'khan-quang-co-dai', 'Khăn quàng cổ dài 2m, đan len cao cấp.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-03.jpg', '[\"assets\\/images\\/products\\/khan-03.jpg\"]', 170000, NULL, 20, 4.7, 66, 0, NOW(), NOW()),
(44, 'SP044', 5, 'Khăn len trẻ em', 'khan-len-tre-em', 'Khăn len trẻ em nhiều màu sắc, an toàn cho bé.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-04.jpg', '[\"assets\\/images\\/products\\/khan-04.jpg\"]', 90000, 120000, 35, 4.9, 124, 0, NOW(), NOW()),
(45, 'SP045', 5, 'Khăn len nam', 'khan-len-nam', 'Khăn len nam tính, đan kiểu đơn giản, lịch sự.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-05.jpg', '[\"assets\\/images\\/products\\/khan-05.jpg\"]', 180000, NULL, 18, 4.6, 72, 0, NOW(), NOW()),
(46, 'SP046', 5, 'Khăn len họa tiết', 'khan-len-hoa-tiet', 'Khăn len họa tiết hoa văn đẹp mắt.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-06.jpg', '[\"assets\\/images\\/products\\/khan-06.jpg\"]', 210000, 260000, 14, 4.8, 56, 0, NOW(), NOW()),
(47, 'SP047', 5, 'Khăn len mỏng', 'khan-len-mong', 'Khăn len mỏng nhẹ, phù hợp mùa thu.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-07.jpg', '[\"assets\\/images\\/products\\/khan-07.jpg\"]', 120000, NULL, 25, 4.5, 88, 0, NOW(), NOW()),
(48, 'SP048', 5, 'Khăn len dày', 'khan-len-day', 'Khăn len dày giữ ấm cực tốt cho mùa đông.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-08.jpg', '[\"assets\\/images\\/products\\/khan-08.jpg\"]', 230000, 290000, 16, 4.9, 62, 0, NOW(), NOW()),
(49, 'SP049', 5, 'Khăn len ombre', 'khan-len-ombre', 'Khăn len ombre chuyển màu độc đáo.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-09.jpg', '[\"assets\\/images\\/products\\/khan-09.jpg\"]', 200000, NULL, 20, 4.7, 74, 0, NOW(), NOW()),
(50, 'SP050', 5, 'Khăn len tặng kèm hộp', 'khan-len-tang-kem-hop', 'Khăn len cao cấp tặng kèm hộp quà sang trọng.', 'Bảo quản nơi khô ráo, tránh ánh nắng trực tiếp và hóa chất mạnh.', 'assets/images/products/khan-10.jpg', '[\"assets\\/images\\/products\\/khan-10.jpg\"]', 260000, 320000, 10, 5, 38, 0, NOW(), NOW());

-- Bảng orders
INSERT INTO orders (id, code, user_id, shipping_name, shipping_phone, shipping_address, total_amount, status, created_at, updated_at) VALUES 
(1, 'ORD-9023', 2, 'Nguyễn Văn An', '0912345678', '123 Đường Nguyễn Huệ, Quận 1, TP.HCM', 1250000, 'pending', NOW(), NOW()),
(2, 'ORD-9022', 3, 'Trần Thị Bình', '0923456789', '456 Lê Duẩn, Quận Hải Châu, Đà Nẵng', 850000, 'shipping', NOW(), NOW()),
(3, 'ORD-9021', 4, 'Lê Văn Minh', '0934567890', '789 Phố Huế, Quận Hai Bà Trưng, Hà Nội', 3100000, 'completed', NOW(), NOW());

-- Bảng order_items
INSERT INTO order_items (id, order_id, product_id, quantity, unit_price, total_price, created_at, updated_at) VALUES 
(1, 1, 1, 2, 250000, 500000, NOW(), NOW()),
(2, 1, 4, 1, 320000, 320000, NOW(), NOW()),
(3, 1, 11, 5, 45000, 225000, NOW(), NOW()),
(4, 1, 31, 1, 180000, 180000, NOW(), NOW()),
(5, 2, 2, 2, 280000, 560000, NOW(), NOW()),
(6, 2, 41, 1, 150000, 150000, NOW(), NOW()),
(7, 2, 21, 1, 89000, 89000, NOW(), NOW()),
(8, 3, 10, 4, 400000, 1600000, NOW(), NOW()),
(9, 3, 6, 3, 350000, 1050000, NOW(), NOW()),
(10, 3, 33, 1, 250000, 250000, NOW(), NOW()),
(11, 3, 48, 1, 230000, 230000, NOW(), NOW());

<?php
/**
 * MockData - Dữ liệu giả lập cho giai đoạn FE
 */

function getCategories(): array {
    return [
        ['id' => 1, 'name' => 'Túi xách',  'slug' => 'tui-xach',  'icon' => '👜'],
        ['id' => 2, 'name' => 'Móc khóa',  'slug' => 'moc-khoa',  'icon' => '🔑'],
        ['id' => 3, 'name' => 'Trang sức', 'slug' => 'trang-suc', 'icon' => '💍'],
        ['id' => 4, 'name' => 'Gấu bông',  'slug' => 'gau-bong',  'icon' => '🧸'],
        ['id' => 5, 'name' => 'Khăn len',  'slug' => 'khan-len',  'icon' => '🧣'],
    ];
}

function getProducts(): array {
    return [
        [
            'id' => 1, 'name' => 'Túi len thủ công', 'slug' => 'tui-len-thu-cong',
            'price' => 250000, 'old_price' => 320000,
            'image' => 'https://picsum.photos/seed/tui1/500/500',
            'gallery' => [
                'https://picsum.photos/seed/tui1a/500/500',
                'https://picsum.photos/seed/tui1b/500/500',
                'https://picsum.photos/seed/tui1c/500/500',
            ],
            'category_id' => 1, 'category' => 'Túi xách',
            'description' => 'Túi len thủ công được đan tỉ mỉ từng mũi, chất liệu cotton mềm mại, thân thiện với môi trường. Phù hợp đi chơi, đi học hoặc làm quà tặng.',
            'stock' => 12, 'rating' => 4.8, 'sold' => 156, 'is_featured' => true,
        ],
        [
            'id' => 2, 'name' => 'Móc khóa gấu nhỏ', 'slug' => 'moc-khoa-gau-nho',
            'price' => 45000, 'old_price' => 60000,
            'image' => 'https://picsum.photos/seed/moc2/500/500',
            'gallery' => [
                'https://picsum.photos/seed/moc2a/500/500',
                'https://picsum.photos/seed/moc2b/500/500',
            ],
            'category_id' => 2, 'category' => 'Móc khóa',
            'description' => 'Móc khóa hình gấu nhỏ xinh, làm từ len acrylic cao cấp, không phai màu.',
            'stock' => 50, 'rating' => 4.9, 'sold' => 320, 'is_featured' => true,
        ],
        [
            'id' => 3, 'name' => 'Vòng tay hạt gỗ', 'slug' => 'vong-tay-hat-go',
            'price' => 89000, 'old_price' => null,
            'image' => 'https://picsum.photos/seed/vong3/500/500',
            'gallery' => ['https://picsum.photos/seed/vong3a/500/500'],
            'category_id' => 3, 'category' => 'Trang sức',
            'description' => 'Vòng tay hạt gỗ tự nhiên, kết hợp chỉ đỏ may mắn.',
            'stock' => 30, 'rating' => 4.7, 'sold' => 89, 'is_featured' => true,
        ],
        [
            'id' => 4, 'name' => 'Gấu bông mini', 'slug' => 'gau-bong-mini',
            'price' => 180000, 'old_price' => 220000,
            'image' => 'https://picsum.photos/seed/gau4/500/500',
            'gallery' => [
                'https://picsum.photos/seed/gau4a/500/500',
                'https://picsum.photos/seed/gau4b/500/500',
            ],
            'category_id' => 4, 'category' => 'Gấu bông',
            'description' => 'Gấu bông mini handmade, nhồi bông gòn trắng, mềm mịn, an toàn cho trẻ em.',
            'stock' => 20, 'rating' => 4.6, 'sold' => 45, 'is_featured' => true,
        ],
        [
            'id' => 5, 'name' => 'Khăn len nữ tính', 'slug' => 'khan-len-nu-tinh',
            'price' => 150000, 'old_price' => 200000,
            'image' => 'https://picsum.photos/seed/khan5/500/500',
            'gallery' => ['https://picsum.photos/seed/khan5a/500/500'],
            'category_id' => 5, 'category' => 'Khăn len',
            'description' => 'Khăn len dài 1m6, đan tay, giữ ấm tốt, nhiều màu để lựa chọn.',
            'stock' => 15, 'rating' => 4.8, 'sold' => 78, 'is_featured' => false,
        ],
        [
            'id' => 6, 'name' => 'Túi tote len', 'slug' => 'tui-tote-len',
            'price' => 280000, 'old_price' => null,
            'image' => 'https://picsum.photos/seed/tote6/500/500',
            'gallery' => ['https://picsum.photos/seed/tote6a/500/500'],
            'category_id' => 1, 'category' => 'Túi xách',
            'description' => 'Túi tote len đan tay, rộng rãi, đựng được laptop 13 inch.',
            'stock' => 8, 'rating' => 4.9, 'sold' => 34, 'is_featured' => false,
        ],
        [
            'id' => 7, 'name' => 'Móc khóa trái tim', 'slug' => 'moc-khoa-trai-tim',
            'price' => 35000, 'old_price' => 50000,
            'image' => 'https://picsum.photos/seed/tim7/500/500',
            'gallery' => ['https://picsum.photos/seed/tim7a/500/500'],
            'category_id' => 2, 'category' => 'Móc khóa',
            'description' => 'Móc khóa trái tim đôi, làm quà tặng cặp đôi cực dễ thương.',
            'stock' => 100, 'rating' => 5.0, 'sold' => 210, 'is_featured' => false,
        ],
        [
            'id' => 8, 'name' => 'Dây chuyền hoa', 'slug' => 'day-chuyen-hoa',
            'price' => 120000, 'old_price' => 150000,
            'image' => 'https://picsum.photos/seed/day8/500/500',
            'gallery' => ['https://picsum.photos/seed/day8a/500/500'],
            'category_id' => 3, 'category' => 'Trang sức',
            'description' => 'Dây chuyền mặt hoa len, nhẹ nhàng nữ tính.',
            'stock' => 25, 'rating' => 4.5, 'sold' => 60, 'is_featured' => false,
        ],
        [
            'id' => 9, 'name' => 'Gấu bông thỏ', 'slug' => 'gau-bong-tho',
            'price' => 200000, 'old_price' => null,
            'image' => 'https://picsum.photos/seed/tho9/500/500',
            'gallery' => ['https://picsum.photos/seed/tho9a/500/500'],
            'category_id' => 4, 'category' => 'Gấu bông',
            'description' => 'Gấu bông hình thỏ trắng, tai dài, mắt đen láy.',
            'stock' => 18, 'rating' => 4.7, 'sold' => 52, 'is_featured' => false,
        ],
        [
            'id' => 10, 'name' => 'Khăn choàng cổ', 'slug' => 'khan-choang-co',
            'price' => 190000, 'old_price' => 240000,
            'image' => 'https://picsum.photos/seed/choang10/500/500',
            'gallery' => ['https://picsum.photos/seed/choang10a/500/500'],
            'category_id' => 5, 'category' => 'Khăn len',
            'description' => 'Khăn choàng cổ len mềm, đan kiểu sóng, phù hợp mùa đông.',
            'stock' => 22, 'rating' => 4.8, 'sold' => 91, 'is_featured' => false,
        ],
    ];
}
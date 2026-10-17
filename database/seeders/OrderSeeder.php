<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'id' => 1,
                'code' => 'ORD-9023',
                'user_id' => 2,
                'shipping_name' => 'Nguyễn Văn An',
                'shipping_phone' => '0912345678',
                'shipping_address' => '123 Đường Nguyễn Huệ, Quận 1, TP.HCM',
                'total_amount' => 1250000.00,
                'status' => 'pending',
                'items' => [
                    ['product_id' => 1, 'quantity' => 2, 'unit_price' => 250000.00, 'total_price' => 500000.00],
                    ['product_id' => 4, 'quantity' => 1, 'unit_price' => 320000.00, 'total_price' => 320000.00],
                    ['product_id' => 11, 'quantity' => 5, 'unit_price' => 45000.00, 'total_price' => 225000.00],
                    ['product_id' => 31, 'quantity' => 1, 'unit_price' => 180000.00, 'total_price' => 180000.00],
                ]
            ],
            [
                'id' => 2,
                'code' => 'ORD-9022',
                'user_id' => 3,
                'shipping_name' => 'Trần Thị Bình',
                'shipping_phone' => '0923456789',
                'shipping_address' => '456 Lê Duẩn, Quận Hải Châu, Đà Nẵng',
                'total_amount' => 850000.00,
                'status' => 'shipping',
                'items' => [
                    ['product_id' => 2, 'quantity' => 2, 'unit_price' => 280000.00, 'total_price' => 560000.00],
                    ['product_id' => 41, 'quantity' => 1, 'unit_price' => 150000.00, 'total_price' => 150000.00],
                    ['product_id' => 21, 'quantity' => 1, 'unit_price' => 89000.00, 'total_price' => 89000.00],
                ]
            ],
            [
                'id' => 3,
                'code' => 'ORD-9021',
                'user_id' => 4,
                'shipping_name' => 'Lê Văn Minh',
                'shipping_phone' => '0934567890',
                'shipping_address' => '789 Phố Huế, Quận Hai Bà Trưng, Hà Nội',
                'total_amount' => 3100000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 10, 'quantity' => 4, 'unit_price' => 400000.00, 'total_price' => 1600000.00],
                    ['product_id' => 6, 'quantity' => 3, 'unit_price' => 350000.00, 'total_price' => 1050000.00],
                    ['product_id' => 33, 'quantity' => 1, 'unit_price' => 250000.00, 'total_price' => 250000.00],
                    ['product_id' => 48, 'quantity' => 1, 'unit_price' => 230000.00, 'total_price' => 230000.00],
                ]
            ],
            [
                'id' => 4,
                'code' => 'ORD-9020',
                'user_id' => 2,
                'shipping_name' => 'Nguyễn Văn An',
                'shipping_phone' => '0912345678',
                'shipping_address' => '123 Đường Nguyễn Huệ, Quận 1, TP.HCM',
                'total_amount' => 540000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 1, 'quantity' => 1, 'unit_price' => 250000.00, 'total_price' => 250000.00],
                    ['product_id' => 7, 'quantity' => 1, 'unit_price' => 290000.00, 'total_price' => 290000.00],
                ]
            ],
            [
                'id' => 5,
                'code' => 'ORD-9019',
                'user_id' => 3,
                'shipping_name' => 'Trần Thị Bình',
                'shipping_phone' => '0923456789',
                'shipping_address' => '456 Lê Duẩn, Quận Hải Châu, Đà Nẵng',
                'total_amount' => 760000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 3, 'quantity' => 2, 'unit_price' => 180000.00, 'total_price' => 360000.00],
                    ['product_id' => 12, 'quantity' => 1, 'unit_price' => 400000.00, 'total_price' => 400000.00],
                ]
            ],

            // ============ 5 đơn completed MỚI để có đủ order_item review ============

            [
                'id' => 6,
                'code' => 'ORD-9018',
                'user_id' => 2,
                'shipping_name' => 'Nguyễn Văn An',
                'shipping_phone' => '0912345678',
                'shipping_address' => '123 Đường Nguyễn Huệ, Quận 1, TP.HCM',
                'total_amount' => 1500000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 5,  'quantity' => 1, 'unit_price' => 260000.00, 'total_price' => 260000.00],
                    ['product_id' => 8,  'quantity' => 1, 'unit_price' => 220000.00, 'total_price' => 220000.00],
                    ['product_id' => 9,  'quantity' => 1, 'unit_price' => 240000.00, 'total_price' => 240000.00],
                    ['product_id' => 13, 'quantity' => 2, 'unit_price' => 40000.00,  'total_price' => 80000.00],
                    ['product_id' => 14, 'quantity' => 3, 'unit_price' => 25000.00,  'total_price' => 75000.00],
                    ['product_id' => 15, 'quantity' => 1, 'unit_price' => 42000.00,  'total_price' => 42000.00],
                    ['product_id' => 16, 'quantity' => 2, 'unit_price' => 38000.00,  'total_price' => 76000.00],
                    ['product_id' => 17, 'quantity' => 4, 'unit_price' => 30000.00,  'total_price' => 120000.00],
                    ['product_id' => 18, 'quantity' => 1, 'unit_price' => 32000.00,  'total_price' => 32000.00],
                    ['product_id' => 19, 'quantity' => 2, 'unit_price' => 40000.00,  'total_price' => 80000.00],
                    ['product_id' => 20, 'quantity' => 3, 'unit_price' => 36000.00,  'total_price' => 108000.00],
                ]
            ],
            [
                'id' => 7,
                'code' => 'ORD-9017',
                'user_id' => 3,
                'shipping_name' => 'Trần Thị Bình',
                'shipping_phone' => '0923456789',
                'shipping_address' => '456 Lê Duẩn, Quận Hải Châu, Đà Nẵng',
                'total_amount' => 1800000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 22, 'quantity' => 1, 'unit_price' => 120000.00, 'total_price' => 120000.00],
                    ['product_id' => 23, 'quantity' => 2, 'unit_price' => 75000.00,  'total_price' => 150000.00],
                    ['product_id' => 24, 'quantity' => 1, 'unit_price' => 95000.00,  'total_price' => 95000.00],
                    ['product_id' => 25, 'quantity' => 1, 'unit_price' => 110000.00, 'total_price' => 110000.00],
                    ['product_id' => 26, 'quantity' => 2, 'unit_price' => 65000.00,  'total_price' => 130000.00],
                    ['product_id' => 27, 'quantity' => 1, 'unit_price' => 250000.00, 'total_price' => 250000.00],
                    ['product_id' => 28, 'quantity' => 1, 'unit_price' => 140000.00, 'total_price' => 140000.00],
                    ['product_id' => 29, 'quantity' => 1, 'unit_price' => 180000.00, 'total_price' => 180000.00],
                    ['product_id' => 30, 'quantity' => 1, 'unit_price' => 130000.00, 'total_price' => 130000.00],
                    ['product_id' => 32, 'quantity' => 2, 'unit_price' => 200000.00, 'total_price' => 400000.00],
                ]
            ],
            [
                'id' => 8,
                'code' => 'ORD-9016',
                'user_id' => 4,
                'shipping_name' => 'Lê Văn Minh',
                'shipping_phone' => '0934567890',
                'shipping_address' => '789 Phố Huế, Quận Hai Bà Trưng, Hà Nội',
                'total_amount' => 2200000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 34, 'quantity' => 1, 'unit_price' => 220000.00, 'total_price' => 220000.00],
                    ['product_id' => 35, 'quantity' => 1, 'unit_price' => 170000.00, 'total_price' => 170000.00],
                    ['product_id' => 36, 'quantity' => 1, 'unit_price' => 190000.00, 'total_price' => 190000.00],
                    ['product_id' => 37, 'quantity' => 1, 'unit_price' => 210000.00, 'total_price' => 210000.00],
                    ['product_id' => 38, 'quantity' => 1, 'unit_price' => 240000.00, 'total_price' => 240000.00],
                    ['product_id' => 39, 'quantity' => 2, 'unit_price' => 160000.00, 'total_price' => 320000.00],
                    ['product_id' => 40, 'quantity' => 1, 'unit_price' => 320000.00, 'total_price' => 320000.00],
                    ['product_id' => 42, 'quantity' => 1, 'unit_price' => 190000.00, 'total_price' => 190000.00],
                    ['product_id' => 43, 'quantity' => 1, 'unit_price' => 170000.00, 'total_price' => 170000.00],
                    ['product_id' => 44, 'quantity' => 2, 'unit_price' => 90000.00,  'total_price' => 180000.00],
                ]
            ],
            [
                'id' => 9,
                'code' => 'ORD-9015',
                'user_id' => 2,
                'shipping_name' => 'Nguyễn Văn An',
                'shipping_phone' => '0912345678',
                'shipping_address' => '123 Đường Nguyễn Huệ, Quận 1, TP.HCM',
                'total_amount' => 1400000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 45, 'quantity' => 1, 'unit_price' => 180000.00, 'total_price' => 180000.00],
                    ['product_id' => 46, 'quantity' => 1, 'unit_price' => 210000.00, 'total_price' => 210000.00],
                    ['product_id' => 47, 'quantity' => 2, 'unit_price' => 120000.00, 'total_price' => 240000.00],
                    ['product_id' => 49, 'quantity' => 1, 'unit_price' => 160000.00, 'total_price' => 160000.00],
                    ['product_id' => 50, 'quantity' => 1, 'unit_price' => 130000.00, 'total_price' => 130000.00],
                    ['product_id' => 51, 'quantity' => 2, 'unit_price' => 85000.00,  'total_price' => 170000.00],
                    ['product_id' => 52, 'quantity' => 1, 'unit_price' => 95000.00,  'total_price' => 95000.00],
                    ['product_id' => 53, 'quantity' => 1, 'unit_price' => 75000.00,  'total_price' => 75000.00],
                    ['product_id' => 54, 'quantity' => 1, 'unit_price' => 110000.00, 'total_price' => 110000.00],
                    ['product_id' => 55, 'quantity' => 1, 'unit_price' => 90000.00,  'total_price' => 90000.00],
                ]
            ],
            [
                'id' => 10,
                'code' => 'ORD-9014',
                'user_id' => 3,
                'shipping_name' => 'Trần Thị Bình',
                'shipping_phone' => '0923456789',
                'shipping_address' => '456 Lê Duẩn, Quận Hải Châu, Đà Nẵng',
                'total_amount' => 1300000.00,
                'status' => 'completed',
                'items' => [
                    ['product_id' => 56, 'quantity' => 1, 'unit_price' => 280000.00, 'total_price' => 280000.00],
                    ['product_id' => 57, 'quantity' => 1, 'unit_price' => 120000.00, 'total_price' => 120000.00],
                    ['product_id' => 58, 'quantity' => 1, 'unit_price' => 100000.00, 'total_price' => 100000.00],
                    ['product_id' => 59, 'quantity' => 1, 'unit_price' => 80000.00,  'total_price' => 80000.00],
                    ['product_id' => 60, 'quantity' => 1, 'unit_price' => 350000.00, 'total_price' => 350000.00],
                ]
            ],
        ];

        $existingProductIds = Product::pluck('id')->toArray();
        if (empty($existingProductIds)) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        OrderItem::truncate();
        Order::truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($orders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);

            $order = Order::updateOrCreate(['id' => $orderData['id']], $orderData);

            $orderTotal = 0;
            $usedInOrder = [];

            foreach ($items as $item) {
                // Đảm bảo product_id luôn hợp lệ và không trùng lặp trong cùng một đơn
                $targetProductId = in_array($item['product_id'], $existingProductIds, true)
                    ? $item['product_id']
                    : $existingProductIds[array_rand($existingProductIds)];

                if (in_array($targetProductId, $usedInOrder, true)) {
                    $available = array_diff($existingProductIds, $usedInOrder);
                    if (!empty($available)) {
                        $targetProductId = $available[array_rand($available)];
                    }
                }
                $usedInOrder[] = $targetProductId;

                $product = Product::find($targetProductId);
                $unitPrice = $product ? (float) $product->price : (float) $item['unit_price'];
                $totalPrice = $unitPrice * $item['quantity'];
                $orderTotal += $totalPrice;

                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $targetProductId,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            $order->update(['total_amount' => $orderTotal]);
        }
    }
}
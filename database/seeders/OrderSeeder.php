<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

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
        ];

        foreach ($orders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);

            $order = Order::updateOrCreate(['id' => $orderData['id']], $orderData);

            foreach ($items as $item) {
                OrderItem::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                    ],
                    $item
                );
            }
        }
    }
}

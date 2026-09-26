<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Đảm bảo dữ liệu đơn hàng đã tồn tại trước khi seed đánh giá
        if (OrderItem::count() === 0) {
            $this->call(OrderSeeder::class);
        }

        // Danh sách mẫu đánh giá thực tế cho các sản phẩm handmade
        $sampleReviews = [
            // Review cho Order 3 (user_id = 4 - Lê Văn Minh)
            [
                'order_id' => 3,
                'product_id' => 10,
                'rating' => 5,
                'comment' => 'Khăn len quàng cổ rất dày dặn và ấm áp, từng đường đan đều tăm tắp. Màu sắc y hình chụp, đóng hộp có kèm thiệp cảm ơn rất dễ thương!',
            ],
            [
                'order_id' => 3,
                'product_id' => 6,
                'rating' => 5,
                'comment' => 'Sản phẩm thủ công rất tỉ mỉ, chất lượng vượt mong đợi so với giá tiền. Shop tư vấn nhiệt tình, đóng gói cẩn thận 2 lớp chống sốc.',
            ],
            [
                'order_id' => 3,
                'product_id' => 33,
                'rating' => 4,
                'comment' => 'Chất lượng đồ len rất tốt, mềm mại không bị xù lông. Trừ 1 sao vì bên vận chuyển giao hơi trễ một chút nhưng shop hỗ trợ rất nhiệt tình.',
            ],
            [
                'order_id' => 3,
                'product_id' => 48,
                'rating' => 5,
                'comment' => 'Mua làm quà sinh nhật cho bạn thân, bạn mình thích mê luôn! Hàng thủ công tinh xảo, đậm chất nghệ thuật. Sẽ ủng hộ shop dài dài.',
            ],

            // Review cho Order 4 (user_id = 2 - Nguyễn Văn An)
            [
                'order_id' => 4,
                'product_id' => 1,
                'rating' => 5,
                'comment' => 'Móc khóa len hoa tulip xinh xỉu! Đường may chắc chắn, cầm rất êm tay. Giao hàng nhanh hơn dự kiến.',
            ],
            [
                'order_id' => 4,
                'product_id' => 7,
                'rating' => 4,
                'comment' => 'Sản phẩm đẹp đúng mô tả, chất len mịn màng không gây kích ứng da. Rất đáng đồng tiền bát gạo.',
            ],

            // Review cho Order 5 (user_id = 3 - Trần Thị Bình)
            [
                'order_id' => 5,
                'product_id' => 3,
                'rating' => 5,
                'comment' => 'Túi tỏi len cho bé siêu cưng luôn ạ. Chất len an toàn, mềm mại. 10/10 điểm cho sự khéo tay của các nghệ nhân làm đồ handmade!',
            ],
            [
                'order_id' => 5,
                'product_id' => 12,
                'rating' => 5,
                'comment' => 'Nón bucket len phối màu vintage rất đẹp, đội vừa vặn và phong cách. Đóng gói chỉn chu, shop 5 sao!',
            ],
        ];

        foreach ($sampleReviews as $data) {
            $orderItem = OrderItem::where('order_id', $data['order_id'])
                ->where('product_id', $data['product_id'])
                ->first();

            if (! $orderItem) {
                continue;
            }

            $order = $orderItem->order;
            if (! $order) {
                continue;
            }

            Review::updateOrCreate(
                [
                    'order_item_id' => $orderItem->id,
                ],
                [
                    'user_id' => $order->user_id,
                    'product_id' => $data['product_id'],
                    'rating' => $data['rating'],
                    'comment' => $data['comment'],
                ]
            );
        }
    }
}

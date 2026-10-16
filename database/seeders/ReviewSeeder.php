<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            'Sản phẩm rất đẹp, đúng mô tả, đóng gói cẩn thận.',
            'Chất lượng vượt mong đợi, đường may tỉ mỉ. Sẽ ủng hộ shop tiếp!',
            'Mua làm quà tặng, ai cũng khen. Rất đáng tiền.',
            'Giao hàng nhanh, shop tư vấn nhiệt tình. 5 sao!',
            'Sản phẩm handmade tinh xảo, màu sắc y hình. Rất hài lòng.',
            'Chất len mềm mịn, không gây kích ứng da. Recommend mọi người.',
            'Đóng gói đẹp, có thiệp cảm ơn. Trải nghiệm mua hàng tuyệt vời.',
            'Đúng như mong đợi, sẽ giới thiệu cho bạn bè.',
            'Hàng thủ công đẹp xuất sắc, rất tỉ mỉ. Cảm ơn shop.',
            'Chất lượng tốt so với giá, đáng mua.',
            'Shop hỗ trợ đổi trả nhanh chóng. Rất chuyên nghiệp.',
            'Sản phẩm đẹp, nhưng giao hàng hơi chậm một chút.',
        ];

        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn('Chưa có user nào, bỏ qua ReviewSeeder.');
            return;
        }

        $products = Product::all();

        // Xóa review cũ để tránh trùng khi seed lại
        Review::query()->delete();

        $reviewCount = 0;

        foreach ($products as $product) {
            $numReviews = rand(3, 6);

            for ($i = 0; $i < $numReviews; $i++) {
                $rating = rand(0, 100) < 75 ? 5 : 4;

                Review::create([
                    'user_id'       => $userIds[array_rand($userIds)],
                    'product_id'    => $product->id,
                    'order_item_id' => null,
                    'rating'        => $rating,
                    'comment'       => $comments[array_rand($comments)],
                ]);

                $reviewCount++;
            }

            $product->recalculateRating();
        }

        $this->command->info("Đã tạo {$reviewCount} review cho {$products->count()} sản phẩm.");
    }
}
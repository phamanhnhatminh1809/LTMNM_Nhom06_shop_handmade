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
            'Sản phẩm đẹp, đúng mô tả và được đóng gói cẩn thận.',
            'Chất lượng tốt, đường may tỉ mỉ và chắc chắn. Mình sẽ tiếp tục ủng hộ cửa hàng.',
            'Mua làm quà tặng rất phù hợp, người nhận đều thích.',
            'Giao hàng đúng hẹn, cửa hàng tư vấn nhiệt tình và rõ ràng.',
            'Sản phẩm thủ công khéo léo, màu sắc đúng như hình ảnh minh họa.',
            'Chất len mềm mịn, dễ chịu khi sử dụng, rất đáng mua.',
            'Đóng gói gọn gàng, kèm thiệp cảm ơn chu đáo.',
            'Sản phẩm đáp ứng đúng kỳ vọng, phù hợp để giới thiệu cho bạn bè.',
            'Đồ thủ công được làm cẩn thận, các chi tiết gọn đẹp.',
            'Chất lượng ổn định so với mức giá, phù hợp với nhu cầu sử dụng.',
            'Cửa hàng hỗ trợ đổi trả nhanh chóng và rõ ràng.',
            'Sản phẩm đẹp và chắc chắn, tuy nhiên thời gian giao hàng hơi chậm một chút.',
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

        $this->command->info("Đã tạo {$reviewCount} đánh giá cho {$products->count()} sản phẩm.");
    }
}
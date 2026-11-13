<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <h4 style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-gem"></i>
                <span>Shop Handmade</span>
            </h4>
            <p>Đồ thủ công len, móc khóa, túi xách handmade dễ thương. Mỗi sản phẩm là một tác phẩm nhỏ xinh được làm bằng cả trái tim người thợ thủ công.</p>
        </div>

        <div class="footer-col">
            <h4>Liên kết</h4>
            <ul>
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                <li><a href="{{ route('about') }}">Giới thiệu</a></li>
                <li><a href="{{ route('contact') }}">Liên hệ</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Hỗ trợ</h4>
            <ul>
                <li><a href="{{ route('about') }}">Chính sách đổi trả (7 ngày)</a></li>
                <li><a href="{{ route('products.index') }}">Hướng dẫn chọn &amp; mua hàng</a></li>
                <li><a href="{{ route('cart.index') }}">Chính sách vận chuyển (Free từ {{ number_format(config('shop.shipping.free_threshold', 300000), 0, ',', '.') }}đ)</a></li>
                <li><a href="{{ route('contact') }}">Tư vấn &amp; đặt làm riêng</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Liên hệ</h4>
            <p style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-geo-alt"></i> {{ config('shop.contact.address') }}
            </p>
            <p style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-telephone"></i> {{ config('shop.contact.phone') }}
            </p>
            <p style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-envelope"></i> {{ config('shop.contact.email') }}
            </p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© {{ date('Y') }} Shop Handmade - LTMNM Nhóm 06. All rights reserved.</p>
    </div>
</footer>
